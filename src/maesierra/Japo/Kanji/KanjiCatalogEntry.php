<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 16/09/18
 * Time: 08:40
 */

namespace maesierra\Japo\Kanji;


class KanjiCatalogEntry {

    public $catalogName;
    public $catalogSlug;
    public $catalogId;
    public $n;
    public $level;

    public function __construct($array = []) {
        foreach ($array as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }
    }
}