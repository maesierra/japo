<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 03/09/2018
 * Time: 23:32
 */

namespace maesierra\Japo\Auth;


use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {

    public function testConstructor() {
        $user = new User([
            'id' => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "email" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            'role' => User::USER_ROLE_NONE,
            'extra' => 'value'
        ]);
        $this->assertEquals($user->id, "auth0|5b879de94b3e140de3007585");
        $this->assertEquals($user->nickname, "mae");
        $this->assertEquals($user->email, "mae@maesierra.net");
        $this->assertEquals($user->role, User::USER_ROLE_NONE);
        $this->assertEquals($user->picture, "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png");
        $this->assertEquals(['id', "nickname", "email", "picture", "role"], array_keys(get_object_vars ($user)));
    }

    public function testHasRole() {
        $noRoleUser = new User(['role' => User::USER_ROLE_NONE]);
        $editoreUser = new User(['role' => User::USER_ROLE_EDITOR]);
        $admineUser = new User(['role' => User::USER_ROLE_ADMIN]);

        $this->assertTrue($noRoleUser->hasRole(User::USER_ROLE_NONE));
        $this->assertFalse($noRoleUser->hasRole(User::USER_ROLE_EDITOR));
        $this->assertFalse($noRoleUser->hasRole(User::USER_ROLE_ADMIN));
        $this->assertTrue($editoreUser->hasRole(User::USER_ROLE_EDITOR));
        $this->assertTrue($editoreUser->hasRole(User::USER_ROLE_NONE));
        $this->assertFalse($noRoleUser->hasRole(User::USER_ROLE_ADMIN));
        $this->assertTrue($admineUser->hasRole(User::USER_ROLE_ADMIN));
        $this->assertTrue($admineUser->hasRole(User::USER_ROLE_NONE));
        $this->assertTrue($admineUser->hasRole(User::USER_ROLE_EDITOR));

    }
}