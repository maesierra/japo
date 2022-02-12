<?php


namespace maesierra\Japo\Common\Http;


class HttpHelper {

    /** @var array */
    public $serverVars;

    public function __construct($serverVars = null) {
        $this->serverVars = $serverVars ?: $_SERVER;
    }

    /**
     * @return string host ($_SERVER['HTTP_HOST']) or localhost if not present
     */
    public function getHost() {
        return $this->serverVars['HTTP_HOST'] ?? 'localhost';
    }

    /**
     * @return bool true if the request is https. Using $_SERVER['HTTPS']
     */
    public function isHttps() {
        return ($this->serverVars['HTTPS'] ?? '') == "on";
    }
}