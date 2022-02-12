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
    /** @var  String */
    private $homeUrl;

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
        $this->homeUrl = $config->homeUrl;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $userLanguage = $request->getCookieParams()['japo_app_language'] ?? $this->language;
        $serverParams = $request->getServerParams();
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? 'unknown';
        $referrer = $serverParams['HTTP_REFERER'] ?? '';
        $regExp = '/' . preg_quote($this->homeUrl, '/') . '(.+)/';
        if (preg_match($regExp, $referrer, $matches)) {
            $redirectTo = $matches[1];
        } else {
            $redirectTo = null;
        }
        $userAgent = $request->getHeader('HTTP_USER_AGENT');
        $this->logger->info("Login request from host: ".json_encode($remoteAddr)." user agent: ".json_encode($userAgent).($redirectTo ? " redirect to ".$redirectTo : '').".");
        if (!$this->authManager->login($userLanguage, $redirectTo)) {
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
        $redirectTo = $this->authManager->authCallback();
        return $redirectTo ? $response->withHeader('Location', "/$redirectTo") : $this->homeRedirect($response);
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