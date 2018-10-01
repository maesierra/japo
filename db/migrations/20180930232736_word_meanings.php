<?php


use Phinx\Migration\AbstractMigration;

class WordMeanings extends AbstractMigration
{
    public function up() {
        $table = $this->table('word_meanings');
        $table->addColumn('word_id', 'integer')
            ->addColumn('n', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL])
            ->addColumn('meaning', 'string', ['limit' => 1024])
            ->addIndex(['word_id', 'n'], ['name' => 'idx_word_meanings_word_id_n', 'unique' => true])
            ->addIndex(['word_id'], ['name' => 'idx_word_meanings_word_id'])
            ->addIndex(['meaning'], ['name' => 'idx_word_meanings_meaning'])
            ->addForeignKey('word_id', 'words', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])
            ->create();
    }
}
