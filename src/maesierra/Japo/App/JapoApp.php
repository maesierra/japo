<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 12/09/18
 * Time: 22:08
 */

namespace maesierra\Japo\App;


use maesierra\Japo\App\Controller\AuthController;
use maesierra\Japo\App\Controller\JDictController;
use maesierra\Japo\App\Controller\KanjiController;
use maesierra\Japo\AppContext\JapoAppContext;
use Slim\App;

class JapoApp extends App {


    /**
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */

    public function authMiddleware($request, $response, $next)
    {
        $appContext = JapoAppContext::context();
        $logger = $appContext->defaultLogger;
        $logInfo = "User Auth from host: {$_SERVER['REMOTE_ADDR']} user agent: {$_SERVER['HTTP_USER_AGENT']}";
        if ($appContext->authManager->isAuthenticated()) {
            $logger->info($logInfo." => Authorized");
            return $next($request, $response);
        } else {
            $logger->info($logInfo." => Unauthorized");
            return $response->withStatus(401, 'Unauthorised');
        }
    }

    public function __construct()
    {
        parent::__construct(JapoAppContext::context());

        $this->group('/auth', function () {
            $this->get('/login',  AuthController::class.':login');
            $this->get('/auth',  AuthController::class.':auth');
            $this->get('/logout',  AuthController::class.':logout');
        });

        $this->get('/', function ($request, $response, $args) {
            return $response;
        })->add([$this, 'authMiddleware']);

        $this->group('/kanji', function () {
            $this->get('/catalogs',  KanjiController::class.':catalogs');
            $this->get('/query',  KanjiController::class.':query');
            $this->get('/{kanji}',  KanjiController::class.':kanji');
        })->add([$this, 'authMiddleware']);

        $this->group('/jdict', function () {
            $this->get('/query',  JDictController::class.':query');
        })->add([$this, 'authMiddleware']);

    }
}