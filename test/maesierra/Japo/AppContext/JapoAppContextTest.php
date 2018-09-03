<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 01/04/2018
 * Time: 20:51
 */

namespace maesierra\Japo\AppContext;

use Auth0\SDK\Auth0;
use maesierra\Japo\Auth\Auth0AuthManager;
use maesierra\Japo\Router\Router;
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
        $this->appContext = JapoAppContext::get();

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

    public function testRouter() {
        $router = $this->appContext->router;
        $this->assertInstanceOf(Router::class, $router);
        $this->assertEquals($this->config->serverPath, $router->backendPath);
        $this->assertEquals($this->config->homePath, $router->frontendPath);
        $this->assertSame($router, $this->appContext->router);
    }

    public function testAuthManager() {
        $authManager = $this->appContext->authManager;
        $this->assertInstanceOf(Auth0AuthManager::class, $authManager);
        $this->assertSame($this->appContext->auth0, $authManager->auth0);
        $this->assertSame($this->appContext->router, $authManager->router);
        $this->assertSame($this->appContext->defaultLogger, $authManager->logger);
        $this->assertEquals($this->config->auth0Domain, $authManager->auth0Domain);
        $this->assertEquals($this->config->auth0ClientId, $authManager->auth0ClientId);
        $this->assertEquals($this->config->auth0LogoutUri, $authManager->auth0LogoutUri);
        $this->assertSame($authManager, $this->appContext->authManager);
    }
    protected function tearDown() {
        JapoAppConfig::clearInstance();
        JapoAppContext::clearInstance();
    }

}