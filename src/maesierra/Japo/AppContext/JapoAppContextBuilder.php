<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 28/06/2018
 * Time: 1:22
 */

namespace maesierra\Japo\AppContext;


use Aura\Di\Container;
use Aura\Di\ContainerConfig;
use Auth0\SDK\Auth0;
use maesierra\Japo\Auth\Auth0AuthManager;
use maesierra\Japo\Router\Router;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

class JapoAppContextBuilder extends ContainerConfig {

    /** @var  JapoAppContext */
    private $appContext;


    /**
     * JapoAppContextBuilder constructor.
     * @param JapoAppContext $appContext
     */
    public function __construct($appContext) {
       $this->appContext = $appContext;
    }

    /**
     * @param $di Container
     * @param $name string 
     * @param $class string
     * @param $constructorArgs array
     */
    private function createObject($di, $name, $class, $constructorArgs) {
        $instance = $di->lazyNew($class, $constructorArgs);
        $di->set($name, $instance);
    }

    public function getParam($param, $default = false) {
        return JapoAppConfig::get()->getParam($param, $default);
    }


    public function define(Container $di) {
        $config = JapoAppConfig::get();
        $di->set('params', $config);
        $auth0Config = [
            'domain' => $config->auth0Domain,
            'client_id' => $config->auth0ClientId,
            'client_secret' => $config->auth0ClientSecret,
            'redirect_uri' => $config->auth0RedirectUri,
            'audience' => 'https://' . $config->auth0Domain . '/userinfo',
            'scope' => 'openid profile',
            'persist_id_token' => true,
            'persist_access_token' => true,
            'persist_refresh_token' => true
        ];
        $di->set('auth0Config', $di->lazy(function () use($auth0Config, $config) {
           if ($config->cliMode) {
               $auth0Config['store'] = false;
               $auth0Config['state_handler'] = false;
           }
           return $auth0Config;
        }));
        $this->createObject($di, 'auth0', Auth0::class, ['config' => $di->lazyGet('auth0Config')]);
        $di->set('defaultLogger', $di->lazy(function () use($config) {
            $log = new Logger('japo');
            $handler = new StreamHandler("{$config->logPath}/japo.log", constant('Monolog\Logger::' . $config->logLevel));
            $handler->pushProcessor(new UidProcessor(24));
            $log->pushHandler($handler);
            return $log;
        }));
        $this->createObject($di, 'router', Router::class, [
            'backendPath' => $config->serverPath,
            'frontendPath' => $config->homePath
        ]);

        $this->createObject($di, 'authManager', Auth0AuthManager::class, [
            'auth0' => $di->lazyGet('auth0'),
            'router' => $di->lazyGet('router'),
            'logger' => $di->lazyGet('defaultLogger'),
            'auth0Domain' => $config->auth0Domain,
            'auth0ClientId' => $config->auth0ClientId,
            'auth0LogoutUri' => $config->auth0LogoutUri
        ]);


    }
    

}