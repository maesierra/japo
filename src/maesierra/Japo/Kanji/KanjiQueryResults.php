<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 15/09/18
 * Time: 23:03
 */

namespace maesierra\Japo\Kanji;


use maesierra\Japo\Common\Query\Page;


class KanjiQueryResults {

    /** @var KanjiQueryResult */
    public $kanjis;

    /** @var Page */
    public $page;

    /** @var int */
    public $total;

    /** @var  KanjiCatalog */
    public $catalog;

    /** @var  KanjiQuery */
    public $query;
}