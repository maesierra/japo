<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 15/09/18
 * Time: 23:03
 */

namespace maesierra\Japo\JDict;

use maesierra\Japo\Common\Query\Page;


class JDictQueryResults {

    /** @var JDictEntry[] */
    public $entries;

    /** @var Page */
    public $page;

    /** @var int */
    public $total;

    /** @var  JDictQuery */
    public $query;
}