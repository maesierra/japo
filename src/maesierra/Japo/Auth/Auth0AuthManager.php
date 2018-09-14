<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 02/09/2018
 * Time: 1:26
 */

namespace maesierra\Japo\Auth;


use Auth0\SDK\Auth0;
use Monolog\Logger;

class Auth0AuthManager implements AuthManager
{

    /** @var  Auth0 */
    public $auth0;

    /** @var  Logger */
    public $logger;

    public $auth0Domain;
    public $auth0ClientId;
    public $auth0LogoutUri;

    /**
     * @param Auth0 $auth0
     * @param string $auth0Domain
     * @param string $auth0ClientId
     * @param string $auth0LogoutUri
     * @param Logger $logger
     */
    public function __construct($auth0, $auth0Domain, $auth0ClientId, $auth0LogoutUri, $logger) {
        $this->auth0 = $auth0;
        $this->logger = $logger;
        $this->auth0Domain = $auth0Domain;
        $this->auth0ClientId = $auth0ClientId;
        $this->auth0LogoutUri = $auth0LogoutUri;
    }

    public function login() {
        $userInfo = $this->auth0->getUser();
        if (!$userInfo) {
            $this->auth0->login();
            return true;
        } else {
            return false;
        }
    }

    public function authCallback() {
        $userInfo = $this->auth0->getUser();
        $this->logger->info("User ".json_encode($userInfo)." authenticated successfully.");
    }

    /**
     * @return User
     */
    public function getUser() {
        $auth0User = $this->auth0->getUser();
        return $auth0User ? new User([
            'id' => isset($auth0User['sub']) ? $auth0User['sub'] : null,
            'nickname' => isset($auth0User['nickname']) ? $auth0User['nickname'] : null,
            'email' => isset($auth0User['name']) ? $auth0User['name'] : null,
            'picture' => isset($auth0User['picture']) ? $auth0User['picture'] : null
        ]) : null;
    }

    /**
     * Checks if the user is authenticated, doing the unauthorized flow.
     * @return bool true if there is an authenticated user in the session
     * @throws \Auth0\SDK\Exception\ApiException
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function isAuthenticated() {
        return $this->auth0->getUser() != null;
    }

    /**
     * Logs out the current user
     * @return string logout url or false if the user is not logged in
     */
    public function logout() {
        $user = $this->getUser();
        if ($user) {
            $this->auth0->logout();
            $logoutUrl = sprintf('http://%s/v2/logout?client_id=%s&returnTo=%s', $this->auth0Domain, $this->auth0ClientId, $this->auth0LogoutUri);
            $this->logger->info("User {$user->email} logged out.");
            return $logoutUrl;
        }
        return '';
    }


}