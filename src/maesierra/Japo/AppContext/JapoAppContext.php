<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 30/07/16
 * Time: 11:18
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\AppContext;
use Aura\Di\Container;
use Aura\Di\ContainerBuilder;

/**
 * Class JapoAppContext
 * Simple DI to have everything under control
 * @package maesierra\PiControl\JapoAppContext
 *
 * @property  $hello
 * @property array $auth0Config
 * @property \Auth0\SDK\Auth0 $auth0
 * @property \Monolog\Logger $defaultLogger
 * @property \maesierra\Japo\Auth\Auth0AuthManager $authManager
 *
 * @property \maesierra\Japo\DB\DBMigration $dbMigration
 * @property \maesierra\Japo\Router\Router $router
 *
 */
class JapoAppContext {



    /** @var  JapoAppContext */
    private static $instance;

    /**
     * @var Container
     */
    private $di;

    /**
     * JapoAppContext constructor.
     */
    public function __construct() {
        $builder = new ContainerBuilder();
        $this->di = $builder->newConfiguredInstance([new JapoAppContextBuilder($this)]);
    }

    function __get($name) {
        return $this->di->get($name);
    }


    /**
     * @return JapoAppContext
     */
    public static function get() {
        if (!self::$instance) {
            self::$instance = new JapoAppContext();
        }
        return self::$instance;
    }

    public static function clearInstance() {
        self::$instance = null;
    }

}

