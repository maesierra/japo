<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 29/09/2018
 * Time: 23:41
 */

namespace maesierra\Japo\JDict;


class JDictEntry {

    /** @var  int */
    public $id;
    /** @var  string[] */
    public $readings;
    /** @var  string[] */
    public $gloss;
    /** @var  JDictEntryKanji[] */
    public $kanji;
    /** @var  string[] */
    public $meta;
}