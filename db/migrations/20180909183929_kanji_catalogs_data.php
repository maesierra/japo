<?php


use Phinx\Migration\AbstractMigration;

class KanjiCatalogsData extends AbstractMigration
{

    public function up() {
        $this->table('kanji_catalogs')
            ->insert([
                ['name' => 'Basic Kanji Book I',  'slug' => 'basic-kanji-book'],
                ['name' => 'Kyōiku kanji', 'slug' => 'kyoiku-kanji'],
                ['name' => 'JLPT',  'slug' => 'jlpt'],
                ['name' => 'Minna no nihongo 2',  'slug' =>  'minna-no-nihongo-2'],
                ['name' => 'Intermediate Kanji Book', 'slug' => 'ikb'],
            ])
        ->save();
    }
}
