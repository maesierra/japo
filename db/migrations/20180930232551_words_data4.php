<?php
use Phinx\Migration\AbstractMigration;
class WordsData4 extends AbstractMigration {
    public function up() {
        $this->table('words')->insert([
            array('id' => '13','kana' => 'あう','kanji' => '会う','type' => 'V','level' => '0','verb_group' => '1','adj_type' => NULL,'notes' => "ひとに　あう\nものに　あう\nひとと　あう => Encontrarse con alguien por sospresa",'created' => '2012-01-22 10:35:22'),
            array('id' => '531','kana' => 'でる','kanji' => '出る','type' => 'V','level' => '13','verb_group' => '2','adj_type' => NULL,'notes' => "<Lugar>をでます=>salir de un lugar\n<Evento>にでる　=> Atender a un evento",'created' => '2012-01-22 10:35:22'),
            array('id' => '1557','kana' => 'すむ','kanji' => '住む','type' => 'V','level' => NULL,'verb_group' => NULL,'adj_type' => NULL,'notes' => "ところに　すむ.\nSe suele usar gerundio.\nところに　すんで　います。",'created' => '2012-01-22 10:35:22'),
            array('id' => '2355','kana' => 'うける','kanji' => '受ける','type' => 'V','level' => '31','verb_group' => '2','adj_type' => NULL,'notes' => "テストを受ける => Hacer un examen\nテストに受ける => Aprobar un examen",'created' => '2012-01-22 10:35:22'),
            array('id' => '2559','kana' => 'であう','kanji' => '出会う','type' => 'V','level' => NULL,'verb_group' => '1','adj_type' => NULL,'notes' => "Usa la partícula と para indicar que te encuentras a alguien\n友達と　出会いました。",'created' => '2012-01-22 10:35:22'),
            array('id' => '2627','kana' => 'ふく','kanji' => '吹く','type' => 'V','level' => '32','verb_group' => '1','adj_type' => NULL,'notes' => "風がふく => soplar el viento\n笛をふく => tocar la flauta",'created' => '2012-01-22 10:35:22'),
            array('id' => '2892','kana' => 'たおれる','kanji' => '倒れる','type' => 'V','level' => '29','verb_group' => '2','adj_type' => NULL,'notes' => "No se usa cuando se cae desde una altura superior\nビルがたおれる",'created' => '2012-01-22 10:35:22'),
            array('id' => '3661','kana' => 'よい','kanji' => '良い','type' => 'A','level' => NULL,'verb_group' => NULL,'adj_type' => 'い','notes' => "En presente afirmativo => いい\nNo se suele usar el kanji",'created' => '2013-10-31 16:23:33')
        ])->save();
    }
}