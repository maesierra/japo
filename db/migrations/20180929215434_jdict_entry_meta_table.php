<?php


use Phinx\Migration\AbstractMigration;

class JDictEntryMetaTable extends AbstractMigration
{
    public function up() {
        $this->table('jdict_entry_meta')
            ->addColumn('meta_id', 'integer', ['null' => false])
            ->addColumn('meta', 'string', ['limit' => 32])
            ->changePrimaryKey(['id', 'meta_id'])
            ->create();
    }
}
