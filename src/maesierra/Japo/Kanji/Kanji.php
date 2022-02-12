<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 16/09/18
 * Time: 19:21
 */

namespace maesierra\Japo\Kanji;


class Kanji {

    /** @var  int */
    public $id;
    /** @var  string */
    public $kanji;

    /** @var KanjiReading[] */
    public $kun = [];

    /** @var KanjiReading[] */
    public $on = [];

    /** @var KanjiCatalogEntry[] */
    public $catalogs = [];

    /** @var string[] */
    public $meanings = [];

    /** @var  array */
    public $words = [];

    /** @var KanjiStroke */
    public $strokes = [];

    public function __construct($array = []) {
        foreach ($array as $prop => $value) {
            switch ($prop) {
                case 'kun':
                case 'on':
                    $this->{$prop} = array_map(function($r) {
                        return new KanjiReading($r);
                    }, $value);
                    break;
                case 'catalogs':
                    $this->catalogs = array_reduce((array)$value, function($result, $e)  {
                        $entry = new KanjiCatalogEntry($e);
                        $result[$entry->catalogId] = $entry;
                        return $result;
                    }, []);
                    break;
                default:
                    if (property_exists($this, $prop)) {
                        $this->{$prop} = $value;
                    }
            }
        }
    }
}