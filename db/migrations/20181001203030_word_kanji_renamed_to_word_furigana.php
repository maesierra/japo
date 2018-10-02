<?php


use Phinx\Migration\AbstractMigration;

class WordKanjiRenamedToWordFurigana extends AbstractMigration
{
    public function change() {
        $this->table('word_kanji')->rename('word_furigana')->save();
    }
}
