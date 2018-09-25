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
use maesierra\Japo\Common\Query\Sort;
use maesierra\Japo\Entity\Kanji;
use maesierra\Japo\Kanji\KanjiCatalog;
use maesierra\Japo\Kanji\KanjiCatalogEntry;
use maesierra\Japo\Kanji\KanjiQuery;
use maesierra\Japo\Kanji\KanjiQueryResult;
use maesierra\Japo\Kanji\KanjiQueryResults;
use maesierra\Japo\Kanji\KanjiReading;
use maesierra\Japo\Lang\JapaneseLanguageHelper as Japanese;
use Monolog\Logger;

class KanjiRepository {

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
     * @return KanjiCatalog[]
     */
    public function listCatalogs() {
        return array_map(function($catalogEntity) {
            /** @var \maesierra\Japo\Entity\KanjiCatalog $catalogEntity */
            $catalog = new KanjiCatalog();
            $catalog->id = $catalogEntity->getId();
            $catalog->name = $catalogEntity->getName();
            $catalog->slug = $catalogEntity->getSlug();
            return $catalog;
        }, $this->entityManager->getRepository(\maesierra\Japo\Entity\KanjiCatalog::class)->findAll());
    }

    /**
     * @param $kanjiQuery KanjiQuery
     * @return KanjiQueryResults
     */
    public function query($kanjiQuery) {
        $this->logger->debug("KanjiQuery:".json_encode($kanjiQuery));
        $conditions = [];
        $join = [];
        $sortColumns = [];
        $parameters = [];
        $filterByCatalog = $kanjiQuery->catalog || !is_null($kanjiQuery->catalogId);
        if ($filterByCatalog) {
            $join[] = "k.catalogs cat";
            if ($kanjiQuery->catalog) {
                $join[] = "cat.catalog c";
                $conditions[] = "c.slug=:catalog";
                $parameters['catalog'] = $kanjiQuery->catalog;
            } else {
                $conditions[] = "cat.idCatalog=:catalogId";
                $parameters['catalogId'] = $kanjiQuery->catalogId;
            }
            if ($kanjiQuery->level) {
                $levels = !is_array($kanjiQuery->level) ? [$kanjiQuery->level] : $kanjiQuery->level;
                $levels = array_filter($levels, function($l) {
                    return trim($l) !== '';
                });
                if (!empty($kanjiQuery->level)) {
                    $conditions[] = "cat.level in (:levels)";
                    $parameters['levels'] = $levels;
                }
            }

        }
        if ($kanjiQuery->reading)  {
            $join[] = "k.readings reading";
            $hiragana = Japanese::toHiragana($kanjiQuery->reading);
            $katakana = Japanese::toKatakana($kanjiQuery->reading);
            if ($kanjiQuery->kunOnly) {
                $conditions[] = "reading.reading=:hiragana and reading.kind='K'";
                $parameters['hiragana'] = $hiragana;
            } else if ($kanjiQuery->onOnly) {
                $conditions[] = "reading.reading=:katakana and reading.kind='O'";
                $parameters['katakana'] = $katakana;
            } else {
                //both readings are OK
                $conditions[] = "(reading.reading=:hiragana and reading.kind='K') or (reading.reading=:katakana and reading.kind='O')";
                $parameters['hiragana'] = $hiragana;
                $parameters['katakana'] = $katakana;
            }
        }
        if ($kanjiQuery->meaning) {
            $join[] = "k.meanings gloss";
            $conditions[] = "gloss.meaning like :meaning";
            $parameters['meaning'] = "%{$kanjiQuery->meaning}%";
        }
        if ($kanjiQuery->sort) {
            switch ($kanjiQuery->sort->field) {
                case "id":
                    $sortColumns[] = 'k.id';
                    break;
                case "kanji":
                    $sortColumns[] = "k.kanji";
                    break;
                case "level":
                    $sortColumns[] = "cat.idCatalog";
                    $sortColumns[] = "cat.level";
                    $sortColumns[] = "cat.n";
                    $join[] = "k.catalogs cat";
                    break;
                case 'on':
                case 'kun':
                    $sortColumns[] = "reading.kind ".($kanjiQuery->sort->field == "on" ? "desc" : "asc");
                    $sortColumns[] = "reading.reading";
                    $join[] = "k.readings reading";
                    break;
                case 'meaning':
                    $sortColumns[] = "gloss.meaning";
                    $join[] = "k.meanings gloss";
                    break;
            }
            if ($kanjiQuery->sort->direction === Sort::SORT_DESC) {
                $sortColumns = array_map(function($c) {
                    if (strpos($c, ' asc') === false && strpos($c, ' desc') === false) {
                        return "$c desc";
                    } else {
                        return $c;
                    }
                }, $sortColumns);
            }
        }
        $join = implode(" JOIN ", array_unique($join));
        $conditions = implode(" AND ", array_map(function ($c) {return "($c)";}, array_unique($conditions)));
        $orderBy = implode(",", array_unique($sortColumns));
        $dql = "select k from \\maesierra\\Japo\\Entity\\Kanji k"
                                    .($join ? " JOIN $join" : '')
                                    .($conditions ? " WHERE $conditions" : '')
                                    .($orderBy ? " ORDER BY $orderBy" : '');

        $this->logger->debug("Kanji Query: $dql");
        $results = new KanjiQueryResults();
        $results->query = $kanjiQuery;
        $query = $this->entityManager->createQuery($dql);
        $countQueryDdl = str_replace('select k', 'select count(k.id)', $dql);
        $countQuery = $this->entityManager->createQuery($countQueryDdl);
        foreach ($parameters as $key => $value) {
            $query->setParameter($key, $value);
            $countQuery->setParameter($key, $value);
        }
        $results->total = $countQuery->getSingleScalarResult();
        $this->logger->debug("Kanji Query total {$results->total}");
        $hasPage = !is_null($kanjiQuery->page) && $kanjiQuery->pageSize;
        if ($hasPage) {
            $results->page = new Page($kanjiQuery->page, $kanjiQuery->pageSize, $results->total);
            $query->setMaxResults($results->page->getPageSize());
            $query->setFirstResult($results->page->getOffset());
        }
        $results->kanjis = [];
        $kanjis = [];
        /** @var Kanji $kanji */
        foreach ($query->getResult() as $kanji) {
            $result = new KanjiQueryResult();
            $result->id = $kanji->getId();
            $result->kanji = $kanji->getKanji();
            $result->catalogs = array_reduce($kanji->getCatalogs()->toArray(), function(&$result, $catalogEntry) use($results, $kanjiQuery, $filterByCatalog) {
                /** @var \maesierra\Japo\Entity\KanjiCatalogEntry $catalogEntry */
                $entry = new KanjiCatalogEntry();
                $catalog = $catalogEntry->getCatalog();
                $entry->level = $catalogEntry->getLevel();
                $idCatalog = $catalog->getId();
                $entry->n = $catalogEntry->getN();
                $entry->catalogName = $catalog->getName();
                $entry->catalogId = $idCatalog;
                $entry->catalogSlug = $catalog->getSlug();
                if ($kanjiQuery->catalog && $entry->catalogSlug === $kanjiQuery->catalog) {
                    $kanjiQuery->catalogId = $entry->catalogId;
                }
                $result[$entry->catalogId] = $entry;
                if ($filterByCatalog && !$results->catalog && ($kanjiQuery->catalogId == $entry->catalogId || $kanjiQuery->catalog == $entry->catalogSlug)) {
                    $results->catalog = new KanjiCatalog();
                    $results->catalog->id = $catalog->getId();
                    $results->catalog->name = $catalog->getName();
                    $results->catalog->slug = $catalog->getSlug();
                }
                return $result;
            }, []);
            $result->readings = array_map(function($kanjiReading) {
                /** @var \maesierra\Japo\Entity\KanjiReading $kanjiReading */
                $reading = new KanjiReading();
                $reading->type = $kanjiReading->getKind();
                $reading->helpWord = $kanjiReading->getHelpWordId();
                $reading->reading = $kanjiReading->getReading();
                return $reading;
            }, $kanji->getReadings()->toArray());
            $result->meanings = array_map(function($kanjiMeaning) {
                /** @var \maesierra\Japo\Entity\KanjiMeaning $kanjiMeaning */
                return $kanjiMeaning->getMeaning();
            }, $kanji->getMeanings()->toArray());
            $kanjis[] = $result;
        }
        $results->kanjis = $kanjis;
        if ($results->catalog) {
            $results->catalog->levels = $this->getCatalogLevels($kanjiQuery->catalogId);
        }
        return $results;

    }

    /**
     * @param $idCatalog int
     * @return int[]
     */
    public function getCatalogLevels($idCatalog) {
        return array_map('current', $this->entityManager->createQueryBuilder()->select('c.level')
            ->from('\maesierra\Japo\Entity\KanjiCatalogEntry', 'c')
            ->where('c.idCatalog = ?1')
            ->distinct()
            ->orderBy('c.level')
            ->getQuery()
            ->setParameter(1, $idCatalog)
            ->getScalarResult());
    }


}