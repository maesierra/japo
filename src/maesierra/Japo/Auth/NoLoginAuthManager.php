<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 15/09/18
 * Time: 20:51
 */

namespace maesierra\Japo\Auth;


class NoLoginAuthManager implements AuthManager {

    public function login($language) {
        return false;
    }

    public function authCallback() {
    }

    public function getUser() {
        return new User([
            'id' => 0,
            'nickname' => 'user',
            'email' => 'none@user.com',
            'role' => 'admin'
         ]);
    }

    public function isAuthenticated() {
        return true;
    }

    public function logout() {
        return '';
    }
}