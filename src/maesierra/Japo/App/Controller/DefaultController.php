<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 12/09/18
 * Time: 22:45
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\DB\KanjiRepository;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DefaultController extends BaseController {



    /**
     * @param Logger $logger
     * @param KanjiRepository $kanjiRepository
     */
    public function __construct($config, $logger) {
        parent::__construct($config, $logger);
    }

    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function defaultAction($request, $response, $args) {
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode($request->getAttribute("user")));
        return $response;
    }
}