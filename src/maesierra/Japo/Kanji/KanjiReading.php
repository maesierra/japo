<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 16/09/18
 * Time: 08:37
 */

namespace maesierra\Japo\Kanji;


class KanjiReading {

    const TYPE_KUN = 'K';
    const TYPE_ON = 'O';

    public $reading;
    public $type;
    /** @var  KanjiWord */
    public $helpWord;

    public function __construct($array = []) {
        foreach ($array as $prop => $value) {
            switch ($prop) {
                case 'helpWord':
                    $this->helpWord = $value ? new KanjiWord($value) : null;
                    break;
                default:
                    if (property_exists($this, $prop)) {
                        $this->{$prop} = $value;
                    }
            }
        }
    }
}