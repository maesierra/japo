<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 02/09/2018
 * Time: 1:26
 */

namespace maesierra\Japo\Auth;


use Auth0\SDK\Auth0;
use maesierra\Japo\Router\Router;
use Monolog\Logger;

if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class Auth0AuthManagerTest extends \PHPUnit_Framework_TestCase {
    private $domain;
    private $clientId;
    private $logoutUrl;

    /** @var  Auth0AuthManager */
    private $authManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $auth0;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $router;


    public function setUp() {
        $this->domain = "auth0.domain.com";
        $this->clientId = "8329823947";
        $this->logoutUrl = "http://japo.com/japo";
        /** @var Auth0 $auth0 */
        $auth0 = $this->createMock(Auth0::class);
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        /** @var Router $router */
        $router = $this->createMock(Router::class);
        $this->authManager = new Auth0AuthManager($auth0, $router, $this->domain, $this->clientId, $this->logoutUrl, $logger);
        $this->auth0 = $auth0;
        $this->logger = $logger;
        $this->router = $router;
    }

    public function testLoginRedirect_userNotLoggedIn() {
        $this->auth0->expects($this->once())->method('login');
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $remoteAddr = "12.78.39.234";
        $httpReferer = 'https://myreferrer';
        $httpUserAgent = 'firefox';
        $_SERVER['REMOTE_ADDR'] = $remoteAddr;
        $_SERVER['HTTP_REFERER'] = $httpReferer;
        $_SERVER['HTTP_USER_AGENT'] = $httpUserAgent;
        $this->logger->expects($this->once())->method('info', "Login request from host: $remoteAddr referer: $httpReferer user agent:  $httpUserAgent.");
        $this->router->expects($this->never())->method('homeRedirect');
        $this->authManager->login();
    }

    public function testLoginRedirect_userLoggedIn() {
        $this->auth0->expects($this->never())->method('login');
        $this->auth0->expects($this->once())->method('getUser')->willReturn(['user' => 'user']);
        $this->logger->expects($this->never())->method('info');
        $this->router->expects($this->once())->method('homeRedirect');
        $this->authManager->login();
    }

    public function testGetUser() {
        $user =  ["sub" => "auth0|5b879de94b3e140de3007585","nickname" => "mae","name" => "mae@maesierra.net","picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png","updated_a
t" => "2018-09-02T21:01:02.750Z"];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($user);
        $this->assertEquals(new User(['id' => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "email" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png"
        ]), $this->authManager->getUser());
    }

    public function testGetUser_noUser() {
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $this->assertEquals(null, $this->authManager->getUser());
    }

    public function testAuthCallback() {
        $userInfo = ['user' => 'user'];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($userInfo);
        $this->logger->expects($this->once())->method('info', "User ".json_encode($userInfo)." authenticated successfully.");
        $this->router->expects($this->once())->method('homeRedirect');
        $this->authManager->authCallback();
    }

    public function testIsAuthenticated_true() {
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $_SERVER['REMOTE_ADDR'] = $remoteAddr;
        $_SERVER['HTTP_USER_AGENT'] = $httpUserAgent;
        $userInfo = ['user' => 'user'];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($userInfo);
        $this->logger->expects($this->once())->method('info', "User Auth from host: $remoteAddr user agent:  $httpUserAgent => Authorized.");
        $this->router->expects($this->never())->method('unauthorized');
        $this->assertTrue($this->authManager->isAuthenticated());
    }

    public function testIsAuthenticated_false() {
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $_SERVER['REMOTE_ADDR'] = $remoteAddr;
        $_SERVER['HTTP_USER_AGENT'] = $httpUserAgent;
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $this->logger->expects($this->once())->method('info', "User Auth from host: $remoteAddr user agent:  $httpUserAgent => Unauthorized.");
        $this->router->expects($this->once())->method('unauthorized');
        $this->assertFalse($this->authManager->isAuthenticated());
    }

    public function testLogout_userLoggedIn() {
        $userInfo = ['email' => 'mae@maesierra.net'];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($userInfo);
        $this->auth0->expects($this->once())->method('logout');
        $this->logger->expects($this->once())->method('info', "User mae@maesierra.net logged out.");
        $this->router->expects($this->once())->method('redirectTo')->with("http://$this->domain/v2/logout?client_id=$this->clientId&returnTo=$this->logoutUrl");
        $this->authManager->logout();
    }

    public function testLogout_userNotLoggedIn() {
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $this->auth0->expects($this->never())->method('logout');
        $this->router->expects($this->never())->method('redirectTo');
        $this->router->expects($this->once())->method('homeRedirect');
        $this->authManager->logout();
    }

}
