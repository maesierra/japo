<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 30/08/2018
 * Time: 23:15
 */

namespace maesierra\Japo\AppContext;

use Dotenv\Dotenv;
use maesierra\Japo\Common\Http\HttpHelper;
use PHPUnit\Framework\TestCase;

class JapoAppConfigTest extends TestCase {

    /** @var  JapoAppConfig */
    private $appConfig;

    /** @var HttpHelper */
    private $httpHelper;

    protected function setUp():void {
        parent::setUp();
        $this->appConfig = JapoAppConfig::get(__DIR__);
        $this->httpHelper = $this->appConfig->httpHelper;
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
        $this->assertEquals('es', $this->appConfig->lang);
    }


    public function testGetParam_DefaultValue() {
        $this->assertEquals("aa", $this->appConfig->getParam('homeDir2', "aa"));
    }

    public function testServerPath() {
        putenv("SERVER_PATH=/japo/api");
        $this->assertEquals('/japo/api', $this->appConfig->serverPath);
    }

    public function testServerPath_withTrailing() {
        putenv("SERVER_PATH=/japo/api/");
        $this->assertEquals('/japo/api', $this->appConfig->serverPath);
    }

    public function testAuth0RedirectUri() {
        $this->httpHelper->serverVars['HTTPS'] = 'on';
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('https://localhost:443/auth/auth', $this->appConfig->auth0RedirectUri);
    }

    public function testAuth0RedirectUri_noHttps() {
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('http://localhost:443/auth/auth', $this->appConfig->auth0RedirectUri);
    }

    public function testAuth0LogoutUri() {
        $this->httpHelper->serverVars['HTTPS'] = 'on';
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('https://localhost:443/japo', $this->appConfig->auth0LogoutUri);
    }

    public function testAuth0LogoutUri_noHttps() {
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('http://localhost:443/japo', $this->appConfig->auth0LogoutUri);
    }

    public function testHostUrl() {
        $this->httpHelper->serverVars['HTTPS'] = 'on';
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('https://localhost:443', $this->appConfig->hostUrl);
    }

    public function testHostsUrl_noHttps() {
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('http://localhost:443', $this->appConfig->hostUrl);
    }

    public function testHomeUrl() {
        $this->httpHelper->serverVars['HTTPS'] = 'on';
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('https://localhost:443/japo', $this->appConfig->homeUrl);
    }

    public function testHomesUrl_noHttps() {
        $this->httpHelper->serverVars['HTTP_HOST']  = 'localhost:443';
        $this->assertEquals('http://localhost:443/japo', $this->appConfig->homeUrl);
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


    protected function tearDown():void {
        JapoAppConfig::clearInstance();
        putenv("SERVER_PATH");
    }

}
