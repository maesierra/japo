<?php


use Phinx\Migration\AbstractMigration;

class JDictEntryGlossTable extends AbstractMigration
{
    public function up() {
        $this->table('jdict_entry_gloss')
            ->addColumn('gloss_id', 'integer', ['null' => false])
            ->addColumn('gloss', 'string', ['limit' => 128])
            ->changePrimaryKey(['id', 'gloss_id'])
            ->create();
    }
}
