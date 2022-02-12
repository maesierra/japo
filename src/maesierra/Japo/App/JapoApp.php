<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 12/09/18
 * Time: 22:08
 */

namespace maesierra\Japo\App;


use maesierra\Japo\App\Controller\AuthController;
use maesierra\Japo\App\Controller\DefaultController;
use maesierra\Japo\App\Controller\JDictController;
use maesierra\Japo\App\Controller\KanjiController;
use maesierra\Japo\AppContext\JapoAppContext;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

class JapoApp extends App {


    public function __construct(JapoAppContext $container) {
        parent::__construct(AppFactory::determineResponseFactory(), $container);
        $this->setBasePath('/api');
        $this->group('/auth', function (RouteCollectorProxy $app) {
            $app->get('/login',  AuthController::class.':login');
            $app->get('/auth',  AuthController::class.':auth');
            $app->get('/logout',  AuthController::class.':logout');
        });

        $authMiddleware = function (Request $request, RequestHandler $handler) use($container) {
            $logger = $container->defaultLogger;
            $logInfo = $request->getUri()." User Auth from host: {$_SERVER['REMOTE_ADDR']} user agent: {$_SERVER['HTTP_USER_AGENT']} ";
            if ($container->authManager->isAuthenticated()) {
                $logger->info($logInfo." => Authorized");
                return $handler->handle($request->withAttribute("user", $container->authManager->getUser()));
            } else {
                $logger->info($logInfo." => Unauthorized");
                $response = new Response();
                return $response->withStatus(401, 'Unauthorised');
            }
        };

        $this->get('/', DefaultController::class.':defaultAction')->add($authMiddleware);

        $this->group('/kanji', function (RouteCollectorProxy $app) {
            $app->get('/catalogs',  KanjiController::class.':catalogs');
            $app->get('/query',  KanjiController::class.':query');
            $app->get('/{kanji}',  KanjiController::class.':kanji');
            $app->post('/{kanji}',  KanjiController::class.':saveKanji');
        })->add($authMiddleware);

        $this->group('/jdict', function (RouteCollectorProxy $app) {
            $app->get('/query',  JDictController::class.':query');
        })->add($authMiddleware);
    }
}