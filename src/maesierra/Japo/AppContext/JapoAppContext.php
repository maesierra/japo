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
use maesierra\Japo\Auth\AuthManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class JapoAppContext
 * Simple DI to have everything under control
 * @package maesierra\PiControl\JapoAppContext
 *
 *
 * @property array $auth0Config
 *
 * @property \Auth0\SDK\Auth0 $auth0
 *
 * @property \Monolog\Logger $defaultLogger
 *
 * @property AuthManager $authManager
 *
 * @property \Doctrine\ORM\EntityManager $entityManager
 *
 * @property \maesierra\Japo\DB\DBMigration $dbMigration
 *
 * @property \maesierra\Japo\DB\KanjiRepository $kanjiRepository
 *
 * @property \maesierra\Japo\DB\JDictRepository $jdictRepository
 *
 * @property \maesierra\Japo\AppContext\JapoAppConfig $config
 *
 */
class JapoAppContext implements ContainerInterface {



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
    public static function context() {
        if (!self::$instance) {
            self::$instance = new JapoAppContext();
        }
        return self::$instance;
    }

    public static function clearInstance() {
        self::$instance = null;
    }

    public function has($id) {
        return $this->di->has($id);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id) {
        return $this->di->get($id);
    }
}

