<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 03/09/2018
 * Time: 23:29
 */

namespace maesierra\Japo\Auth;


class User {

    const USER_ROLE_ADMIN = "admin";
    const USER_ROLE_EDITOR = "editor";
    const USER_ROLE_NONE = "none";

    public $id;
    public $nickname;
    public $email;
    public $picture;
    public $role;

    public function __construct($obj = null) {
        if ($obj) {
            foreach ($obj as $key => $value) {
                if (property_exists(User::class, $key) ) {
                    $this->{$key} = $value;
                }
            }
        }
    }
}
