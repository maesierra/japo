<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 10/09/2018
 * Time: 20:37
 */

namespace maesierra\Japo\DB;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use maesierra\Japo\Common\Query\Page;
use maesierra\Japo\Common\Query\Sort;
use maesierra\Japo\Entity\Kanji\Kanji as KanjiEntity;
use maesierra\Japo\Entity\Kanji\KanjiCatalog as KanjiCatalogEntity;
use maesierra\Japo\Entity\Kanji\KanjiCatalogEntry as KanjiCatalogEntryEntity;
use maesierra\Japo\Entity\Kanji\KanjiMeaning as KanjiMeaningEntity;
use maesierra\Japo\Entity\Kanji\KanjiReading as KanjiReadingEntity;
use maesierra\Japo\Entity\Kanji\KanjiStroke as KanjiStrokeEntity;
use maesierra\Japo\Entity\Word\Word as WordEntity;
use maesierra\Japo\Entity\Word\Word;
use maesierra\Japo\Entity\Word\WordMeaning as WordMeaningEntity;
use maesierra\Japo\Kanji\Kanji;
use maesierra\Japo\Kanji\KanjiCatalog;
use maesierra\Japo\Kanji\KanjiCatalogEntry;
use maesierra\Japo\Kanji\KanjiQuery;
use maesierra\Japo\Kanji\KanjiQueryResult;
use maesierra\Japo\Kanji\KanjiQueryResults;
use maesierra\Japo\Kanji\KanjiReading;
use maesierra\Japo\Kanji\KanjiStroke;
use maesierra\Japo\Kanji\KanjiWord;
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
     * @param KanjiCatalogEntity $catalogEntity
     * @return KanjiCatalog
     */
    private function mapKanjiCatalog($catalogEntity) {
        if (!$catalogEntity) {
            return null;
        }
        $catalog = new KanjiCatalog();
        $catalog->id = $catalogEntity->getId();
        $catalog->name = $catalogEntity->getName();
        $catalog->slug = $catalogEntity->getSlug();
        return $catalog;
    }

    /**
     * @param $entity KanjiCatalogEntryEntity
     * @return KanjiCatalogEntry
     */
    private function mapKanjiCatalogEntry($entity)
    {
        $catalog = $entity->getCatalog();
        $entry = new KanjiCatalogEntry();
        $entry->level = $entity->getLevel();
        $idCatalog = $catalog->getId();
        $entry->n = $entity->getN();
        $entry->catalogName = $catalog->getName();
        $entry->catalogId = $idCatalog;
        $entry->catalogSlug = $catalog->getSlug();
        return $entry;
    }

    /**
    * @param $entity KanjiReadingEntity
    * @return KanjiReading
    */
    private function mapKanjiReading($entity) {
        $reading = new KanjiReading();
        $reading->type = $entity->getKind();
        $reading->helpWord = $this->mapWord($entity->getHelpWord());
        $reading->reading = $entity->getReading();
        return $reading;
    }

    /**
     * @param $entity WordEntity
     * @return KanjiWord
     */
    private function mapWord($entity){
        if ($entity == null) {
            return null;
        }
        /** @var WordEntity $entity */
        $word = new KanjiWord();
        $word->id = $entity->getIdWord();
        $word->kana = $entity->getKana();
        $word->kanji = $entity->getKanji();
        $word->meanings = array_map(function ($m) {
            /** @var WordMeaningEntity $m */
            return $m->getMeaning();
        }, $entity->getMeanings()->toArray());
        return $word;
    }

    /**
     * @return KanjiCatalog[]
     */
    public function listCatalogs() {
        return array_map(function($catalogEntity) {
            return $this->mapKanjiCatalog($catalogEntity);
        }, $this->kanjiCatalogRepository()->findAll());
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
        $dql = "select k from ".KanjiEntity::class." k"
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
        /** @var KanjiEntity $kanji */
        foreach ($query->getResult() as $kanji) {
            $result = new KanjiQueryResult();
            $result->id = $kanji->getId();
            $result->kanji = $kanji->getKanji();
            $result->catalogs = array_reduce($kanji->getCatalogs()->toArray(), function($result, $catalogEntry) use($results, $kanjiQuery, $filterByCatalog) {

                $entry = $this->mapKanjiCatalogEntry($catalogEntry);
                if ($kanjiQuery->catalog && $entry->catalogSlug === $kanjiQuery->catalog) {
                    $kanjiQuery->catalogId = $entry->catalogId;
                }
                $result[$entry->catalogId] = $entry;
                if ($filterByCatalog && !$results->catalog && ($kanjiQuery->catalogId == $entry->catalogId || $kanjiQuery->catalog == $entry->catalogSlug)) {
                    $results->catalog = new KanjiCatalog();
                    $results->catalog->id = $entry->catalogId;
                    $results->catalog->name = $entry->catalogName;
                    $results->catalog->slug = $entry->catalogSlug;
                }
                return $result;
            }, []);
            $result->readings = array_map(function($kanjiReading) {
                return $this->mapKanjiReading($kanjiReading);
            }, $kanji->getReadings()->toArray());
            $result->meanings = array_map(function($kanjiMeaning) {
                /** @var KanjiMeaningEntity $kanjiMeaning */
                return $kanjiMeaning->getMeaning();
            }, $kanji->getMeanings()->toArray());
            $kanjis[] = $result;
        }
        $results->kanjis = $kanjis;
        if ($filterByCatalog) {
            if (!$results->catalog) {
                if ($kanjiQuery->catalog) {
                    $results->catalog = $this->mapKanjiCatalog($this->kanjiCatalogRepository()->findOneBy(['slug' => $kanjiQuery->catalog]));
                } else {
                    $results->catalog = $this->mapKanjiCatalog($this->kanjiCatalogRepository()->find($kanjiQuery->catalogId));
                }

            }
            if ($results->catalog) {
                $results->catalog->levels = $this->getCatalogLevels($results->catalog->id);
            }
        }
        return $results;

    }

    /**
     * @param $idCatalog int
     * @return int[]
     */
    public function getCatalogLevels($idCatalog) {
        return array_map('current', $this->entityManager->createQueryBuilder()->select('c.level')
            ->from(KanjiCatalogEntryEntity::class, 'c')
            ->where('c.idCatalog = ?1')
            ->distinct()
            ->orderBy('c.level')
            ->getQuery()
            ->setParameter(1, $idCatalog)
            ->getScalarResult());
    }

    /**
     * @param $kanji string
     * @return Kanji
     */
    public function findKanji($kanji) {
        /** @var KanjiEntity $kanjiEntity */
        $kanjiEntity = $this->kanjiRepository()->findOneBy(['kanji' => $kanji]);
        if (!$kanjiEntity) {
            return null;
        }
        $result = new Kanji();
        $result->id = $kanjiEntity->getId();
        $result->kanji = $kanjiEntity->getKanji();

        $result->catalogs = array_reduce($kanjiEntity->getCatalogs()->toArray(), function($result, $catalogEntry)  {
            $entry = $this->mapKanjiCatalogEntry($catalogEntry);
            $result[$entry->catalogId] = $entry;
            return $result;
        }, []);

        $readings = array_map(function ($kanjiReading) {return $this->mapKanjiReading($kanjiReading);}, $kanjiEntity->getReadings()->toArray());

        $result->kun = array_values(array_filter($readings, function($r) {
            /** @var KanjiReading $r */
            return $r->type == KanjiReading::TYPE_KUN;
        }));

        $result->on = array_values(array_filter($readings, function($r) {
            /** @var KanjiReading $r */
            return $r->type == KanjiReading::TYPE_ON;
        }));

        $result->meanings = array_map(function($kanjiMeaning) {
            /** @var KanjiMeaningEntity $kanjiMeaning */
            return $kanjiMeaning->getMeaning();
        }, $kanjiEntity->getMeanings()->toArray());

        $result->strokes = array_map(function($e) {
            /** @var KanjiStrokeEntity $e */
            $stroke = new KanjiStroke();
            $stroke->path = $e->getPath();
            $stroke->position = $e->getPosition();
            $stroke->type = $e->getType();
            return $stroke;
        }, $kanjiEntity->getStrokes()->toArray());

        $result->words = array_map(function($w) {
            return $this->mapWord($w);
        }, $kanjiEntity->getWords()->toArray());
        return $result;

    }

    /**
     * Saves a kanji
     * @param $kanji Kanji
     * @return Kanji
     */
    public function saveKanji($kanji) {
        $this->logger->debug("Saving kanji: ".$kanji->kanji);
        $kanjiRepository = $this->kanjiRepository();
        $wordRepository = $this->entityManager->getRepository(Word::class);
        $catalogRepository = $this->kanjiCatalogRepository();
        /** @var KanjiEntity $kanjiEntity */
        $kanjiEntity = $kanjiRepository->findOneBy(['kanji' => $kanji->kanji]);
        if (!$kanjiEntity) {
            $kanjiEntity = new KanjiEntity();
            $kanjiEntity->setKanji($kanji->kanji);
        } else {
            $this->entityManager->createQueryBuilder()
                ->delete(KanjiMeaningEntity::class, 'm')
                ->where('m.idKanji = ?1')
                ->setParameter(1, $kanjiEntity->getId())
                ->getQuery()
                ->execute();
            $this->entityManager->createQueryBuilder()
                ->delete(KanjiReadingEntity::class, 'r')
                ->where('r.idKanji = ?1')
                ->setParameter(1, $kanjiEntity->getId())
                ->getQuery()
                ->execute();
            $this->entityManager->createQueryBuilder()
                ->delete(KanjiCatalogEntryEntity::class, 'c')
                ->where('c.idKanji = ?1')
                ->setParameter(1, $kanjiEntity->getId())
                ->getQuery()
                ->execute();
        }
        $kanjiMeanings = $kanjiEntity->getMeanings();
        $kanjiMeanings->clear();
        /** @var ArrayCollection $readings */
        $readings = $kanjiEntity->getReadings();
        $readings->clear();
        $kanjiCatalogs = $kanjiEntity->getCatalogs();
        $kanjiCatalogs->clear();

        foreach (['K' => $kanji->kun, 'O' => $kanji->on] as $type => $typeReadings) {
            /** @var KanjiReading $r */
            foreach ($typeReadings as $r) {
                $reading = new KanjiReadingEntity();
                $reading->setKanji($kanjiEntity);
                $reading->setKind($type);
                $reading->setReading($r->reading);
                if ($r->helpWord) {
                    /** @var Word $helpWord */
                    $helpWord = $wordRepository->find($r->helpWord->id);
                    if ($helpWord) {
                        $reading->setHelpWordId($r->helpWord->id);
                        $reading->setHelpWord($helpWord);
                    }
                }
                $readings->add($reading);
            }
        }
        foreach ($kanji->meanings as $m) {
            $kanjiMeaning = new KanjiMeaningEntity();
            $kanjiMeaning->setKanji($kanjiEntity);
            $kanjiMeaning->setMeaning($m);
            $kanjiMeanings->add($kanjiMeaning);
        }
        foreach ($kanji->catalogs as $c) {
            /** @var KanjiCatalogEntity $catalog */
            $catalog = $catalogRepository->find($c->catalogId);
            if (!$catalog) {
                continue;
            }
            $catalogEntry = new KanjiCatalogEntryEntity();
            $catalogEntry->setKanji($kanjiEntity);
            $catalogEntry->setCatalog($catalog);
            $catalogEntry->setLevel($c->level);
            $catalogEntry->setN($c->n);
            $kanjiCatalogs->add($catalogEntry);
        }
        $this->logger->debug("Saving kanji ".Debug::dump($kanjiEntity, 3, true, false));
        $this->entityManager->persist($kanjiEntity);
        $this->entityManager->flush();
        return $kanji;

    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function kanjiCatalogRepository() {
        return $this->entityManager->getRepository(KanjiCatalogEntity::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function kanjiRepository()
    {
        return $this->entityManager->getRepository(KanjiEntity::class);
    }

}