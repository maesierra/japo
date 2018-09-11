<?php


use Phinx\Migration\AbstractMigration;

class KanjiMeaningsTable extends AbstractMigration
{
    public function up() {
        $table = $this->table('kanji_meanings');
        $table->addColumn('id_kanji', 'integer')
            ->addColumn('meaning', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_SMALL])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['id_kanji'], ['name' => 'idx_kanji_meanings_id_kanji'])
            ->addForeignKey('id_kanji', 'kanji', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])
            ->create();
    }
}
