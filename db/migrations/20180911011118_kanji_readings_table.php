<?php


use Phinx\Migration\AbstractMigration;

class KanjiReadingsTable extends AbstractMigration
{
    public function up() {
        $table = $this->table('kanji_readings');
        $table->addColumn('id_kanji', 'integer')
            ->addColumn('reading', 'string', ['limit' => 32])
            ->addColumn('reading_type', 'string', ['limit' => 1])
            ->addColumn('help_word', 'integer', ['null' => true])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['id_kanji', 'reading'], [
                'unique' => true,
                'name' => 'idx_kanji_readings_id_kanji_reading'
            ])
            ->addIndex(['id_kanji'], ['name' => 'idx_kanji_readings_id_kanji'])
            ->addForeignKey('id_kanji', 'kanji', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])
            ->create();
    }

}
