<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 29/09/2018
 * Time: 23:42
 */

namespace maesierra\Japo\JDict;


class JDictEntryKanji {

    /** @var  string */
    public $kanji;
    /** @var  boolean */
    public $common;

    /**
     * JDictEntryKanji constructor.
     * @param string $kanji
     * @param bool $common
     */
    public function __construct($kanji, $common) {
        $this->kanji = $kanji;
        $this->common = $common;
    }


}