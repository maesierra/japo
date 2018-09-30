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

    /** @var KanjiCatalogEntry */
    public $catalogs = [];

    /** @var string[] */
    public $meanings = [];

    /** @var  array */
    public $words = [];

    /** @var KanjiStroke */
    public $strokes = [];


}