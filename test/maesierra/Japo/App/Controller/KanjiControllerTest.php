<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 25/09/2018
 * Time: 20:37
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\Common\Query\Sort;
use maesierra\Japo\DB\KanjiRepository;
use maesierra\Japo\Kanji\KanjiCatalog;
use maesierra\Japo\Kanji\KanjiCatalogEntry;
use maesierra\Japo\Kanji\KanjiQuery;
use maesierra\Japo\Kanji\KanjiQueryResult;
use maesierra\Japo\Kanji\KanjiQueryResults;
use maesierra\Japo\Kanji\KanjiReading;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;


if (file_exists('../../../../../vendor/autoload.php')) include '../../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class KanjiControllerTest extends \PHPUnit_Framework_TestCase {

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $request;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $kanjiRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $body;

    /**
     * @var KanjiController
     */
    private $controller;

    public function setUp() {
        /** @var KanjiRepository $kanjiRepository */
        $kanjiRepository = $this->createMock(KanjiRepository::class);
        $this->kanjiRepository = $kanjiRepository;
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $this->logger = $logger;
        $this->controller = new KanjiController($this->kanjiRepository, null, $logger);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->body = $this->createMock(StreamInterface::class);
        $this->response->method('getBody')->willReturn($this->body);
    }

    public function testCatalogs() {
        $catalogs = [
            $this->kanjiCatalog(1, "catalog 1", "catalog1"),
            $this->kanjiCatalog(2, "catalog 2", "catalog2")
        ];
        $this->kanjiRepository->expects($this->once())->method('listCatalogs')->willReturn($catalogs);
        $this->response->expects($this->once())->method('withHeader')->with('Content-type', 'application/json')->willReturnSelf();
        $this->body->expects($this->once())->method('write')->with(json_encode([
            ['id'=>1, 'name'=>'catalog 1', 'slug' => 'catalog1', 'levels' => null],
            ['id'=>2, 'name'=>'catalog 2', 'slug' => 'catalog2', 'levels' => null]
        ]));

        /** @var ServerRequestInterface $request */
        $request = $this->request;
        /** @var ResponseInterface $response */
        $response = $this->response;
        $this->controller->catalogs($request, $response, []);
    }

    public function testKanjiQuery() {
        $results = new KanjiQueryResults();
        $results->kanjis = [
            $this->kanjiQueryResult(7328, 'kanji', 5, 550),
            $this->kanjiQueryResult(7329, 'kanj2', 6, 551),
            $this->kanjiQueryResult(7330, 'kanj3', 6, 552),
        ];
        $results->total = 20;
        $expectedQuery = new KanjiQuery();
        $expectedQuery->catalogId = 10;
        $expectedQuery->sort = new Sort("id", Sort::SORT_DESC);
        $this->kanjiRepository->expects($this->once())->method('query')->willReturn($results)->with($expectedQuery);
        $this->request->method('getQueryParams')->willReturn([
           'catalogId' => 10,
           'sort' => 'id',
           'order' => 'desc'
        ]);
        $this->response->expects($this->once())->method('withHeader')->with('Content-type', 'application/json')->willReturnSelf();
        $this->body->expects($this->once())->method('write')->with(json_encode($results));

        /** @var ServerRequestInterface $request */
        $request = $this->request;
        /** @var ResponseInterface $response */
        $response = $this->response;
        $this->controller->query($request, $response, []);
    }

    public function testKanjiQuery_defaultSort() {
        $results = new KanjiQueryResults();
        $results->kanjis = [
            $this->kanjiQueryResult(7328, 'kanji', 5, 550),
            $this->kanjiQueryResult(7329, 'kanj2', 6, 551),
            $this->kanjiQueryResult(7330, 'kanj3', 6, 552),
        ];
        $results->total = 20;
        $expectedQuery = new KanjiQuery();
        $expectedQuery->catalogId = 10;
        $expectedQuery->sort = new Sort("level", Sort::SORT_ASC);
        $this->kanjiRepository->expects($this->once())->method('query')->willReturn($results)->with($expectedQuery);
        $this->request->method('getQueryParams')->willReturn([
            'catalogId' => 10
        ]);
        $this->response->expects($this->once())->method('withHeader')->with('Content-type', 'application/json')->willReturnSelf();
        $this->body->expects($this->once())->method('write')->with(json_encode($results));

        /** @var ServerRequestInterface $request */
        $request = $this->request;
        /** @var ResponseInterface $response */
        $response = $this->response;
        $this->controller->query($request, $response, []);
    }

    /**
     * @return KanjiCatalog
     */
    private function kanjiCatalog($id, $name, $slug) {
        $catalog = new KanjiCatalog();
        $catalog->id = $id;
        $catalog->name = $name;
        $catalog->slug = $slug;
        return $catalog;
    }

    /**
     * @param $id
     * @param $kanjiStr
     * @param $level1
     * @param $level2
     * @return KanjiQueryResult
     */
    private function kanjiQueryResult($id, $kanjiStr, $level1, $level2)
    {
        $kanjiQueryResult = new KanjiQueryResult();
        $kanjiQueryResult->id = $id;
        $kanjiQueryResult->kanji = $kanjiStr;
        $kanjiQueryResult->catalogs = [
            33 => $this->kanjiCatalogEntry($level1, 1, 'catalog1', 33, 'catalog_1'),
            4 => $this->kanjiCatalogEntry($level2,10,  'catalog2', 4, 'catalog_2')
        ];
        $kanjiQueryResult->readings = [
            $this->kanjiReading('K', 'kun reading', 356),
            $this->kanjiReading('O', 'on reading1', null),
            $this->kanjiReading('O', 'on reading2', 35)
        ];
        $kanjiQueryResult->meanings = ['sun', 'day'];
        return $kanjiQueryResult;
    }


    /**
     * @return KanjiCatalogEntry
     */
    private function kanjiCatalogEntry($level, $n, $catalogName, $catalogId, $slug)
    {
        $entry = new KanjiCatalogEntry();
        $entry->catalogId = $catalogId;
        $entry->catalogName = $catalogName;
        $entry->catalogSlug = $slug;
        $entry->level = $level;
        $entry->n = $n;
        return $entry;
    }

    /**
     * @param $type
     * @param $r
     * @param $helpWordId
     * @return KanjiReading
     */
    private function kanjiReading($type, $r, $helpWordId)
    {
        $reading = new KanjiReading();
        $reading->type = $type;
        $reading->helpWord = $helpWordId;
        $reading->reading = $r;
        return $reading;
    }

}
