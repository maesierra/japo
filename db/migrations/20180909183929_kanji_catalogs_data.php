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
        $this->getQueryBuilder()->update('kanji_catalogs')->set('id', 0)->where(['slug' => 'basic-kanji-book'])->execute();
        $this->getQueryBuilder()->update('kanji_catalogs')->set('id', 1)->where(['slug' => 'jlpt'])->execute();
        $this->getQueryBuilder()->update('kanji_catalogs')->set('id', 2)->where(['slug' => 'kyoiku-kanji'])->execute();
        $this->getQueryBuilder()->update('kanji_catalogs')->set('id', 4)->where(['slug' =>  'minna-no-nihongo-2'])->execute();
        $this->getQueryBuilder()->update('kanji_catalogs')->set('id', 5)->where(['slug' => 'ikb'])->execute();
    }
}
