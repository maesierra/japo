<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 14/09/18
 * Time: 21:08
 */

namespace maesierra\Japo\Kanji;


use maesierra\Japo\Common\Query\Sort;

class KanjiQuery
{

    public $catalogId = null;
    public $catalog = null;
    public $level = null;
    public $reading = null;
    public $kunOnly = false;
    public $onOnly = false;
    public $meaning = null;
    /** @var Sort */
    public $sort = null;
    public $page = null;
    public $pageSize = null;


    public function __construct($array = []) {
        $sort = null;
        $sortDirection = Sort::SORT_ASC;
        foreach ($array as $prop => $value) {
            switch ($prop) {
                case 'sort':
                    $sort = $value;
                    break;
                case 'order':
                    if (in_array($value, [Sort::SORT_ASC, Sort::SORT_DESC])) {
                        $sortDirection = $value;
                    }
                    break;
                default:
                    if (property_exists($this, $prop)) {
                        $this->{$prop} = $value;
                    }
            }

        }
        $this->kunOnly = isset($this->kunOnly) && $this->kunOnly;
        $this->onOnly  = isset($this->onOnly) && $this->onOnly;
        if ($sort) {
            $this->sort = new Sort($sort, $sortDirection);
        }
    }

}
    