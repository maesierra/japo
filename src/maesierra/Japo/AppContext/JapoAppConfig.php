<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 28/06/2018
 * Time: 2:18
 */

namespace maesierra\Japo\AppContext;

use Dotenv\Dotenv;
use maesierra\Japo\Auth\NoLoginAuthManager;
use maesierra\Japo\Common\Http\HttpHelper;


/**
 * Class JapoAppConfig
 * @package maesierra\PiControl\JapoAppContext
 *
 * @property $auth0Domain
 *
 * @property $auth0ClientId
 *
 * @property $serverPath
 *
 * @property $auth0RedirectUri
 *
 * @property $auth0LogoutUri
 *
 * @property boolean $cliMode
 *
 * @property string $hostUrl
 *
 * @property string $logPath
 *
 * @property string $logLevel
 *
 * @property $auth0ClientSecret
 *
 * @property string $rootPath
 *
 * @property string $mysqlPort
 *
 * @property string $mysqlHost
 *
 * @property string $mysqlUser
 *
 * @property string $mysqlPassword
 *
 * @property string $databaseName
 *
 * @property string $tempDir
 *
 * @property string $authManager
 *
 * @property string $lang
 *
 * @property string $homePath
 *
 * @property string $homeUrl

 */

class JapoAppConfig {

    /** @var  Dotenv */
    public $dotEnv;

    /** @var HttpHelper */
    public $httpHelper;

    private $params;

    /** @var  string */
    private $dotEnvPath;

    /** @var  JapoAppConfig */
    private static $instance;


    private function __construct($dotEnvPath = null, $httpHelper = null) {
        $this->dotEnvPath = $dotEnvPath ?: __DIR__.'/../';
        $this->httpHelper = $httpHelper ?: new HttpHelper();
    }

    /**
     * Updates a param value
     * @param $param string
     * @param $value
     */
    public function setParam($param, $value) {
        $this->getParam($param);
        $this->params[$param] = $value;
    }

    private function getEnv($param, $default = null) {
        $value = getenv($param);
        if (!$value && $default) {
            $value = $default;
        }
        return $value;
    }

    public function getParam($param, $default = false) {
        if (!isset($this->params)) {
            $this->dotEnv = $this->dotEnv ?: new Dotenv($this->dotEnvPath);
            try {
                $this->dotEnv->load();
            } catch(\InvalidArgumentException $ex) {
                // Ignore if no dotenv
            }
            $params = [];
            $params['auth0Domain'           ] = $this->getEnv('AUTH0_DOMAIN');
            $params['auth0ClientId'         ] = $this->getEnv('AUTH0_CLIENT_ID');
            $params['auth0ClientSecret'     ] = $this->getEnv('AUTH0_CLIENT_SECRET');
            $params['serverPath'            ] = $this->getEnv('SERVER_PATH');
            $params['logPath'               ] = $this->getEnv('LOG_FOLDER');
            $params['logLevel'              ] = $this->getEnv('LOG_LEVEL');
            $params['homePath'              ] = $this->getEnv('HOME_PATH');
            $params['tempDir'               ] = $this->getEnv('TEMP_DIR');
            $params['mysqlHost'             ] = $this->getEnv('MYSQL_HOST', 'localhost');
            $params['mysqlPort'             ] = $this->getEnv('MYSQL_PORT', '3306');
            $params['mysqlUser'             ] = $this->getEnv('MYSQL_USER', 'japo');
            $params['mysqlPassword'         ] = $this->getEnv('MYSQL_PASSWORD');
            $params['databaseName'          ] = $this->getEnv('DATABASE_NAME', 'japo');
            $params['lang'                  ] = $this->getEnv('JAPO_APP_LANGUAGE', 'es');
            $params['authManager'           ] = $this->getEnv('AUTH_MANAGER', NoLoginAuthManager::class);
            if (substr($params['serverPath'], -1, 1) == '/') {
                $params['serverPath'] = substr($params['serverPath'], 0, -1);
            }
            $httpHost = $this->httpHelper->getHost();
            $httpsEnabled = $this->httpHelper->isHttps();
            $protocol = $httpsEnabled ? "https" : "http";
            $params['hostUrl'] = "$protocol://$httpHost{$params['serverPath']}";
            $params['homeUrl'] = "$protocol://$httpHost{$params['homePath']}";
            $params['auth0RedirectUri'] = "{$params['hostUrl']}/auth/auth";
            $params['auth0LogoutUri'] = $params['homeUrl'];
            $params['cliMode'] = php_sapi_name() == "cli";
            $rootPath = __DIR__ . '/../../../../';
            $params['rootPath'] = realpath($rootPath);
            if (!$params['tempDir']) {
                $params['tempDir'] = realpath(sys_get_temp_dir());
            }
            $this->params = $params;

        }
        return isset($this->params[$param]) ? $this->params[$param] : $default;

    }

    function __get($name) {
        return $this->getParam($name);
    }

    function __set($name, $value) {
        $this->setParam($name, $value);
    }

    /**
     * @param $dotEnvPath string path for the folder where the .env file is located
     * @return JapoAppConfig
     */
    public static function get($dotEnvPath = null, $httpHelper = null) {
        if (!self::$instance ) {
            self::$instance = new JapoAppConfig($dotEnvPath, $httpHelper);
        }
        return self::$instance;
    }

    public static function clearInstance() {
        self::$instance = null;
    }

}