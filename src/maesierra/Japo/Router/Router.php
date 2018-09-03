<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 03/09/2018
 * Time: 1:52
 */

namespace maesierra\Japo\Router;


class Router {

    /** @var  string */
    public $backendPath;

    /** @var  string */
    public $frontendPath;

    /**
     * Router constructor.
     * @param string $backendPath
     * @param string $frontendPath
     */
    public function __construct($backendPath, $frontendPath) {
        $this->backendPath = $backendPath;
        $this->frontendPath = $frontendPath;
    }

    public function redirectTo($path) {
        header("Location:$path");
    }

    public function homeRedirect() {
        $this->redirectTo("{$this->frontendPath}/");
    }

    public function unauthorized() {
        http_response_code(401);
        die('Unauthorized');
    }

}