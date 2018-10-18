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

    public function setUp() {
        $this->domain = "auth0.domain.com";
        $this->clientId = "8329823947";
        $this->logoutUrl = "http://japo.com/japo";
        /** @var Auth0 $auth0 */
        $auth0 = $this->createMock(Auth0::class);
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $this->authManager = new Auth0AuthManager($auth0, $this->domain, $this->clientId, $this->logoutUrl, $logger);
        $this->auth0 = $auth0;
        $this->logger = $logger;
    }

    public function testLoginRedirect_userNotLoggedIn() {
        $language = 'es';
        $this->auth0->expects($this->once())->method('login')->with(null, null, ['custom_lang' => $language]);
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $this->assertTrue($this->authManager->login($language));
    }

    public function testLoginRedirect_userLoggedIn() {
        $language = 'es';
        $this->auth0->expects($this->never())->method('login');
        $this->auth0->expects($this->once())->method('getUser')->willReturn(['user' => 'user']);
        $this->assertFalse($this->authManager->login($language));
    }

    public function testGetUser() {
        $user =  [
            "sub" => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "name" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            "updated_at" => "2018-09-02T21:01:02.750Z",
            "https://github.com/maesierra/japo/user_metadata" => [],
            "https://github.com/maesierra/japo/app_metadata" => ["role" => "admin"]
        ];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($user);
        $this->assertEquals(new User(['id' => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "email" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            "role" => User::USER_ROLE_ADMIN
        ]), $this->authManager->getUser());
    }

    public function testGetUser_withNoRolesOnMetadata() {
        $user =  [
            "sub" => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "name" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            "updated_at" => "2018-09-02T21:01:02.750Z",
            "https://github.com/maesierra/japo/user_metadata" => [],
            "https://github.com/maesierra/japo/app_metadata" => ["someproperty" => "value"]
        ];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($user);
        $this->assertEquals(new User(['id' => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "email" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            "role" => User::USER_ROLE_NONE
        ]), $this->authManager->getUser());
    }

    public function testGetUser_noAppMetadata() {
        $user =  [
            "sub" => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "name" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            "updated_at" => "2018-09-02T21:01:02.750Z"
        ];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($user);
        $this->assertEquals(new User(['id' => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "email" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            "role" => User::USER_ROLE_NONE
        ]), $this->authManager->getUser());
    }

    public function testGetUser_noUser() {
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $this->assertEquals(null, $this->authManager->getUser());
    }

    public function testAuthCallback() {
        $userInfo = ['user' => 'user'];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($userInfo);
        $this->logger->expects($this->once())->method('info')->with("User ".json_encode($userInfo)." authenticated successfully.");
        $this->authManager->authCallback();
    }

    /**
     * @throws \Auth0\SDK\Exception\ApiException
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function testIsAuthenticated_true() {
        $userInfo = ['user' => 'user'];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($userInfo);
        $this->assertTrue($this->authManager->isAuthenticated());
    }

    /**
     * @throws \Auth0\SDK\Exception\ApiException
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function testIsAuthenticated_false() {
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $this->assertFalse($this->authManager->isAuthenticated());
    }


    public function testLogout_userLoggedIn() {
        $userInfo = ['name' => 'mae@maesierra.net'];
        $this->auth0->expects($this->once())->method('getUser')->willReturn($userInfo);
        $this->auth0->expects($this->once())->method('logout');
        $this->logger->expects($this->once())->method('info')->with("User mae@maesierra.net logged out.");
        $this->assertEquals(
            "http://$this->domain/v2/logout?client_id=$this->clientId&returnTo=$this->logoutUrl",
            $this->authManager->logout()
        );
    }

    public function testLogout_userNotLoggedIn() {
        $this->auth0->expects($this->once())->method('getUser')->willReturn(null);
        $this->auth0->expects($this->never())->method('logout');
        $this->logger->expects($this->never())->method('info');
        $this->assertEquals(
            '',
            $this->authManager->logout()
        );
    }
}