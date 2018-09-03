<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 03/09/2018
 * Time: 23:29
 */

namespace maesierra\Japo\Auth;


class User {

    public $id;
    public $nickname;
    public $email;
    public $picture;

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