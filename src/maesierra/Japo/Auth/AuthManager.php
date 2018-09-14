<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 13/09/18
 * Time: 15:39
 */

namespace maesierra\Japo\Auth;

interface AuthManager
{
    /**
     * Redirect to the login flow if the user is not already logged in
     * @return boolean true if the redirect flow has been performed, false if the user is already logged in
     */
    public function login();

    public function authCallback();

    /**
     * @return User
     */
    public function getUser();

    /**
     * Checks if the user is authenticated, doing the unauthorized flow.
     * @return bool true if there is an authenticated user in the session
     */
    public function isAuthenticated();

    /**
     * Logs out the current user
     */
    public function logout();
}