<?php


use Phinx\Migration\AbstractMigration;

class KanjiCatalogsRelTable extends AbstractMigration
{

    public function up() {
        $table = $this->table('kanji_catalogs_rel');
        $table->addColumn('id_kanji', 'integer')
            ->addColumn('id_catalog', 'integer')
            ->addColumn('level', 'integer')
            ->addColumn('n', 'integer')
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['id_kanji', 'id_catalog'], [
                'unique' => true,
                'name' => 'idx_kanji_catalogs_rel_id_kanji_id_catalog'
            ])
            ->addIndex(['id_kanji'], ['name' => 'idx_kanji_catalogs_rel_id_kanji'])
            ->addIndex(['id_catalog'], ['name' => 'idx_kanji_catalogs_rel_id_catalog'])
            ->addForeignKey('id_kanji', 'kanji', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])
            ->addForeignKey('id_catalog', 'kanji_catalogs', 'id', ['delete'=> 'RESTRICT', 'update'=> 'RESTRICT'])
            ->create();
    }
}
