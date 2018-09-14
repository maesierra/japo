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
use maesierra\Japo\App\Controller\KanjiController;
use maesierra\Japo\Auth\Auth0AuthManager;
use maesierra\Japo\DB\DBMigration;
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
        $this->appContext = JapoAppContext::context();

    }

    public function testAuth0Config_nonCli() {
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
        $auth0 = $this->appContext->auth0;
        $this->assertInstanceOf(Auth0::class, $auth0);
        $this->assertSame($auth0, $this->appContext->auth0);
    }

    public function testLogger() {
        $defaultLogger = $this->appContext->defaultLogger;
        $this->assertInstanceOf(Logger::class, $defaultLogger);
        /** @var StreamHandler $handler */
        $handler = $defaultLogger->getHandlers()[0];
        $this->assertEquals("logs/japo.log", $handler->getUrl());
        $this->assertEquals(Logger::DEBUG, $handler->getLevel());
        $this->assertSame($defaultLogger, $this->appContext->defaultLogger);

    }


    public function testAuthManager() {
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
        $dbMigration = $this->appContext->dbMigration;
        $this->assertInstanceOf(DBMigration::class, $dbMigration);
        $this->assertEquals($this->config->tempDir, $dbMigration->tempDir);
        $this->assertSame($dbMigration, $this->appContext->dbMigration);
    }

    public function testEntityManager() {
        $entityManager = $this->appContext->entityManager;
        $this->assertInstanceOf(EntityManager::class, $entityManager);
        $this->assertSame($entityManager, $this->appContext->entityManager);
    }

    public function testKanjiRepository() {
        $kanjiRepository = $this->appContext->kanjiRepository;
        $this->assertInstanceOf(KanjiRepository::class, $kanjiRepository);
        $this->assertSame($this->appContext->entityManager, $kanjiRepository->entityManager);
        $this->assertSame($this->appContext->defaultLogger, $kanjiRepository->logger);
        $this->assertSame($kanjiRepository, $this->appContext->kanjiRepository);
    }

    public function testAuthController() {
        /** @var AuthController $authController */
        $authController  = $this->appContext->get(AuthController::class);
        $this->assertInstanceOf(AuthController::class, $authController);
        $this->assertSame($this->appContext->defaultLogger, $authController->logger);
        $this->assertSame($this->config, $authController->config);
        $this->assertSame($this->appContext->authManager, $authController->authManager);
        $this->assertSame($authController, $this->appContext->get(AuthController::class));
    }

    public function testKanjiController() {
        /** @var KanjiController $kanjiController */
        $kanjiController  = $this->appContext->get(KanjiController::class);
        $this->assertInstanceOf(KanjiController::class, $kanjiController);
        $this->assertSame($this->appContext->defaultLogger, $kanjiController->logger);
        $this->assertSame($this->config, $kanjiController->config);
        $this->assertSame($this->appContext->kanjiRepository, $kanjiController->kanjiRepository);
        $this->assertSame($kanjiController, $this->appContext->get(KanjiController::class));
    }

    public function testConfig() {
        $this->assertEquals('auth0Domain',$this->appContext->config->auth0Domain);
    }

    protected function tearDown() {
        JapoAppConfig::clearInstance();
        JapoAppContext::clearInstance();
    }

}