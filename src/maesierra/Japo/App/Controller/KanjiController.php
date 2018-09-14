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
        $response->getBody()->write(json_encode(array_map(function($c) {
                            /** @var \maesierra\Japo\Entity\KanjiCatalog $c */
                            return [
                                "id" => $c->getId(),
                                "name" => $c->getName(),
                                "slug" => $c->getSlug()
                            ];
                  }, $this->kanjiRepository->listCatalogs())));
        return $response;

    }
}