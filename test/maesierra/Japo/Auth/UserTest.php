<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 03/09/2018
 * Time: 23:32
 */

namespace maesierra\Japo\Auth;


if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class UserTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor() {
        $user = new User([
            'id' => "auth0|5b879de94b3e140de3007585",
            "nickname" => "mae",
            "email" => "mae@maesierra.net",
            "picture" => "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png",
            'extra' => 'value'
        ]);
        $this->assertEquals($user->id, "auth0|5b879de94b3e140de3007585");
        $this->assertEquals($user->nickname, "mae");
        $this->assertEquals($user->email, "mae@maesierra.net");
        $this->assertEquals($user->picture, "https:\\/\\/s.gravatar.com\\/avatar\\/35685adfdc0e9f5b3816c58954487b39?s=480&r=pg&d=https%3A%2F%2Fcdn.auth0.com%2Favatars%2Fma.png");
        $this->assertEquals(['id', "nickname", "email", "picture"], array_keys(get_object_vars ($user)));
    }
}
