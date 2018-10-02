<?php


use Phinx\Migration\AbstractMigration;

class HelpWordWithForeignKeys extends AbstractMigration
{
    public function change() {
        $builder = $this->getQueryBuilder();
        $ids = array_map('current', $builder->select(['kanji_readings.id'])
                ->from('kanji_readings')
                ->leftJoin('words', 'words.id = kanji_readings.help_word')
                ->whereNull(['words.id'])
                ->whereNotNull(['kanji_readings.help_word'])
                ->execute()->fetchAll());

        $this->getQueryBuilder()->update('kanji_readings')
                                ->set('help_word', null)
                                ->whereInList('id', $ids)
                                ->execute();

        $this->table('kanji_readings')->addForeignKey('help_word', 'words', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])->save();
    }
}
