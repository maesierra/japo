<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 16/09/18
 * Time: 19:21
 */

namespace maesierra\Japo\Kanji;


class KanjiQueryResult {

    public $id;
    public $kanji;
    /** @var KanjiReading[] */
    public $readings;

    /** @var KanjiCatalogEntry */
    public $catalogs;

    /** @var string[] */
    public $meanings;


}