<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 13/10/2018
 * Time: 15:48
 */

namespace maesierra\Japo\Auth;

use PHPUnit\Framework\TestCase;

class NoLoginAuthManagerTest extends TestCase {

    /** @var  NoLoginAuthManager */
    private $authManager;

    public function setUp():void {
        $this->authManager = new NoLoginAuthManager();
    }

    public function testLogin() {
        $this->assertFalse($this->authManager->login('en', null));
    }

    public function testIsAuthenticated() {
        $this->assertTrue($this->authManager->isAuthenticated());
    }
    public function testLogout() {
        $this->assertEquals('', $this->authManager->logout());
    }

    public function testGetUser() {
        $this->assertEquals(new User([
            'id' => 0,
            'nickname' => 'user',
            'email' => 'none@user.com',
            'role' => User::USER_ROLE_ADMIN
        ]), $this->authManager->getUser());
    }
}