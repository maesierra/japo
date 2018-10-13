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

    /**
     * @param $role string
     * @return bool
     */
    public function hasRole($role) {
        switch ($this->role) {
            case self::USER_ROLE_NONE:
                return $role == self::USER_ROLE_NONE;
            case self::USER_ROLE_EDITOR:
                return in_array($role, [self::USER_ROLE_NONE, self::USER_ROLE_EDITOR]);
            case self::USER_ROLE_ADMIN:
                return true;
        }
    }
}
