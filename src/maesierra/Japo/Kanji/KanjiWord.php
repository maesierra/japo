<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 16/09/18
 * Time: 08:37
 */

namespace maesierra\Japo\Kanji;


class KanjiWord {

    /** @var  int */
    public $id;
    /** @var  string */
    public $kana;
    /** @var  string */
    public $kanji;
    /** @var  string[] */
    public $meanings;

    public function __construct($array = []) {
        foreach ($array as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }
    }
}