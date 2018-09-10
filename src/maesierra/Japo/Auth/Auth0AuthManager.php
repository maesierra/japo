<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 02/09/2018
 * Time: 1:26
 */

namespace maesierra\Japo\Auth;


use Auth0\SDK\Auth0;
use maesierra\Japo\Router\Router;
use Monolog\Logger;

class Auth0AuthManager {

    /** @var  Auth0 */
    public $auth0;

    /** @var  Logger */
    public $logger;

    /** @var  Router */
    public $router;

    public $auth0Domain;
    public $auth0ClientId;
    public $auth0LogoutUri;

    /**
     * @param Auth0 $auth0
     * @param Router $router
     * @param Logger $logger
     * @param string $auth0Domain
     * @param string $auth0ClientId
     * @param string $auth0LogoutUri
     */
    public function __construct($auth0, $router, $auth0Domain, $auth0ClientId, $auth0LogoutUri, $logger) {
        $this->auth0 = $auth0;
        $this->logger = $logger;
        $this->router = $router;
        $this->auth0Domain = $auth0Domain;
        $this->auth0ClientId = $auth0ClientId;
        $this->auth0LogoutUri = $auth0LogoutUri;
    }

    public function login() {
        $userInfo = $this->auth0->getUser();
        if (!$userInfo) {
            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
            $referrer =  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referrer';
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
            $this->logger->info("Login request from host: $remoteAddr referrer: $referrer user agent: $userAgent.");
            $this->auth0->login();
        } else {
            $this->router->homeRedirect();
        }
    }

    public function authCallback() {
        $userInfo = $this->auth0->getUser();
        $this->logger->info("User ".json_encode($userInfo)." authenticated successfully.");
        $this->router->homeRedirect();
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
     * @param $callback callable only will be called on successful authentication
     * @return bool true if there is an authenticated user in the session
     */
    public function isAuthenticated($callback = null) {
        $logInfo = "User Auth from host: {$_SERVER['REMOTE_ADDR']} user agent: {$_SERVER['HTTP_USER_AGENT']}";
        $authenticated = $this->auth0->getUser() != null;
        if (!$authenticated) {
            $this->logger->info($logInfo." => Unauthorized");
            $this->router->unauthorized();
        } else {
            $this->logger->info($logInfo." => Authorized");
            if ($callback) {
                $callback();
            }
        }
        return $authenticated;
    }

    /**
     * Logs out the current user
     */
    public function logout() {
        $user = $this->getUser();
        if ($user) {
            $this->auth0->logout();
            $logoutUrl = sprintf('http://%s/v2/logout?client_id=%s&returnTo=%s', $this->auth0Domain, $this->auth0ClientId, $this->auth0LogoutUri);
            $this->router->redirectTo($logoutUrl);
            $this->logger->info("User {$user->email} logged out");
        } else {
            $this->router->homeRedirect();
        }
    }


}