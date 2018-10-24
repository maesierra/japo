<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 13/10/2018
 * Time: 15:48
 */

namespace maesierra\Japo\Auth;

if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class NoLoginAuthManagerTest extends \PHPUnit_Framework_TestCase {

    /** @var  NoLoginAuthManager */
    private $authManager;

    public function setUp() {
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