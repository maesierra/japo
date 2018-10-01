<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 18/09/2018
 * Time: 22:30
 */

namespace maesierra\Japo\DB;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use maesierra\Japo\Common\Query\Page;
use maesierra\Japo\Common\Query\Sort;
use maesierra\Japo\Entity\Kanji as KanjiEntity;
use maesierra\Japo\Entity\KanjiCatalog as KanjiCatalogEntity;
use maesierra\Japo\Entity\KanjiCatalogEntry as KanjiCatalogEntryEntity;
use maesierra\Japo\Entity\KanjiMeaning as KanjiMeaningEntity;
use maesierra\Japo\Entity\KanjiReading as KanjiReadingEntity;
use maesierra\Japo\Entity\KanjiStroke as KanjiStrokeEntity;
use maesierra\Japo\Kanji\Kanji;
use maesierra\Japo\Kanji\KanjiCatalog;
use maesierra\Japo\Kanji\KanjiCatalogEntry;
use maesierra\Japo\Kanji\KanjiQuery;
use maesierra\Japo\Kanji\KanjiQueryResult;
use maesierra\Japo\Kanji\KanjiReading;
use maesierra\Japo\Kanji\KanjiReadingHelpWord;
use maesierra\Japo\Kanji\KanjiStroke;
use maesierra\Japo\Test\Utils\TestQuery;
use Monolog\Logger;

if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');
if (!class_exists(TestQuery::class)) {
    file_exists('../Test/Utils/TestQuery.php') ? require_once '../Test/Utils/TestQuery.php' : require_once 'test/maesierra/Japo/Test/Utils/TestQuery.php';
}



class KanjiRepositoryTest extends \PHPUnit_Framework_TestCase {

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var  KanjiRepository */
    private $kanjiRepository;

    public function setUp() {
        /** @var EntityManager $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $this->entityManager = $entityManager;
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $this->logger = $logger;
        $this->kanjiRepository = new KanjiRepository($entityManager, $logger);
    }

    public function testKanjiQuery_noResults() {
        $this->stubKanjiQuery();
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $results = $this->kanjiRepository->query(new KanjiQuery());
        $this->assertEquals([], $results->kanjis);
        $this->assertEquals(0, $results->total);
        $this->assertNull($results->page);
        $this->assertEquals(new KanjiQuery(), $results->query);
    }

    public function testKanjiQuery_singleResult() {
        $kanji = $this->mockKanjiEntity(7328, 'kanji', 5, 550);
        $this->stubKanjiQuery([$kanji]);
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $results = $this->kanjiRepository->query(new KanjiQuery());
        $this->assertEquals([
            $this->expectedKanjiQueryResult(7328, 'kanji', 5, 550)
        ], $results->kanjis);
        $this->assertEquals(1, $results->total);
        $this->assertNull($results->page);
    }

    public function testKanjiQuery_singleResult_withCatalogId() {
        $kanji = $this->mockKanjiEntity(7328, 'kanji', 5, 550);
        $catalog = $this->selectCatalogByLevel($kanji, 5);
        $catalogId = $catalog->getId();
        $query = $this->stubKanjiQuery([$kanji]);
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalogId = $catalogId;
        $expectedConditions = "JOIN k.catalogs cat ".
            "WHERE ".
            "(cat.idCatalog=:catalogId)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalogId', $catalogId]);
        $this->stubGetCatalogsLevelsQuery($catalogId, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals([
            $this->expectedKanjiQueryResult(7328, 'kanji', 5, 550)
        ], $results->kanjis);
        $this->assertEquals(1, $results->total);
        $this->assertEquals($this->stubCatalog($catalogId, $catalog->getName(), $catalog->getSlug(), [1, 3, 5]), $results->catalog);
        $this->assertNull($results->page);
    }

    public function testKanjiQuery_singleResult_withCatalogSlug() {
        $kanji = $this->mockKanjiEntity(7328, 'kanji', 5, 550);
        $catalog = $this->selectCatalogByLevel($kanji, 5);
        $catalogId = $catalog->getId();
        $query = $this->stubKanjiQuery([$kanji]);
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalog = $catalog->getSlug();
        $expectedConditions = "JOIN k.catalogs cat ".
            "JOIN cat.catalog c ".
            "WHERE ".
            "(c.slug=:catalog)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalog', $catalog->getSlug()]);
        $this->stubGetCatalogsLevelsQuery($catalogId, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals([
            $this->expectedKanjiQueryResult(7328, 'kanji', 5, 550)
        ], $results->kanjis);
        $this->assertEquals(1, $results->total);
        $this->assertEquals($this->stubCatalog($catalogId, $catalog->getName(), $catalog->getSlug(), [1, 3, 5]), $results->catalog);
        $this->assertNull($results->page);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_paginated_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->page = 2;
        $kanjiQuery->pageSize = 10;
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $query->expects($this->once())->method('setMaxResults')->with(10);
        $query->expects($this->once())->method('setFirstResult')->with(20);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals([], $results->kanjis);
        $this->assertEquals(0, $results->total);
        $this->assertEquals(new Page(2, 10, 0), $results->page);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_paginated_multipleResults() {
        $kanji1 = $this->mockKanjiEntity(7328, 'kanji', 5, 550);
        $kanji2 = $this->mockKanjiEntity(7329, 'kanj2', 6, 551);
        $kanji3 = $this->mockKanjiEntity(7330, 'kanj3', 6, 552);
        $query = $this->stubKanjiQuery([$kanji1, $kanji2, $kanji3], 30);
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->page = 2;
        $kanjiQuery->pageSize = 3;
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $query->expects($this->once())->method('setMaxResults')->with(3);
        $query->expects($this->once())->method('setFirstResult')->with(6);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals([
            $this->expectedKanjiQueryResult(7328, 'kanji', 5, 550),
            $this->expectedKanjiQueryResult(7329, 'kanj2', 6, 551),
            $this->expectedKanjiQueryResult(7330, 'kanj3', 6, 552)
        ], $results->kanjis);
        $this->assertEquals(30, $results->total);
        $this->assertEquals(new Page(2, 3, 30), $results->page);
        $this->assertEquals($kanjiQuery, $results->query);
    }



    public function testKanjiQuery_multipleConditions_sort_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalogId = 0;
        $kanjiQuery->level = 2;
        $kanjiQuery->reading = 'わたし';
        $kanjiQuery->sort = new Sort("kanji", Sort::SORT_DESC);
        $expectedConditions = "JOIN k.catalogs cat ".
                       "JOIN k.readings reading ".
                       "WHERE ".
                            "(cat.idCatalog=:catalogId) AND ".
                            "(cat.level in (:levels)) AND ".
                            "((reading.reading=:hiragana and reading.kind='K') or (reading.reading=:katakana and reading.kind='O')) ".
                       "ORDER BY k.kanji desc";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalogId', 0], ['levels', [2]], ['hiragana', 'わたし'], ['katakana', 'ワタシ']);
        $this->stubCatalogEntityById(0, 'catalog 0', 'catalog0');
        $this->stubGetCatalogsLevelsQuery(0, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
        $this->assertEquals($this->stubCatalog(0, 'catalog 0', 'catalog0', [1, 3, 5]), $results->catalog);
    }

    public function testKanjiQuery_catalogId_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalogId = 1;
        $expectedConditions = "JOIN k.catalogs cat ".
            "WHERE ".
            "(cat.idCatalog=:catalogId)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalogId', 1]);
        $this->stubCatalogEntityById(1, 'catalog 0', 'catalog0');
        $this->stubGetCatalogsLevelsQuery(1, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
        $this->assertEquals($this->stubCatalog(1, 'catalog 0', 'catalog0', [1, 3, 5]), $results->catalog);
    }

    public function testKanjiQuery_catalogSlug_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalog = 'jlpt';
        $expectedConditions = "JOIN k.catalogs cat ".
            "JOIN cat.catalog c ".
            "WHERE ".
            "(c.slug=:catalog)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalog', 'jlpt']);
        $this->stubCatalogEntityBySlug(0, 'catalog 0', 'jlpt');
        $this->stubGetCatalogsLevelsQuery(0, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
        $this->assertEquals($this->stubCatalog(0, 'catalog 0', 'jlpt', [1, 3, 5]), $results->catalog);
    }

    public function testKanjiQuery_catalogSlugPrecedesCatalogId_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalog = 'jlpt';
        $kanjiQuery->catalogId = 4;
        $expectedConditions = "JOIN k.catalogs cat ".
            "JOIN cat.catalog c ".
            "WHERE ".
            "(c.slug=:catalog)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalog', 'jlpt']);
        $this->stubCatalogEntityBySlug(0, 'catalog 0', 'jlpt');
        $this->stubGetCatalogsLevelsQuery(0, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
        $this->assertEquals($this->stubCatalog(0, 'catalog 0', 'jlpt', [1, 3, 5]), $results->catalog);    }

    public function testKanjiQuery_catalogSlugAndLevelSingleValue_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalog = 'jlpt';
        $kanjiQuery->level = 411;
        $expectedConditions = "JOIN k.catalogs cat ".
            "JOIN cat.catalog c ".
            "WHERE ".
            "(c.slug=:catalog) AND ".
            "(cat.level in (:levels))";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalog', 'jlpt'], ['levels', [411]]);
        $this->stubCatalogEntityBySlug(0, 'catalog 0', 'jlpt');
        $this->stubGetCatalogsLevelsQuery(0, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
        $this->assertEquals($this->stubCatalog(0, 'catalog 0', 'jlpt', [1, 3, 5]), $results->catalog);
    }

    public function testKanjiQuery_catalogIdAndLevelArray_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->catalogId = 1;
        $kanjiQuery->level = [411, '407', ''];
        $expectedConditions = "JOIN k.catalogs cat " .
            "WHERE " .
            "(cat.idCatalog=:catalogId) AND " .
            "(cat.level in (:levels))";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalogId', 1], ['levels', [411, 407]]);
        $this->stubCatalogEntityById(0, 'catalog 0', 'catalog0');
        $this->stubGetCatalogsLevelsQuery(0, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
        $this->assertEquals($this->stubCatalog(0, 'catalog 0', 'catalog0', [1, 3, 5]), $results->catalog);
    }

    public function testKanjiQuery_levelNoCatalog_noResults()
    {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->level = [411, 407];
        $expectedConditions = "";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_reading_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->reading = 'わたし';
        $expectedConditions = "JOIN k.readings reading ".
            "WHERE ".
            "((reading.reading=:hiragana and reading.kind='K') or (reading.reading=:katakana and reading.kind='O'))";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['hiragana', 'わたし'], ['katakana', 'ワタシ']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_readingKunOnly_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->reading = 'わたし';
        $kanjiQuery->kunOnly = true;
        $expectedConditions = "JOIN k.readings reading ".
            "WHERE ".
            "(reading.reading=:hiragana and reading.kind='K')";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['hiragana', 'わたし']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_readingoOnOnly_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->reading = 'わたし';
        $kanjiQuery->onOnly = true;
        $expectedConditions = "JOIN k.readings reading ".
            "WHERE ".
            "(reading.reading=:katakana and reading.kind='O')";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['katakana', 'ワタシ']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_readingKunOnlyPrecedesOnOnly_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->reading = 'わたし';
        $kanjiQuery->kunOnly = true;
        $kanjiQuery->onOnly = true;
        $expectedConditions = "JOIN k.readings reading ".
            "WHERE ".
            "(reading.reading=:hiragana and reading.kind='K')";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['hiragana', 'わたし']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_kunOnlyNoReading_noResults()
    {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->kunOnly = true;
        $expectedConditions = "";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_OnOnlyNoReading_noResults()
    {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->onOnly = true;
        $expectedConditions = "";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_meaning_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->meaning = 'Sol';
        $expectedConditions = "JOIN k.meanings gloss ".
            "WHERE ".
            "(gloss.meaning like :meaning)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['meaning', '%Sol%']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_sortById_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("id");
        $expectedConditions = "ORDER BY k.id";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_sortByIdDesc_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("id", Sort::SORT_DESC);
        $expectedConditions = "ORDER BY k.id desc";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }


    public function testKanjiQuery_sortByKanji_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("kanji");
        $expectedConditions = "ORDER BY k.kanji";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_sortByKanjiDesc_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("kanji", Sort::SORT_DESC);
        $expectedConditions = "ORDER BY k.kanji desc";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_sortByLevel_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("level");
        $expectedConditions = "JOIN k.catalogs cat " .
                              "ORDER BY cat.idCatalog,cat.level,cat.n";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_catalogIdAndSortByLevel_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("level");
        $kanjiQuery->catalogId = 0;
        $expectedConditions = "JOIN k.catalogs cat " .
            "WHERE ".
            "(cat.idCatalog=:catalogId) ".
            "ORDER BY cat.idCatalog,cat.level,cat.n";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['catalogId', 0]);
        $this->stubCatalogEntityById(0, 'catalog 0', 'catalog0');
        $this->stubGetCatalogsLevelsQuery(0, [1, 3, 5]);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
        $this->assertEquals($this->stubCatalog(0, 'catalog 0', 'catalog0', [1, 3, 5]), $results->catalog);
    }



    public function testKanjiQuery_sortByLevelDesc_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("level", Sort::SORT_DESC);
        $expectedConditions = "JOIN k.catalogs cat " .
                              "ORDER BY cat.idCatalog desc,cat.level desc,cat.n desc";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }



    public function testKanjiQuery_sortByOn_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("on");
        $expectedConditions = "JOIN k.readings reading ".
            "ORDER BY reading.kind desc,reading.reading";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_readingAndSortByOn_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("on");
        $kanjiQuery->reading = 'わたし';
        $expectedConditions = "JOIN k.readings reading ".
            "WHERE ".
            "((reading.reading=:hiragana and reading.kind='K') or (reading.reading=:katakana and reading.kind='O')) ".
            "ORDER BY reading.kind desc,reading.reading";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['hiragana', 'わたし'], ['katakana', 'ワタシ']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }


    public function testKanjiQuery_sortByOnDesc_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("on", Sort::SORT_DESC);
        $expectedConditions = "JOIN k.readings reading ".
            "ORDER BY reading.kind desc,reading.reading desc";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_sortByKun_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("kun");
        $expectedConditions = "JOIN k.readings reading ".
            "ORDER BY reading.kind asc,reading.reading";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_readingAndSortByKun_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("kun");
        $kanjiQuery->reading = 'わたし';
        $expectedConditions = "JOIN k.readings reading ".
            "WHERE ".
            "((reading.reading=:hiragana and reading.kind='K') or (reading.reading=:katakana and reading.kind='O')) ".
            "ORDER BY reading.kind asc,reading.reading";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['hiragana', 'わたし'], ['katakana', 'ワタシ']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }


    public function testKanjiQuery_sortByKunDesc_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("kun", Sort::SORT_DESC);
        $expectedConditions = "JOIN k.readings reading ".
            "ORDER BY reading.kind asc,reading.reading desc";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_sortByMeaning_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("meaning");
        $expectedConditions = "JOIN k.meanings gloss ".
            "ORDER BY gloss.meaning";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testKanjiQuery_meaningAndSortByMeaning_noResults() {
        $query = $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("meaning");
        $kanjiQuery->meaning = 'Sol';
        $expectedConditions = "JOIN k.meanings gloss ".
            "WHERE ".
            "(gloss.meaning like :meaning) ".
            "ORDER BY gloss.meaning";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['meaning', '%Sol%']);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }


    public function testKanjiQuery_sortByMeaningDesc_noResults() {
        $this->stubKanjiQuery();
        $kanjiQuery = new KanjiQuery();
        $kanjiQuery->sort = new Sort("meaning", Sort::SORT_DESC);
        $expectedConditions = "JOIN k.meanings gloss ".
            "ORDER BY gloss.meaning desc";
        $this->verifyDDLExecuted($expectedConditions);
        $results = $this->kanjiRepository->query($kanjiQuery);
        $this->assertEquals($kanjiQuery, $results->query);
    }

    public function testGetCatalogLevels() {
        $idCatalog = 2663;
        $this->stubGetCatalogsLevelsQuery($idCatalog, [1, 3, 56]);
        $this->assertEquals([1, 3, 56], $this->kanjiRepository->getCatalogLevels($idCatalog));
    }

    public function testListCatalogs() {
        $this->stubListCatalogs([1, 'catalog 1', 'catalog1'], [2, 'catalog 2', 'catalog2']);
        $this->assertEquals([
            $this->stubCatalog(1, 'catalog 1', 'catalog1'),
            $this->stubCatalog(2, 'catalog 2', 'catalog2')
        ], $this->kanjiRepository->listCatalogs());
    }



    public function testKanji() {
        $this->stubFindKanjiByKanji('kanji', $this->mockKanjiEntity(7328, 'kanji', 5, 550));
        $kanji = $this->kanjiRepository->findKanji('kanji');

        $this->assertEquals(
            $this->expectedKanji(7328, 'kanji', 5, 550),
            $kanji
        );
    }

    public function testKanji_notFound() {
        $this->stubFindKanjiByKanji('kanji', null);
        $kanji = $this->kanjiRepository->findKanji('kanji');
        $this->assertNull($kanji);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stubKanjiQuery($results = [], $total = null)
    {
        $total = $total ?: count($results);
        $query = $this->createMock(TestQuery::class);
        $query->method('getResult')->willReturn($results);

        $countQuery = $this->createMock(TestQuery::class);
        $countQuery->method('getSingleScalarResult')->willReturn($total);
        $countQuery->method('getResult')->willReturn($results);

        $this->entityManager->method('createQuery')->willReturnOnConsecutiveCalls($query, $countQuery);
        return $query;
    }

    /**
     * @param $expectedConditions
     */
    private function verifyDDLExecuted($expectedConditions)
    {
        $this->entityManager->expects($this->exactly(2))->method('createQuery')->withConsecutive(
            [trim("select k from \\maesierra\\Japo\\Entity\\Kanji k $expectedConditions")],
            [trim("select count(k.id) from \\maesierra\\Japo\\Entity\\Kanji k $expectedConditions")]
        );
    }

    /**
     * @param $id
     * @param $kanjiStr
     * @param $level1
     * @param $level2
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockKanjiEntity($id, $kanjiStr, $level1, $level2)
    {
        $kanji = $this->createMock(KanjiEntity::class);
        $kanji->method('getId')->willReturn($id);
        $kanji->method('getKanji')->willReturn($kanjiStr);
        $kanji->method('getCatalogs')->willReturn(new ArrayCollection([
            $this->catalogEntryEntity($level1, 1, 'catalog1', 33, 'catalog_1'),
            $this->catalogEntryEntity($level2, 10, 'catalog2', 4, 'catalog_2')
        ]));
        $kanji->method('getReadings')->willReturn(new ArrayCollection([
            $this->kanjiReadingEntity('K', 'kun reading', 356),
            $this->kanjiReadingEntity('O', 'on reading1', null),
            $this->kanjiReadingEntity('O', 'on reading2', 35)
        ]));
        $kanji->method('getMeanings')->willReturn(new ArrayCollection([
            $this->kanjiMeaningEntity('sun'),
            $this->kanjiMeaningEntity('day')
        ]));
        $kanji->method('getStrokes')->willReturn(new ArrayCollection([
            $this->kanjiStrokeEntity(1, 'M54.5,20c0.37,2.12,0.23,4.03-0.22,6.27C51.68,39.48,38.25,72.25,16.5,87.25', '18'),
            $this->kanjiStrokeEntity(2, 'M46,54.25c6.12,6,25.51,22.24,35.52,29.72c3.66,2.73,6.94,4.64,11.48,5.53', '15'),
        ]));
        return $kanji;
    }

    /**
     * @param $id
     * @param $kanjiStr
     * @param $level1
     * @param $level2
     * @return KanjiQueryResult
     */
    private function expectedKanjiQueryResult($id, $kanjiStr, $level1, $level2)
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
     * @param $id
     * @param $kanjiStr
     * @param $level1
     * @param $level2
     * @return Kanji
     */
    private function expectedKanji($id, $kanjiStr, $level1, $level2)
    {
        $kanji = new Kanji();
        $kanji->id = $id;
        $kanji->kanji = $kanjiStr;
        $kanji->catalogs = [
            33 => $this->kanjiCatalogEntry($level1, 1, 'catalog1', 33, 'catalog_1'),
            4 => $this->kanjiCatalogEntry($level2,10,  'catalog2', 4, 'catalog_2')
        ];
        $kanji->on = [
            $this->kanjiReading('O', 'on reading1', null),
            $this->kanjiReading('O', 'on reading2', 35)
        ];
        $kanji->kun = [
            $this->kanjiReading('K', 'kun reading', 356),
        ];
        $kanji->meanings = ['sun', 'day'];
        $kanji->strokes = [
            $this->kanjiStroke(1, 'M54.5,20c0.37,2.12,0.23,4.03-0.22,6.27C51.68,39.48,38.25,72.25,16.5,87.25', '18'),
            $this->kanjiStroke(2, 'M46,54.25c6.12,6,25.51,22.24,35.52,29.72c3.66,2.73,6.94,4.64,11.48,5.53', '15')
        ];
        return $kanji;
    }

    /**
     * @param $level
     * @param $n
     * @param $catalogName
     * @param $catalogId
     * @param $slug
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function catalogEntryEntity($level, $n, $catalogName, $catalogId, $slug)
    {
        $catalogEntry = $this->createMock(KanjiCatalogEntryEntity::class);
        $catalogEntry->method('getLevel')->willReturn($level);
        $catalogEntry->method('getN')->willReturn($n);
        $catalog = $this->stubCatalogEntity($catalogId, $catalogName, $slug);
        $catalogEntry->method('getCatalog')->willReturn($catalog);
        return $catalogEntry;
    }

    /**
     * @param $kind
     * @param $r
     * @param $helpWordId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function kanjiReadingEntity($kind, $r, $helpWordId)
    {
        $reading = $this->createMock(KanjiReadingEntity::class);
        $reading->method('getKind')->willReturn($kind);
        $reading->method('getHelpWordId')->willReturn($helpWordId);
        $reading->method('getReading')->willReturn($r);
        return $reading;
    }

    /**
     * @param $meaning
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function kanjiMeaningEntity($meaning)
    {
        $kanjiMeaning = $this->createMock(KanjiMeaningEntity::class);
        $kanjiMeaning->method('getMeaning')->willReturn($meaning);
        return $kanjiMeaning;
    }

    /**
     * @param $path
     * @param $position
     * @param $type
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function kanjiStrokeEntity($position, $path, $type)
    {
        $stroke = $this->createMock(KanjiStrokeEntity::class);
        $stroke->method('getPosition')->willReturn($position);
        $stroke->method('getPath')->willReturn($path);
        $stroke->method('getType')->willReturn($type);
        return $stroke;
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
     * @param $path
     * @param $position
     * @param $type
     * @return KanjiStroke
     */
    private function kanjiStroke($position, $path, $type)
    {
        $stroke = new KanjiStroke();
        $stroke->position = $position;
        $stroke->path = $path;
        $stroke->type = $type;
        return $stroke;
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
        $helpWord = new KanjiReadingHelpWord();
        $helpWord->id = $helpWordId;
        $reading->helpWord = $helpWord;
        $reading->reading = $r;
        return $reading;
    }

    /**
     * @param $query \PHPUnit_Framework_MockObject_MockObject
     */
    private function verifyParameters($query, ...$params)
    {
        $query->expects($this->exactly(count($params)))
            ->method('setParameter')
            ->withConsecutive(...$params);
    }

    /**
     * @param $idCatalog
     * @param $levels
     */
    private function stubGetCatalogsLevelsQuery($idCatalog, $levels)
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->with('c.level')->willReturnSelf();
        $qb->method('from')->with('\maesierra\Japo\Entity\KanjiCatalogEntry', 'c')->willReturnSelf();
        $qb->method('where')->with('c.idCatalog = ?1')->willReturnSelf();
        $qb->method('distinct')->willReturnSelf();
        $qb->method('orderBy')->with('c.level')->willReturnSelf();
        $query = $this->createMock(TestQuery::class);
        $query->expects($this->once())->method('setParameter')->with(1, $idCatalog)->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);
        $query->method('getScalarResult')->willReturn(array_map(function ($l) {return [$l];}, $levels));
        $this->entityManager->method('createQueryBuilder')->willReturn($qb);
    }

    /**
     * @param $idCatalog
     * @param $levels
     */
    private function stubListCatalogs(...$catalog)
    {
        $repository = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->with(KanjiCatalogEntity::class)->willReturn($repository);
        $repository->method('findAll')->willReturn(array_map(function($c) {
            return $this->stubCatalogEntity($c[0], $c[1], $c[2]);
        }, $catalog));
    }

    /**
     * @param $catalogName
     * @param $catalogId
     * @param $slug
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stubCatalogEntity($catalogId, $catalogName, $slug)
    {
        $catalog = $this->createMock(KanjiCatalogEntity::class);
        $catalog->method('getName')->willReturn($catalogName);
        $catalog->method('getId')->willReturn($catalogId);
        $catalog->method('getSlug')->willReturn($slug);
        return $catalog;
    }

    /**
     * @param $catalogName
     * @param $catalogId
     * @param $slug
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stubCatalogEntityById($catalogId, $catalogName, $slug) {
        $repository = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->with(KanjiCatalogEntity::class)->willReturn($repository);
        $catalog = $this->stubCatalogEntity($catalogId, $catalogName, $slug);
        $repository->method('find')->willReturn($catalog);
        return $catalog;
    }

    /**
     * @param $catalogName
     * @param $catalogId
     * @param $slug
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stubCatalogEntityBySlug($catalogId, $catalogName, $slug) {
        $repository = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->with(KanjiCatalogEntity::class)->willReturn($repository);
        $catalog = $this->stubCatalogEntity($catalogId, $catalogName, $slug);
        $repository->method('findOneBy')->with(['slug' => $slug])->willReturn($catalog);
        return $catalog;
    }

    /**
     * @param $catalogName
     * @param $catalogId
     * @param $slug
     * @return KanjiCatalog
     */
    private function stubCatalog($catalogId, $catalogName, $slug, $levels = null)
    {
        $catalog = new KanjiCatalog();
        $catalog->id = $catalogId;
        $catalog->name = $catalogName;
        $catalog->slug = $slug;
        $catalog->levels = $levels;
        return $catalog;
    }

    /**
     * @param $kanji
     * @return KanjiCatalogEntity
     */
    private function selectCatalogByLevel($kanji, $level)
    {
        /** @var $kanji KanjiEntity */
        return array_filter($kanji->getCatalogs()->toArray(), function ($c) use($level) {
            /** @var KanjiCatalogEntryEntity $c */
            return $c->getLevel() == $level;
        })[0]->getCatalog();
    }

    /**
     * @param $kanji string
     * @param $kanjiEntity
     */
    private function stubFindKanjiByKanji($kanji, $kanjiEntity) {
        $repository = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->with(KanjiEntity::class)->willReturn($repository);
        $repository->method('findOneBy')->with(['kanji' => $kanji])->willReturn($kanjiEntity);
    }

}