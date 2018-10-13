<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 12/09/18
 * Time: 22:45
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\AppContext\JapoAppConfig;
use maesierra\Japo\Auth\User;
use maesierra\Japo\DB\KanjiRepository;
use maesierra\Japo\Kanji\Kanji;
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
        $this->logger->debug("Kanji Query: ".json_encode($params));
        $response->getBody()->write(json_encode($this->kanjiRepository->query(new KanjiQuery($params))));
        return $response;
    }

    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function kanji($request, $response, $args) {
        $response = $response->withHeader('Content-type', 'application/json');
        $kanji = $this->kanjiRepository->findKanji($args['kanji']);
        if (!$kanji) {
            $response = $response->withStatus(404);
            $response->getBody()->write('Kanji not found');
        } else {
            $response->getBody()->write(json_encode($kanji));
        }
        return $response;
    }

    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function saveKanji($request, $response, $args) {
        $user = $this->getUserFromRequest($request);
        if (!$user->hasRole(User::USER_ROLE_EDITOR)) {
            $response = $response->withStatus(403);
            $response->getBody()->write('editor role required');
            return $response;
        }
        $kanji = $request->getParsedBody();
        $kanji = $kanji ? new Kanji($kanji) : null;
        if (!$kanji) {
            $response = $response->withStatus(400);
            $response->getBody()->write('Unable to parse kanji');
        } else {
            $this->logger->debug("Saving kanji {$kanji->kanji}: ".json_encode($kanji));
            $response = $response->withHeader('Content-type', 'application/json');
            $response->getBody()->write(json_encode($this->kanjiRepository->saveKanji($kanji)));
        }
        return $response;

    }

}