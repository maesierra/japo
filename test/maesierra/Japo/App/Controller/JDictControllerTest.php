<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 25/09/2018
 * Time: 20:37
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\DB\JDictRepository;
use maesierra\Japo\JDict\JDictEntry;
use maesierra\Japo\JDict\JDictEntryKanji;
use maesierra\Japo\JDict\JDictQuery;
use maesierra\Japo\JDict\JDictQueryResults;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;


if (file_exists('../../../../../vendor/autoload.php')) include '../../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');

class JDictControllerTest extends \PHPUnit_Framework_TestCase {

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $request;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $jdictRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $body;

    /**
     * @var JDictController
     */
    private $controller;

    public function setUp() {
        /** @var JDictRepository $jdictRepository */
        $jdictRepository = $this->createMock(JDictRepository::class);
        $this->jdictRepository = $jdictRepository;
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $this->logger = $logger;
        $this->controller = new JDictController($this->jdictRepository, null, $logger);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->body = $this->createMock(StreamInterface::class);
        $this->response->method('getBody')->willReturn($this->body);
    }

    public function testJDictQuery() {
        $results = new JDictQueryResults();
        $results->entries = [
            $this->jdictEntry(7328, 'kanji', 'alt_kanji'),
            $this->jdictEntry(7329, 'kanj2'),
            $this->jdictEntry(7330, 'kanj3'),
        ];
        $results->total = 20;
        $expectedQuery = new JDictQuery();
        $expectedQuery->reading = 'reading';
        $expectedQuery->exact = false;
        $this->jdictRepository->expects($this->once())->method('query')->willReturn($results)->with($expectedQuery);
        $this->request->method('getQueryParams')->willReturn([
           'reading' => 'reading',
           'exact' => 'false'
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
     * @param $id
     * @param array ...$kanjiStr
     * @return JDictEntry
     */
    private function jdictEntry($id, ...$kanjiStr)
    {
        $entry = new JDictEntry();
        $entry->id = $id;
        $entry->kanji = [];
        foreach ($kanjiStr as $pos => $k) {
            $entry->kanji[] = new JDictEntryKanji($k, $pos == 0);
        }
        $entry->gloss = ['sun', 'day'];
        $entry->readings = ['kun reading','on reading1','on reading2'];
        $entry->meta = ['vt1', 'news1'];
        return $entry;
    }
}
