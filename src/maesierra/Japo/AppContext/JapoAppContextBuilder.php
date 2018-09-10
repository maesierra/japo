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
use Doctrine\Common\Cache\ApcCache;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use maesierra\Japo\Auth\Auth0AuthManager;
use maesierra\Japo\DB\DBMigration;
use maesierra\Japo\DB\KanjiRepository;
use maesierra\Japo\Entity\KanjiCatalog;
use maesierra\Japo\Router\Router;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

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
        $this->auth0Config($di, $config);
        $this->auth0($di, $config);
        $this->defaultLogger($di, $config);
        $this->router($di, $config);
        $this->authManager($di, $config);
        $this->dbMigration($di, $config);
        $this->entityManager($di, $config);
        $this->kanjiRepository($di, $config);
    }

    /**
     * @param Container $di
     * @param JapoAppConfig $config
     */
    private function dbMigration(Container $di, $config) {
        $dbConfig = [
            'adapter' => 'mysql',
            'host' => $config->mysqlHost,
            'name' => $config->databaseName,
            'user' => $config->mysqlUser,
            'pass' => $config->mysqlPassword,
            'port' => $config->mysqlPort,
            'charset' => 'utf8',
        ];
        $dbMigrationConfig = [
            'paths' => [
                'migrations' => $config->rootPath.'/db/migrations',
                'seeds' => $config->rootPath.'/db/seeds'
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database' => 'development',
                'production' => $dbConfig,
                'development' => $dbConfig,
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
     * @param $config
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
     * @param $config
     */
    private function router(Container $di, $config)
    {
        $this->createObject($di, 'router', Router::class, [
            'backendPath' => $config->serverPath,
            'frontendPath' => $config->homePath
        ]);
    }

    /**
     * @param Container $di
     * @param $config
     */
    private function authManager(Container $di, $config)
    {
        $this->createObject($di, 'authManager', Auth0AuthManager::class, [
            'auth0' => $di->lazyGet('auth0'),
            'router' => $di->lazyGet('router'),
            'logger' => $di->lazyGet('defaultLogger'),
            'auth0Domain' => $config->auth0Domain,
            'auth0ClientId' => $config->auth0ClientId,
            'auth0LogoutUri' => $config->auth0LogoutUri
        ]);
    }

    /**
     * @param Container $di
     * @param $config JapoAppConfig
     */
    private function entityManager(Container $di, $config) {
        $di->set('entityManager', $di->lazy(function() use($config) {
            $reflector = new \ReflectionClass(KanjiCatalog::class);
            $doctrineConfig = Setup::createAnnotationMetadataConfiguration(
                [dirname($reflector->getFileName())]
            );
            $cache = new ApcCache();
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

}