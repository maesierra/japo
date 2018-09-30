<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 12/09/18
 * Time: 22:45
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\AppContext\JapoAppConfig;
use maesierra\Japo\DB\JDictRepository;
use maesierra\Japo\JDict\JDictQuery;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JDictController extends BaseController {


    /** @var JDictRepository */
    public $jdictRepository;


    /**
     * @param Logger $logger
     * @param JDictRepository $jdictRepository
     * @param JapoAppConfig $config
     */
    public function __construct($jdictRepository, $config, $logger) {
        parent::__construct($config, $logger);
        $this->jdictRepository = $jdictRepository;
    }


    /**
     * @param $request ServerRequestInterface
     * @param $response ResponseInterface
     * @param $args array
     * @return ResponseInterface
     */
    public function query($request, $response, $args) {
        $params = $request->getQueryParams();
        $response = $response->withHeader('Content-type', 'application/json');
        $this->logger->debug("JDict Query: ".json_encode($params));
        $response->getBody()->write(json_encode($this->jdictRepository->query(new JDictQuery($params))));
        return $response;

    }

}