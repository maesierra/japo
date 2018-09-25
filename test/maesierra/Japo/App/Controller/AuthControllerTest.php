<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 13/09/18
 * Time: 15:43
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\AppContext\JapoAppConfig;
use maesierra\Japo\Auth\AuthManager;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (file_exists('../../../../../vendor/autoload.php')) include '../../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class AuthControllerTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $authManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var AuthController */
    private $controller;


    protected function setUp()
    {
        parent::setUp();
        /** @var AuthManager $authManager */
        $authManager = $this->createMock(AuthManager::class);
        $this->authManager = $authManager;
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $config = (object) [
            'homePath' => '/japo'
        ];
        $this->logger = $logger;
        $this->controller = new AuthController($authManager, $config, $logger);
    }

    public function testLoginRedirect_userNotLoggedIn()
    {

        $this->authManager->expects($this->once())->method('login')->willReturn(true);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $_SERVER['REMOTE_ADDR'] = $remoteAddr;
        $this->logger->expects($this->once())->method('info')->with("Login request from host: $remoteAddr user agent: $httpUserAgent.");
        $response->expects($this->never())->method('withHeader');
        $this->controller->login($request, $response, []);
    }

    public function testLoginRedirect_userAlreadyLoggedIn()
    {

        $this->authManager->expects($this->once())->method('login')->willReturn(false);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $_SERVER['REMOTE_ADDR'] = $remoteAddr;
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $this->logger->expects($this->once())->method('info')->with("Login request from host: $remoteAddr user agent: $httpUserAgent.");
        $response->expects($this->once())->method('withHeader')->with('Location', '/japo');
        $this->assertNull($this->controller->login($request, $response, []));
    }

    public function testAuthCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $this->authManager->expects($this->once())->method('authCallback');
        $response->expects($this->once())->method('withHeader')->with('Location', '/japo');
        $this->controller->auth($request, $response, []);
    }

    public function testLogout_userLoggedIn()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $redirect = "redirect";
        $this->authManager->expects($this->once())->method('logout')->willReturn($redirect);
        $response->expects($this->once())->method('withHeader')->with('Location', $redirect);
        $this->controller->logout($request, $response, []);
    }

    public function testLogout_userNotLoggedIn()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $this->authManager->expects($this->once())->method('logout')->willReturn('');
        $response->expects($this->once())->method('withHeader')->with('Location', '/japo');
        $this->controller->logout($request, $response, []);
    }
}
