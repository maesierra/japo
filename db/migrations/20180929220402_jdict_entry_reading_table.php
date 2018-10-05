<?php
use Phinx\Migration\AbstractMigration;
class JdictEntryReadingTable extends AbstractMigration {
    public function up() {

        $this->table('jdict_entry_reading')
            ->addColumn('reading_id', 'integer', ['null' => false])
            ->addColumn('reading', 'string', ['limit' => 128])
            ->changePrimaryKey(['id', 'reading_id'])
            ->create();

    }
}