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
use maesierra\Japo\Common\Query\Page;
use maesierra\Japo\Entity\JDict\JDictEntry as JDictEntryEntity;
use maesierra\Japo\Entity\JDict\JDictEntryGloss as JDictEntryGlossEntity;
use maesierra\Japo\Entity\JDict\JDictEntryKanji as JDictEntryKanjiEntity;
use maesierra\Japo\Entity\JDict\JDictEntryMeta as JDictEntryMetaEntity;
use maesierra\Japo\Entity\JDict\JDictEntryReading as JDictEntryReadingEntity;
use maesierra\Japo\JDict\JDictEntry;
use maesierra\Japo\JDict\JDictEntryKanji;
use maesierra\Japo\JDict\JDictQuery;
use maesierra\Japo\Kanji\KanjiCatalogEntry;
use maesierra\Japo\Kanji\KanjiReading;
use maesierra\Test\Utils\TestQuery;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JDictRepositoryTest extends TestCase {

    /** @var  MockObject */
    private $entityManager;
    /** @var  MockObject */
    private $logger;

    /** @var  JDictRepository */
    private $jdictRepository;

    public function setUp():void {
        /** @var EntityManager $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $this->entityManager = $entityManager;
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $this->logger = $logger;
        $this->jdictRepository = new JDictRepository($entityManager, $logger);
    }

    public function testJDictQuery_noResults() {
        $this->stubQuery();
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $results = $this->jdictRepository->query(new JDictQuery());
        $this->assertEquals([], $results->entries);
        $this->assertEquals(0, $results->total);
        $this->assertNull($results->page);
        $this->assertEquals(new JDictQuery(), $results->query);
    }

    public function testJDictQuery_singleResult() {
        $kanji = $this->stubJDictEntry(7328, 'kanji', 'kanji2');
        $this->stubQuery([$kanji]);
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $results = $this->jdictRepository->query(new JDictQuery());
        $this->assertEquals([
            $this->expectedJDictEntry(7328, 'kanji', 'kanji2')
        ], $results->entries);
        $this->assertEquals(1, $results->total);
        $this->assertNull($results->page);
    }


    public function testJDictQuery_paginated_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->page = 2;
        $jdictQuery->pageSize = 10;
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $query->expects($this->once())->method('setMaxResults')->with(10);
        $query->expects($this->once())->method('setFirstResult')->with(20);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals([], $results->entries);
        $this->assertEquals(0, $results->total);
        $this->assertEquals(new Page(2, 10, 0), $results->page);
        $this->assertEquals($jdictQuery, $results->query);
    }

    public function testJDictQuery_paginated_multipleResults() {
        $kanji1 = $this->stubJDictEntry(7328, 'kanji');
        $kanji2 = $this->stubJDictEntry(7329, 'kanj2');
        $kanji3 = $this->stubJDictEntry(7330, 'kanj3');
        $query = $this->stubQuery([$kanji1, $kanji2, $kanji3], 30);
        $jdictQuery = new JDictQuery();
        $jdictQuery->page = 2;
        $jdictQuery->pageSize = 3;
        $expectedDDL = "";
        $this->verifyDDLExecuted($expectedDDL);
        $query->expects($this->once())->method('setMaxResults')->with(3);
        $query->expects($this->once())->method('setFirstResult')->with(6);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals([
            $this->expectedJDictEntry(7328, 'kanji'),
            $this->expectedJDictEntry(7329, 'kanj2'),
            $this->expectedJDictEntry(7330, 'kanj3')
        ], $results->entries);
        $this->assertEquals(30, $results->total);
        $this->assertEquals(new Page(2, 3, 30), $results->page);
        $this->assertEquals($jdictQuery, $results->query);
    }



    public function testJDictQuery_multipleConditions_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->gloss = 'sun';
        $jdictQuery->kanji = 'kanji';
        $jdictQuery->reading = 'わたし';
        $jdictQuery->exact = true;
        $expectedConditions = "JOIN entry.kanji kanji ".
                       "JOIN entry.readings reading ".
                       "JOIN entry.gloss gloss ".
                       "WHERE ".
                            "(kanji.kanji=:kanji) AND ".
                            "(reading.reading=:reading) AND ".
                            "(gloss.gloss=:gloss)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['kanji', 'kanji'], ['reading', 'わたし'], ['gloss', 'sun']);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals($jdictQuery, $results->query);
    }

    public function testJDictQuery_kanji_not_exact_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->kanji = 'kanji';
        $jdictQuery->exact = false;
        $expectedConditions = "JOIN entry.kanji kanji ".
            "WHERE ".
            "(kanji.kanji like :kanji)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['kanji', '%kanji%']);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals($jdictQuery, $results->query);
    }

    public function testJDictQuery_kanji_exact_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->kanji = 'kanji';
        $jdictQuery->exact = true;
        $expectedConditions = "JOIN entry.kanji kanji ".
            "WHERE ".
            "(kanji.kanji=:kanji)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['kanji', 'kanji']);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals($jdictQuery, $results->query);
    }

    public function testJDictQuery_reading_not_exact_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->reading = 'reading';
        $jdictQuery->exact = false;
        $expectedConditions = "JOIN entry.readings reading ".
            "WHERE ".
            "(reading.reading like :reading)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['reading', '%reading%']);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals($jdictQuery, $results->query);
    }

    public function testJDictQuery_reading_exact_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->reading = 'reading';
        $jdictQuery->exact = true;
        $expectedConditions = "JOIN entry.readings reading ".
            "WHERE ".
            "(reading.reading=:reading)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['reading', 'reading']);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals($jdictQuery, $results->query);
    }

    public function testJDictQuery_gloss_not_exact_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->gloss = 'gloss';
        $jdictQuery->exact = false;
        $expectedConditions = "JOIN entry.gloss gloss ".
            "WHERE ".
            "(gloss.gloss like :gloss)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['gloss', '%gloss%']);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals($jdictQuery, $results->query);
    }


    public function testJDictQuery_gloss_exact_noResults() {
        $query = $this->stubQuery();
        $jdictQuery = new JDictQuery();
        $jdictQuery->gloss = 'gloss';
        $jdictQuery->exact = true;
        $expectedConditions = "JOIN entry.gloss gloss ".
            "WHERE ".
            "(gloss.gloss=:gloss)";
        $this->verifyDDLExecuted($expectedConditions);
        $this->verifyParameters($query, ['gloss', 'gloss']);
        $results = $this->jdictRepository->query($jdictQuery);
        $this->assertEquals($jdictQuery, $results->query);
    }



    /**
     * @return MockObject
     */
    private function stubQuery($results = [], $total = null)
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
            [trim("select entry from \\maesierra\\Japo\\Entity\\JDict\\JDictEntry entry $expectedConditions")],
            [trim("select count(entry.id) from \\maesierra\\Japo\\Entity\\JDict\\JDictEntry entry $expectedConditions")]
        );
    }

    /**
     * @param $id
     * @param array ...$kanjiStr
     * @return MockObject
     */
    private function stubJDictEntry($id, ...$kanjiStr)
    {
        $entry = $this->createMock(JDictEntryEntity::class);
        $entry->method('getId')->willReturn($id);
        $entry->method('getGloss')->willReturn(new ArrayCollection([
            $this->glossEntity('sun'),
            $this->glossEntity('day')
        ]));
        $entry->method('getReadings')->willReturn(new ArrayCollection([
            $this->readingEntity('kun reading'),
            $this->readingEntity('on reading1'),
            $this->readingEntity('on reading2')
        ]));
        $entry->method('getMeta')->willReturn(new ArrayCollection([
            $this->metaEntity('vt1'),
            $this->metaEntity('news1')
        ]));
        $kanjis = [];
        foreach ($kanjiStr as $pos => $k) {
            $kanjis[] = $this->kanjiEntity($k, $pos == 0);
        }
        $entry->method('getKanji')->willReturn(new ArrayCollection($kanjis));
        return $entry;
    }

    /**
     * @param $id
     * @param array ...$kanjiStr
     * @return JDictEntry
     */
    private function expectedJDictEntry($id, ...$kanjiStr)
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

    /**
     * @param $gloss
     * @return MockObject
     */
    private function glossEntity($gloss)
    {
        $jdictGlossEntity = $this->createMock(JDictEntryGlossEntity::class);
        $jdictGlossEntity->method('getGloss')->willReturn($gloss);
        return $jdictGlossEntity;
    }

    /**
     * @param $r
     * @return MockObject
     */
    private function readingEntity($r)
    {
        $reading = $this->createMock(JDictEntryReadingEntity::class);
        $reading->method('getReading')->willReturn($r);
        return $reading;
    }

    /**
     * @param $m
     * @return MockObject
     */
    private function metaEntity($m)
    {
        $meta = $this->createMock(JDictEntryMetaEntity::class);
        $meta->method('getMeta')->willReturn($m);
        return $meta;
    }


    /**
     * @param $kanji
     * @param $common
     * @return MockObject
     */
    private function kanjiEntity($kanji, $common)
    {
        $kanjiEntity = $this->createMock(JDictEntryKanjiEntity::class);
        $kanjiEntity->method('getKanji')->willReturn($kanji);
        $kanjiEntity->method('getCommon')->willReturn($common);
        return $kanjiEntity;
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

    /**
     * @param $query MockObject
     */
    private function verifyParameters($query, ...$params)
    {
        $query->expects($this->exactly(count($params)))
            ->method('setParameter')
            ->withConsecutive(...$params);
    }


}
