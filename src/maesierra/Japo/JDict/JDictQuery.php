<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 14/09/18
 * Time: 21:08
 */

namespace maesierra\Japo\JDict;


class JDictQuery {

    /** @var  string */
    public $kanji = null;
    /** @var  string */
    public $reading = null;
    /** @var  string */
    public $gloss = null;
    /** @var  boolean */
    public $exact = false;
    /** @var int */
    public $page = null;
    /** @var int  */
    public $pageSize = null;


    public function __construct($array = []) {
        foreach ($array as $prop => $value) {
            switch ($prop) {
                case 'exact':
                    $this->exact = is_bool($value) ? $value : $value == 'true';
                    break;
                default:
                    if (property_exists($this, $prop)) {
                        $this->{$prop} = $value;
                    }
            }
        }
    }


}
    