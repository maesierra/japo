<?php


use Phinx\Migration\AbstractMigration;

class WordsTable extends AbstractMigration
{
    public function up() {
        $table = $this->table('words');
        $table->addColumn('kana', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('kanji', 'string', ['limit' => 64, 'null' => true])
            ->addColumn('type', 'string', ['null' => true, 'limit' => 1])
            ->addColumn('level', 'integer', ['null' => true, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL])
            ->addColumn('verb_group', 'integer', ['null' => true, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL])
            ->addColumn('adj_type', 'string', ['null' => true, 'limit' => 1])
            ->addColumn('notes', 'text', ['null' => true, 'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_SMALL])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['kana', 'kanji'], ['name' => 'idx_words_kana_kanji', 'unique' => true])
            ->addIndex(['kana'], ['name' => 'idx_words_kana'])
            ->addIndex(['kanji'], ['name' => 'idx_words_kanji'])
            ->create();
    }
}
