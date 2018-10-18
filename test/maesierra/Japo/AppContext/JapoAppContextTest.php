<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 01/04/2018
 * Time: 20:51
 */

namespace maesierra\Japo\AppContext;

use Auth0\SDK\Auth0;
use Doctrine\ORM\EntityManager;
use maesierra\Japo\App\Controller\AuthController;
use maesierra\Japo\App\Controller\DefaultController;
use maesierra\Japo\App\Controller\JDictController;
use maesierra\Japo\App\Controller\KanjiController;
use maesierra\Japo\Auth\Auth0AuthManager;
use maesierra\Japo\Auth\NoLoginAuthManager;
use maesierra\Japo\DB\DBMigration;
use maesierra\Japo\DB\JDictRepository;
use maesierra\Japo\DB\KanjiRepository;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class JapoAppContextTest extends \PHPUnit_Framework_TestCase {

    /** @var  JapoAppContext */
    private $appContext;
    /** @var  JapoAppConfig */
    private $config;

    protected function setUp() {
        parent::setUp();
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST']  = 'localhost:443';
        $this->config = JapoAppConfig::get(__DIR__);


    }

    public function testAuth0Config_nonCli() {
        $this->buildContext();
        $this->config->setParam('cliMode', false);
        $auth0Config = $this->appContext->auth0Config;
        $this->assertTrue(is_array($auth0Config));
        $this->assertEquals($auth0Config, [
            'domain' => $this->config->auth0Domain,
            'client_id' => $this->config->auth0ClientId,
            'client_secret' => $this->config->auth0ClientSecret,
            'redirect_uri' => $this->config->auth0RedirectUri,
            'audience' => 'https://' . $this->config->auth0Domain. '/userinfo',
            'scope' => 'openid profile',
            'persist_id_token' => true,
            'persist_access_token' => true,
            'persist_refresh_token' => true

        ]);
        $this->assertSame($auth0Config, $this->appContext->auth0Config);
    }

    public function testAuth0Config_cliMode() {
        $this->buildContext();
        $auth0Config = $this->appContext->auth0Config;
        $this->assertTrue(is_array($auth0Config));
        $this->assertEquals($auth0Config, [
            'domain' => $this->config->auth0Domain,
            'client_id' => $this->config->auth0ClientId,
            'client_secret' => $this->config->auth0ClientSecret,
            'redirect_uri' => $this->config->auth0RedirectUri,
            'audience' => 'https://' . $this->config->auth0Domain. '/userinfo',
            'scope' => 'openid profile',
            'persist_id_token' => true,
            'persist_access_token' => true,
            'persist_refresh_token' => true,
            'store' => false,
            'state_handler' => false
        ]);
        $this->assertSame($auth0Config, $this->appContext->auth0Config);
    }

    public function testAuth0() {
        $this->buildContext();
        $auth0 = $this->appContext->auth0;
        $this->assertInstanceOf(Auth0::class, $auth0);
        $this->assertSame($auth0, $this->appContext->auth0);
    }

    public function testLogger() {
        $this->buildContext();
        $defaultLogger = $this->appContext->defaultLogger;
        $this->assertInstanceOf(Logger::class, $defaultLogger);
        /** @var StreamHandler $handler */
        $handler = $defaultLogger->getHandlers()[0];
        $this->assertEquals("logs/japo.log", $handler->getUrl());
        $this->assertEquals(Logger::DEBUG, $handler->getLevel());
        $this->assertSame($defaultLogger, $this->appContext->defaultLogger);

    }


    public function testAuthManager_default() {
        $this->buildContext();
        /** @var NoLoginAuthManager $authManager */
        $authManager = $this->appContext->authManager;
        $this->assertInstanceOf(NoLoginAuthManager::class, $authManager);
        $this->assertSame($authManager, $this->appContext->authManager);
    }

    public function testAuthManager_auth0() {
        $this->config->authManager = Auth0AuthManager::class;
        $this->buildContext();
        /** @var Auth0AuthManager $authManager */
        $authManager = $this->appContext->authManager;
        $this->assertInstanceOf(Auth0AuthManager::class, $authManager);
        $this->assertSame($this->appContext->auth0, $authManager->auth0);
        $this->assertSame($this->appContext->defaultLogger, $authManager->logger);
        $this->assertEquals($this->config->auth0Domain, $authManager->auth0Domain);
        $this->assertEquals($this->config->auth0ClientId, $authManager->auth0ClientId);
        $this->assertEquals($this->config->auth0LogoutUri, $authManager->auth0LogoutUri);
        $this->assertSame($authManager, $this->appContext->authManager);
    }

    public function testDBMigration() {
        $this->buildContext();
        $dbMigration = $this->appContext->dbMigration;
        $this->assertInstanceOf(DBMigration::class, $dbMigration);
        $this->assertEquals($this->config->tempDir, $dbMigration->tempDir);
        $this->assertEquals([
            'paths' => [
                'migrations' => [
                    "{$this->config->rootPath}/db/migrations",
                    "{$this->config->rootPath}/db/migrations/{$this->config->lang}"
                 ],
                'seeds' => $this->config->rootPath.'/db/seeds'
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database' => 'production',
                'production' => [
                    'adapter' => 'mysql',
                    'host' => $this->config->mysqlHost,
                    'name' => $this->config->databaseName,
                    'user' => $this->config->mysqlUser,
                    'pass' => $this->config->mysqlPassword,
                    'port' => $this->config->mysqlPort,
                    'charset' => 'utf8',
                ]
            ],
            'version_order' => 'creation'
        ], $dbMigration->config);
        $this->assertSame($dbMigration, $this->appContext->dbMigration);
    }

    public function testEntityManager() {
        $this->buildContext();
        $entityManager = $this->appContext->entityManager;
        $this->assertInstanceOf(EntityManager::class, $entityManager);
        $this->assertSame($entityManager, $this->appContext->entityManager);
    }

    public function testKanjiRepository() {
        $this->buildContext();
        $kanjiRepository = $this->appContext->kanjiRepository;
        $this->assertInstanceOf(KanjiRepository::class, $kanjiRepository);
        $this->assertSame($this->appContext->entityManager, $kanjiRepository->entityManager);
        $this->assertSame($this->appContext->defaultLogger, $kanjiRepository->logger);
        $this->assertSame($kanjiRepository, $this->appContext->kanjiRepository);
    }

    public function testJDictRepository() {
        $this->buildContext();
        $jdictRepository = $this->appContext->jdictRepository;
        $this->assertInstanceOf(JDictRepository::class, $jdictRepository);
        $this->assertSame($this->appContext->entityManager, $jdictRepository->entityManager);
        $this->assertSame($this->appContext->defaultLogger, $jdictRepository->logger);
        $this->assertSame($jdictRepository, $this->appContext->jdictRepository);
    }


    public function testAuthController() {
        $this->buildContext();
        /** @var AuthController $authController */
        $authController  = $this->appContext->get(AuthController::class);
        $this->assertInstanceOf(AuthController::class, $authController);
        $this->assertSame($this->appContext->defaultLogger, $authController->logger);
        $this->assertSame($this->config, $authController->config);
        $this->assertSame($this->appContext->authManager, $authController->authManager);
        $this->assertSame($authController, $this->appContext->get(AuthController::class));
    }

    public function testDefaultController() {
        $this->buildContext();
        /** @var DefaultController $defaultController */
        $defaultController  = $this->appContext->get(DefaultController::class);
        $this->assertInstanceOf(DefaultController::class, $defaultController);
        $this->assertSame($this->appContext->defaultLogger, $defaultController->logger);
        $this->assertSame($this->config, $defaultController->config);
        $this->assertSame($defaultController, $this->appContext->get(DefaultController::class));
    }


    public function testKanjiController() {
        $this->buildContext();
        /** @var KanjiController $kanjiController */
        $kanjiController  = $this->appContext->get(KanjiController::class);
        $this->assertInstanceOf(KanjiController::class, $kanjiController);
        $this->assertSame($this->appContext->defaultLogger, $kanjiController->logger);
        $this->assertSame($this->config, $kanjiController->config);
        $this->assertSame($this->appContext->kanjiRepository, $kanjiController->kanjiRepository);
        $this->assertSame($kanjiController, $this->appContext->get(KanjiController::class));
    }

    public function testJDictController() {
        $this->buildContext();
        /** @var JDictController $jdictController */
        $jdictController  = $this->appContext->get(JDictController::class);
        $this->assertInstanceOf(JDictController::class, $jdictController);
        $this->assertSame($this->appContext->defaultLogger, $jdictController->logger);
        $this->assertSame($this->config, $jdictController->config);
        $this->assertSame($this->appContext->jdictRepository, $jdictController->jdictRepository);
        $this->assertSame($jdictController, $this->appContext->get(JDictController::class));
    }


    public function testConfig() {
        $this->buildContext();
        $this->assertEquals('auth0Domain',$this->appContext->config->auth0Domain);
    }

    protected function tearDown() {
        JapoAppConfig::clearInstance();
        JapoAppContext::clearInstance();
    }

    protected function buildContext()
    {
        $this->appContext = JapoAppContext::context();
    }

}