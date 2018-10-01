<?php


use Phinx\Migration\AbstractMigration;

class WordKanjiTable extends AbstractMigration
{
    public function up() {
        $table = $this->table('word_kanji');
        $table->addColumn('word_id', 'integer')
            ->addColumn('id_kanji', 'integer')
            ->addColumn('pos_start', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true])
            ->addColumn('pos_end', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => true])
            ->addIndex(['word_id', 'id_kanji'], ['name' => 'idx_word_kanji_word_id_id_kanji', 'unique' => true])
            ->addIndex(['word_id'], ['name' => 'idx_word_kanji_word_id'])
            ->addIndex(['id_kanji'], ['name' => 'idx_word_kanji_id_kanji'])
            ->addForeignKey('word_id', 'words', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])
            ->addForeignKey('id_kanji', 'kanji', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])
            ->create();
    }
}
