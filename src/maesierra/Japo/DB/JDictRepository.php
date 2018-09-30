<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 10/09/2018
 * Time: 20:37
 */

namespace maesierra\Japo\DB;


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
use maesierra\Japo\JDict\JDictQueryResults;
use Monolog\Logger;

class JDictRepository {

    /** @var  EntityManager */
    public $entityManager;

    /** @var  Logger */
    public $logger;

    /**
     * KanjiRepository constructor.
     * @param EntityManager $entityManager
     * @param Logger $logger
     */
    public function __construct($entityManager, $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }


    /**
     * @param $jdictQuery JDictQuery
     * @return JDictQueryResults
     */
    public function query($jdictQuery) {
        $this->logger->debug("JDictQuery:".json_encode($jdictQuery));
        $conditions = [];
        $join = [];
        $parameters = [];
        if ($jdictQuery->kanji) {
            $join[] = "entry.kanji kanji";
            if ($jdictQuery->exact) {
                $conditions[] = "kanji.kanji=:kanji";
                $parameters['kanji'] = $jdictQuery->kanji;
            } else {
                $conditions[] = "kanji.kanji like :kanji";
                $parameters['kanji'] = "%{$jdictQuery->kanji}%";
            }
        }
        if ($jdictQuery->reading)  {
            $join[] = "entry.readings reading";
            if ($jdictQuery->exact) {
                $conditions[] = "reading.reading=:reading";
                $parameters['reading'] = $jdictQuery->reading;
            } else {
                $conditions[] = "reading.reading like :reading";
                $parameters['reading'] = "%{$jdictQuery->reading}%";
            }
        }
        if ($jdictQuery->gloss)  {
            $join[] = "entry.gloss gloss";
            if ($jdictQuery->exact) {
                $conditions[] = "gloss.gloss=:gloss";
                $parameters['gloss'] = $jdictQuery->gloss;
            } else {
                $conditions[] = "gloss.gloss like :gloss";
                $parameters['gloss'] = "%{$jdictQuery->gloss}%";
            }
        }

        $join = implode(" JOIN ", array_unique($join));
        $conditions = implode(" AND ", array_map(function ($c) {return "($c)";}, array_unique($conditions)));
        $dql = "select entry from \\maesierra\\Japo\\Entity\\JDict\\JDictEntry entry"
                                    .($join ? " JOIN $join" : '')
                                    .($conditions ? " WHERE $conditions" : '');

        $this->logger->debug("JDict Query: $dql");
        $results = new JDictQueryResults();
        $results->query = $jdictQuery;
        $query = $this->entityManager->createQuery($dql);
        $countQueryDdl = str_replace('select entry', 'select count(entry.id)', $dql);
        $countQuery = $this->entityManager->createQuery($countQueryDdl);
        foreach ($parameters as $key => $value) {
            $query->setParameter($key, $value);
            $countQuery->setParameter($key, $value);
        }
        $results->total = $countQuery->getSingleScalarResult();
        $this->logger->debug("Jdict Query total {$results->total}");
        $hasPage = !is_null($jdictQuery->page) && $jdictQuery->pageSize;
        if ($hasPage) {
            $results->page = new Page($jdictQuery->page, $jdictQuery->pageSize, $results->total);
            $query->setMaxResults($results->page->getPageSize());
            $query->setFirstResult($results->page->getOffset());
        }
        $results->entries = [];
        $entries = [];
        /** @var JDictEntryEntity $entry */
        foreach ($query->getResult() as $entry) {
            $result = new JDictEntry();
            $result->id = $entry->getId();
            $result->gloss = array_map(function ($gloss) {
                /** @var JDictEntryGlossEntity $gloss */
                return $gloss->getGloss();
            }, $entry->getGloss()->toArray());
            $result->kanji = array_map(function ($kanji) {
                /** @var JDictEntryKanjiEntity $kanji */
                return new JDictEntryKanji($kanji->getKanji(), $kanji->getCommon());
            }, $entry->getKanji()->toArray());
            $result->readings = array_map(function ($reading) {
                /** @var JDictEntryReadingEntity $reading */
                return $reading->getReading();
            }, $entry->getReadings()->toArray());
            $result->meta = array_map(function ($meta) {
                /** @var JDictEntryMetaEntity $meta */
                return $meta->getMeta();
            }, $entry->getMeta()->toArray());
            $entries[] = $result;
        }
        $results->entries = $entries;
        return $results;
    }
}