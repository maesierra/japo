<?php


use Phinx\Migration\AbstractMigration;

class KanjiStrokesTable extends AbstractMigration
{
    public function up() {
        $table = $this->table('kanji_strokes');
        $table->addColumn('id_kanji', 'integer')
            ->addColumn('position', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY])
            ->addColumn('path', 'text')
            ->addColumn('type', 'string', ['null' => true, 'limit' => 3])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['id_kanji'], ['name' => 'idx_kanji_strokes_id_kanji'])
            ->create();
    }
}
