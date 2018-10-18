<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 12/09/18
 * Time: 22:45
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\AppContext\JapoAppConfig;
use maesierra\Japo\Auth\AuthManager;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends BaseController {


    /** @var AuthManager */
    public $authManager;
    /** @var  String */
    private $language;

    /**
     * AuthController constructor.
     * @param Logger $logger
     * @param AuthManager $authManager
     * @param JapoAppConfig $config
     */
    public function __construct($authManager, $config, $logger) {
        parent::__construct($config, $logger);
        $this->authManager = $authManager;
        $this->language = $config->lang;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $userLanguage = isset($_COOKIE['japo_app_language']) ? $_COOKIE['japo_app_language'] : $this->language;
        $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        $userAgent = $request->getHeader('HTTP_USER_AGENT');
        $this->logger->info("Login request from host: ".json_encode($remoteAddr)." user agent: ".json_encode($userAgent).".");
        if (!$this->authManager->login($userLanguage)) {
            return $this->homeRedirect($response);
        }
    }

    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function auth($request, $response, $args) {
        $this->authManager->authCallback();
        return $this->homeRedirect($response);
    }

    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function logout($request, $response, $args) {
        $logoutUrl = $this->authManager->logout();
        if ($logoutUrl) {
            return $response->withHeader('Location', $logoutUrl);
        } else {
            return $this->homeRedirect($response);
        }

    }
}