<?php


use Phinx\Migration\AbstractMigration;

class JDictEntryKanjiTable extends AbstractMigration
{
    public function up() {
        $this->table('jdict_entry_kanji')
            ->addColumn('kanji_id', 'integer', ['null' => false])
            ->addColumn('kanji', 'string', ['limit' => 512])
            ->addColumn('common', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0])
            ->changePrimaryKey(['id', 'kanji_id'])
            ->create();
    }
}
