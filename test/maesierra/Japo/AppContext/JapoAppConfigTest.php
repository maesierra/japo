<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 30/08/2018
 * Time: 23:15
 */

namespace maesierra\Japo\AppContext;

use Dotenv\Dotenv;
use maesierra\Japo\Auth\Auth0AuthManager;
use maesierra\Japo\Auth\NoLoginAuthManager;

if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class JapoAppConfigTest extends \PHPUnit_Framework_TestCase {

    /** @var  JapoAppConfig */
    private $appConfig;


    protected function setUp() {
        parent::setUp();
        $this->appConfig = JapoAppConfig::get(__DIR__);
    }

    public function testDotEnvParam() {
        $dotEnv = $this->createMock(Dotenv::class);
        $this->appConfig->dotEnv = $dotEnv;
        $dotEnv->expects($this->once())->method('load');
        $this->appConfig->getParam('auth0Domain');
        $this->appConfig->auth0Domain;
        $this->appConfig->getParam('auth0ClientId');
        $this->appConfig->auth0ClientId;
    }

    public function testGetParam() {
        $this->assertEquals("auth0Domain", $this->appConfig->getParam('auth0Domain'));
        $this->assertEquals($this->appConfig->getParam('auth0Domain'), $this->appConfig->auth0Domain);
    }

    public function testGetParam_Default() {
        $this->assertFalse($this->appConfig->getParam('homeDir2'));
    }

    public function testGetEnvParam_withDefault() {
        $this->assertEquals('localhost', $this->appConfig->mysqlHost);
        $this->assertEquals(3306, $this->appConfig->mysqlPort);
        $this->assertEquals('japo', $this->appConfig->mysqlUser);
        $this->assertEquals('japo', $this->appConfig->databaseName);
    }


    public function testGetParam_DefaultValue() {
        $this->assertEquals("aa", $this->appConfig->getParam('homeDir2', "aa"));
    }

    public function testServerPath() {
        putenv("SERVER_PATH=/api/japo");
        $this->assertEquals('/api/japo', $this->appConfig->serverPath);
    }

    public function testServerPath_withTrailing() {
        putenv("SERVER_PATH=/api/japo/");
        $this->assertEquals('/api/japo', $this->appConfig->serverPath);
    }

    public function testAuth0RedirectUri() {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('https://localhost:443/api/japo/auth/auth', $this->appConfig->auth0RedirectUri);
    }

    public function testAuth0RedirectUri_noHttps() {
        $_SERVER['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('http://localhost:443/api/japo/auth/auth', $this->appConfig->auth0RedirectUri);
    }

    public function testAuth0LogoutUri() {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('https://localhost:443/japo', $this->appConfig->auth0LogoutUri);
    }

    public function testAuth0LogoutUri_noHttps() {
        $_SERVER['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('http://localhost:443/japo', $this->appConfig->auth0LogoutUri);
    }

    public function testHostUrl() {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('https://localhost:443/api/japo', $this->appConfig->hostUrl);
    }

    public function testHostsUrl_noHttps() {
        $_SERVER['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('http://localhost:443/api/japo', $this->appConfig->hostUrl);
    }



    public function testCliMode() {
        $this->assertTrue($this->appConfig->cliMode);
    }

    public function testRootPath() {
        $path = __DIR__ . '/../../../../';
        $rootPath = realpath($path);
        $this->assertEquals($rootPath, $this->appConfig->rootPath);
    }

    public function testTempDir() {
        $tempDir = realpath(sys_get_temp_dir());
        $this->assertEquals($tempDir, $this->appConfig->tempDir);
    }


    protected function tearDown() {
        JapoAppConfig::clearInstance();
        putenv("SERVER_PATH");
    }

}
