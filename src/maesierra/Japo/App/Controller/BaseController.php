<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 14/09/18
 * Time: 12:26
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\AppContext\JapoAppConfig;
use maesierra\Japo\Auth\User;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BaseController
{

    /**
     * @var Logger
     */
    public $logger;

    /** @var JapoAppConfig */
    public $config;

    /**
     * BaseController constructor.
     * @param $logger Logger
     * @param $config JapoAppConfig
     */
    public function __construct($config, $logger) {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param $response ResponseInterface
     * @return ResponseInterface
     */
    protected function homeRedirect($response) {
        return $response->withHeader('Location', $this->config->homePath);
    }

    /**
     * @param $request ServerRequestInterface
     * @return User
     */
    protected function getUserFromRequest($request) {
        return $request->getAttribute("user");
    }
}