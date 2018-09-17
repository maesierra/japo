<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 10/09/2018
 * Time: 20:37
 */

namespace maesierra\Japo\DB;


use Doctrine\ORM\EntityManager;
use maesierra\Japo\Entity\KanjiCatalog;
use maesierra\Japo\Kanji\KanjiQuery;
use maesierra\Japo\Kanji\KanjiQueryResults;
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
        return $this->entityManager->getRepository(KanjiCatalog::class)->findAll();
    }

    /**
     * @param $query KanjiQuery
     * @return KanjiQueryResults
     */
    public function kanjiQuery($query) {
        $conditions = [];
        $tables = [];
        if ($query->catalog || $query->catalogId) {
            $tables[] = "k.catalogs cat";
            if ($query->catalog) {
                $tables[] = "cat.catalog c";
                $conditions[] = "c.slug='{$query->catalog}'";
            } else {
                $conditions[] = "cat.idCatalog={$query->catalogId}";
            }
            if ($query->level) {
                $levels = !is_array($query->level) ? [$query->level] : $query->level;
                $levels = array_filter($levels, function($l) {
                    return trim($l) !== '';
                });
                if (!empty($query->level)) {
                    $conditions[] = "cat.level in (".implode(",", $levels).")";
                }
            }

        }
        if ($query->reading)  {
            $tables[] = "k.readings reading";
            $hiragana = JapaneseLanguage::toHiragana($query->reading);
            $katakana = JapaneseLanguage::toKatakana($query->reading);
            if ($query->kunOnly) {
                $conditions[] = "reading.reading='$hiragana' and reading.kind='K'";
            } else if ($query->onOnly) {
                $conditions[] = "reading.reading='$katakana' and reading.kind='O'";
            } else {
                //both readings are OK
                $conditions[] = "(reading.reading='$hiragana' and reading.kind='K') or (reading.reading='$katakana' and reading.kind='O')";
            }
        }
        if ($query->meaning) {
            $tables[] = "k.meanings gloss";
            $conditions[] = "gloss.meaning like '%{$query->meaning}%'";
        }
        $sortColumns = array(
            "id"=>array("col"=>"k.idKanji"),
            "id desc"=>array("col"=>"k.idKanji desc"),
            "kanji"=>array("col"=>"k.kanji"),
            "kanji desc"=>array("col"=>"k.kanji desc"),
            "level"=>array("col"=>"cat.idCatalog, cat.level, cat.n", "join"=>"k.catalogs cat"),
            "level desc"=>array("col"=>"cat.idCatalog desc, cat.level desc, cat.n desc", "join"=>"k.catalogs cat"),
            "on"=>array("col"=>"reading.kind desc, reading.reading", "join"=>"k.readings reading"),
            "on desc"=>array("col"=>"reading.kind desc, reading.reading desc", "join"=>"k.readings reading"),
            "kun"=>array("col"=>"reading.kind, reading.reading", "join"=>"k.readings reading"),
            "kun desc"=>array("col"=>"reading.kind, reading.reading desc", "join"=>"k.readings reading"),
            "meaning"=>array("col"=>"gloss.meaning", "join"=>"k.meanings gloss"),
            "meaning desc"=>array("col"=>"gloss.meaning desc", "join"=>"k.meanings gloss")
        );
        $orderBy = "";
        if ($sort && isset($sortColumns[$sort])) {
            $orderBy = "order by ".$sortColumns[$sort]["col"];
            $join = $sortColumns[$sort]["join"];
            if (isset($join) && (strpos($tables, $join) === false)) {
                $tables = "$tables JOIN $join";
            }
        }
        $dql = "select k from \\maesierra\\Japo\\entity\\Kanji k $tables where $query $orderBy";
        $this->logger->debug($dql);
        $query = $this->entityManager->createQuery($dql);
        if ($page) {
            $countQuery = $this->entityManager->createQuery(str_replace('select k', 'select count(k.idKanji)', $dql));
            $page->setTotal($countQuery->getSingleScalarResult());
            $query->setMaxResults($page->getPageSize());
            $query->setFirstResult($page->getOffset());
        }
        return $query->getResult();

    }

}