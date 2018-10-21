<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 28/06/2018
 * Time: 1:22
 */

namespace maesierra\Japo\AppContext;


use Aura\Di\Container;
use Aura\Di\ContainerConfig;
use Auth0\SDK\Auth0;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use maesierra\Japo\App\Controller\AuthController;
use maesierra\Japo\App\Controller\DefaultController;
use maesierra\Japo\App\Controller\JDictController;
use maesierra\Japo\App\Controller\KanjiController;
use maesierra\Japo\Auth\Auth0AuthManager;
use maesierra\Japo\Auth\NoLoginAuthManager;
use maesierra\Japo\DB\DBMigration;
use maesierra\Japo\DB\JDictRepository;
use maesierra\Japo\DB\KanjiRepository;
use maesierra\Japo\Entity\Kanji\KanjiCatalog;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Slim\CallableResolver;

class JapoAppContextBuilder extends ContainerConfig {

    /** @var  JapoAppContext */
    private $appContext;


    /**
     * JapoAppContextBuilder constructor.
     * @param JapoAppContext $appContext
     */
    public function __construct($appContext) {
       $this->appContext = $appContext;
    }

    /**
     * @param $di Container
     * @param $name string 
     * @param $class string
     * @param $constructorArgs array
     */
    private function createObject($di, $name, $class, $constructorArgs) {
        $instance = $di->lazyNew($class, $constructorArgs);
        $di->set($name, $instance);
    }

    public function getParam($param, $default = false) {
        return JapoAppConfig::get()->getParam($param, $default);
    }


    public function define(Container $di) {
        $config = JapoAppConfig::get();
        $di->set('params', $config);
        $di->set('config', $config);
        $this->auth0Config($di, $config);
        $this->auth0($di, $config);
        $this->defaultLogger($di, $config);
        $this->authManager($di, $config);
        $this->dbMigration($di, $config);
        $this->entityManager($di, $config);
        $this->kanjiRepository($di, $config);
        $this->jdictRepository($di, $config);
        $this->authController($di, $config);
        $this->kanjiController($di, $config);
        $this->jdictController($di, $config);
        $this->defaultController($di, $config);
        $this->slimCoreServices($di);
    }

    /**
     * @param Container $di
     * @param JapoAppConfig $config
     */
    private function dbMigration(Container $di, $config) {
        $dbMigrationConfig = [
            'paths' => [
                'migrations' => [
                    "{$config->rootPath}/db/migrations",
                    "{$config->rootPath}/db/migrations/{$config->lang}"
                ],
                'seeds' => $config->rootPath.'/db/seeds'
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database' => 'production',
                'production' => [
                    'adapter' => 'mysql',
                    'host' => $config->mysqlHost,
                    'name' => $config->databaseName,
                    'user' => $config->mysqlUser,
                    'pass' => $config->mysqlPassword,
                    'port' => $config->mysqlPort,
                    'charset' => 'utf8',
                ]
            ],
            'version_order' => 'creation'
        ];
        $this->createObject($di, 'dbMigration', DBMigration::class, [
            'config' => $dbMigrationConfig,
            'tempDir' => $config->tempDir
        ]);
    }

    /**
     * @param Container $di
     * @param $config
     */
    private function auth0Config(Container $di, $config)
    {
        $auth0Config = [
            'domain' => $config->auth0Domain,
            'client_id' => $config->auth0ClientId,
            'client_secret' => $config->auth0ClientSecret,
            'redirect_uri' => $config->auth0RedirectUri,
            'audience' => 'https://' . $config->auth0Domain . '/userinfo',
            'scope' => 'openid profile',
            'persist_id_token' => true,
            'persist_access_token' => true,
            'persist_refresh_token' => true
        ];
        $di->set('auth0Config', $di->lazy(function () use ($auth0Config, $config) {
            if ($config->cliMode) {
                $auth0Config['store'] = false;
                $auth0Config['state_handler'] = false;
            }
            return $auth0Config;
        }));
    }

    /**
     * @param Container $di
     */
    private function auth0(Container $di, $config)
    {
        $this->createObject($di, 'auth0', Auth0::class, ['config' => $di->lazyGet('auth0Config')]);
    }

    /**
     * @param Container $di
     * @param $config JapoAppConfig
     */
    private function defaultLogger(Container $di, $config)
    {
        $di->set('defaultLogger', $di->lazy(function () use ($config) {
            $log = new Logger('japo');
            $handler = new StreamHandler("{$config->logPath}/japo.log", constant('Monolog\Logger::' . $config->logLevel));
            $handler->pushProcessor(new UidProcessor(24));
            $log->pushHandler($handler);
            return $log;
        }));
    }

    /**
     * @param Container $di
     * @param $config JapoAppConfig
     */
    private function authManager(Container $di, $config)
    {
        switch ($config->authManager) {
            case NoLoginAuthManager::class:
                $this->createObject($di, 'authManager', NoLoginAuthManager::class, []);
                break;
            case Auth0AuthManager::class:
                $this->createObject($di, 'authManager', Auth0AuthManager::class, [
                    'auth0' => $di->lazyGet('auth0'),
                    'logger' => $di->lazyGet('defaultLogger'),
                    'auth0Domain' => $config->auth0Domain,
                    'auth0ClientId' => $config->auth0ClientId,
                    'auth0LogoutUri' => $config->auth0LogoutUri
                ]);
        }
    }

    /**
     * @param Container $di
     * @param $config JapoAppConfig
     */
    private function entityManager(Container $di, $config) {
        $di->set('entityManager', $di->lazy(function() use($config) {
            $reflector = new \ReflectionClass(KanjiCatalog::class);
            $doctrineConfig = Setup::createAnnotationMetadataConfiguration(
                [dirname(dirname($reflector->getFileName()))]
            );
            $cache = new ApcuCache();
            $doctrineConfig->setAutoGenerateProxyClasses(true);
            $doctrineConfig->setQueryCacheImpl($cache);
            $doctrineConfig->setResultCacheImpl($cache);
            $entityManager = EntityManager::create([
                "driver" => "pdo_mysql",
                "dbname" => $config->databaseName,
                "user" => $config->mysqlUser,
                "password" => $config->mysqlPassword,
                "host" => $config->mysqlHost,
                "port" => $config->mysqlPort,
                "charset" => 'utf8'
            ],
                $doctrineConfig
            );
            $entityManager->getConnection()->getConfiguration()->setSQLLogger(new DebugStack());
            return $entityManager;
        }));
    }

    /**
     * @param Container $di
     * @param $config
     */
    private function kanjiRepository(Container $di, $config)
    {
        $this->createObject($di, 'kanjiRepository', KanjiRepository::class, [
            'entityManager' => $di->lazyGet('entityManager'),
            'logger' => $di->lazyGet('defaultLogger')
        ]);
    }

    /**
     * @param Container $di
     * @param $config
     */
    private function jdictRepository(Container $di, $config)
    {
        $this->createObject($di, 'jdictRepository', JDictRepository::class, [
            'entityManager' => $di->lazyGet('entityManager'),
            'logger' => $di->lazyGet('defaultLogger')
        ]);
    }


    /**
     * @param Container $di
     * @param $config
     */
    private function authController(Container $di, $config)
    {
        $this->createObject($di, AuthController::class, AuthController::class, [
            'authManager' => $di->lazyGet('authManager'),
            'logger' => $di->lazyGet('defaultLogger'),
            'config' => $di->lazyGet('config')
        ]);
    }

    /**
     * @param Container $di
     * @param $config
     */
    private function defaultController(Container $di, $config)
    {
        $this->createObject($di, DefaultController::class, DefaultController::class, [
            'logger' => $di->lazyGet('defaultLogger'),
            'config' => $di->lazyGet('config')
        ]);
    }


    /**
     * @param Container $di
     * @param $config
     */
    private function kanjiController(Container $di, $config)
    {
        $this->createObject($di, KanjiController::class, KanjiController::class, [
            'kanjiRepository' => $di->lazyGet('kanjiRepository'),
            'logger' => $di->lazyGet('defaultLogger'),
            'config' => $di->lazyGet('config')
        ]);
    }

    /**
     * @param Container $di
     * @param $config
     */
    private function jdictController(Container $di, $config)
    {
        $this->createObject($di, JDictController::class, JDictController::class, [
            'jdictRepository' => $di->lazyGet('jdictRepository'),
            'logger' => $di->lazyGet('defaultLogger'),
            'config' => $di->lazyGet('config')
        ]);
    }

    /**
     * @param Container $di
     * @throws \Aura\Di\Exception\ContainerLocked
     * @throws \Aura\Di\Exception\ServiceNotObject
     */
    private function slimCoreServices(Container $di)
    {
        $slimContainer = new \Slim\Container();
        $slimCoreServices = ['settings', 'environment', 'request', 'response', 'router', 'foundHandler', 'phpErrorHandler', 'errorHandler', 'notFoundHandler', 'notAllowedHandler'];
        foreach ($slimCoreServices as $service) {
            $di->set($service, $di->lazy(function () use ($slimContainer, $service) {
                return $slimContainer->get($service);
            }));
        }
        $di->set('callableResolver', $di->lazy(function () use ($di) {
            return new CallableResolver($di);
        }));
    }

}