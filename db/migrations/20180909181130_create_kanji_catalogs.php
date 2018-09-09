<?php


use Phinx\Migration\AbstractMigration;

class CreateKanjiCatalogs extends AbstractMigration
{

    public function change() {
        $table = $this->table('kanji_catalogs');
        $table->addColumn('name', 'string', ['limit' => 32])
            ->addColumn('slug', 'string', ['limit' => 255])
            ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], [
                'unique' => true,
                'name' => 'idx_kanji_catalogs_name'])
            ->addIndex(['slug'], [
                'unique' => true,
                'name' => 'idx_kanji_catalogs_slug'])
            ->create();

    }

}
