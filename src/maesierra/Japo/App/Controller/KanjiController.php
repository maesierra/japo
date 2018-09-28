<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 12/09/18
 * Time: 22:45
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\AppContext\JapoAppConfig;
use maesierra\Japo\Auth\AuthManager;
use maesierra\Japo\DB\KanjiRepository;
use maesierra\Japo\Kanji\KanjiQuery;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class KanjiController extends BaseController {


    /** @var KanjiRepository */
    public $kanjiRepository;


    /**
     * @param Logger $logger
     * @param KanjiRepository $kanjiRepository
     * @param JapoAppConfig $config
     */
    public function __construct($kanjiRepository, $config, $logger) {
        parent::__construct($config, $logger);
        $this->kanjiRepository = $kanjiRepository;
    }

    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function catalogs($request, $response, $args) {
        $response = $response->withHeader('Content-type', 'application/json');
        $response->getBody()->write(json_encode($this->kanjiRepository->listCatalogs()));
        return $response;
    }

    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function query($request, $response, $args) {
        $params = $request->getQueryParams();
        if (!isset($params['sort'])) {
            $params['sort'] = 'level';
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $this->logger->debug("Kanji Query: ".\GuzzleHttp\json_encode($params));
        $response->getBody()->write(json_encode($this->kanjiRepository->query(new KanjiQuery($params))));
        return $response;

    }

}