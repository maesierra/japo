<?php


use Phinx\Migration\AbstractMigration;

class JDictEntryTable extends AbstractMigration
{
    public function up() {
        $this->table('jdict_entry')->create();
    }
}
