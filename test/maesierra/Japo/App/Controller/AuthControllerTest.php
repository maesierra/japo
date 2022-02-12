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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthControllerTest extends TestCase
{

    /** @var MockObject */
    private $authManager;

    /** @var  MockObject */
    private $logger;

    /** @var AuthController */
    private $controller;

    private $language;

    protected function setUp():void
    {
        parent::setUp();
        /** @var AuthManager $authManager */
        $authManager = $this->createMock(AuthManager::class);
        $this->authManager = $authManager;
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $this->language = 'es';
        /** @var JapoAppConfig $config */
        $config = (object) [
            'homePath' => '/japo',
            'homeUrl' => 'https://localhost:443/japo/',
            'lang' => $this->language
        ];
        $this->logger = $logger;
        $this->controller = new AuthController($authManager, $config, $logger);
    }

    public function testLoginRedirect_userNotLoggedIn_noLanguageCookie() {

        $this->authManager->expects($this->once())->method('login')->with($this->language)->willReturn(true);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => $remoteAddr]);
        $this->logger->expects($this->once())->method('info')->with("Login request from host: \"$remoteAddr\" user agent: \"$httpUserAgent\".");
        $response->expects($this->never())->method('withHeader');
        $this->controller->login($request, $response, []);
    }

    public function testLoginRedirect_userNotLoggedIn_noLanguageCookie_withReferrerDirectHit() {

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => $remoteAddr, 'HTTP_REFERER' => 'https://localhost:443/japo/']);
        $this->authManager->expects($this->once())->method('login')->with($this->language, null)->willReturn(true);
        $this->logger->expects($this->once())->method('info')->with("Login request from host: \"$remoteAddr\" user agent: \"$httpUserAgent\".");
        $response->expects($this->never())->method('withHeader');
        $this->controller->login($request, $response, []);
    }

    public function testLoginRedirect_userNotLoggedIn_noLanguageCookie_withExternalReferrer() {

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => $remoteAddr, 'HTTP_REFERER' => 'https://localhost:443/other/page/other?param=1']);
        $this->authManager->expects($this->once())->method('login')->with($this->language)->willReturn(true);
        $this->logger->expects($this->once())->method('info')->with("Login request from host: \"$remoteAddr\" user agent: \"$httpUserAgent\".");
        $response->expects($this->never())->method('withHeader');
        $this->controller->login($request, $response, []);
    }

    public function testLoginRedirect_userNotLoggedIn_noLanguageCookie_withReferrerRedirect() {

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => $remoteAddr, 'HTTP_REFERER' => 'https://localhost:443/japo/page/other?param=1']);
        $this->authManager->expects($this->once())->method('login')->with($this->language, 'page/other?param=1')->willReturn(true);
        $this->logger->expects($this->once())->method('info')->with("Login request from host: \"$remoteAddr\" user agent: \"$httpUserAgent\" redirect to page/other?param=1.");
        $response->expects($this->never())->method('withHeader');
        $this->controller->login($request, $response, []);
    }


    public function testLoginRedirect_userNotLoggedIn_withLanguageCookie() {
        $this->authManager->expects($this->once())->method('login')->with('en')->willReturn(true);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $request->method('getCookieParams')->willReturn(['japo_app_language' => 'en']);
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => $remoteAddr]);
        $this->logger->expects($this->once())->method('info')->with("Login request from host: \"$remoteAddr\" user agent: \"$httpUserAgent\".");
        $response->expects($this->never())->method('withHeader');
        $this->controller->login($request, $response, []);
    }

    public function testLoginRedirect_userAlreadyLoggedIn() {
        $this->authManager->expects($this->once())->method('login')->with($this->language)->willReturn(false);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $remoteAddr = "12.78.39.234";
        $httpUserAgent = 'firefox';
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => $remoteAddr]);
        $request->method('getHeader')->with('HTTP_USER_AGENT')->willReturn($httpUserAgent);
        $this->logger->expects($this->once())->method('info')->with("Login request from host: \"$remoteAddr\" user agent: \"$httpUserAgent\".");
        $response->expects($this->once())->method('withHeader')->with('Location', '/japo');
        $this->assertNull($this->controller->login($request, $response, []));
    }

    public function testAuthCallback_noRedirect() {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $this->authManager->expects($this->once())->method('authCallback');
        $response->expects($this->once())->method('withHeader')->with('Location', '/japo');
        $this->controller->auth($request, $response, []);
    }

    public function testAuthCallback_redirect() {
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $redirectTo = "japo/kanji/details/傘";
        $this->authManager->expects($this->once())->method('authCallback')->willReturn($redirectTo);
        $response->expects($this->once())->method('withHeader')->with('Location', "/$redirectTo");
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