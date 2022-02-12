<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 03/10/2018
 * Time: 2:45
 */

namespace maesierra\Japo\Kanji;


use PHPUnit\Framework\TestCase;

class KanjiTest extends TestCase {

    public function testJsonDeserialize() {
        $json = '{"kanji":"届","kun":[{"reading":"とどーく","type":"K","helpWord":{"id":3108}},{"reading":"とどーける","type":"K","helpWord":{"id":4813}}],"on":[{"reading":"カイ","type":"O","helpWord":null}],"meanings":["Enviar","Notificar"],"catalogs":{"1":{"catalogId":"1","level":353,"n":792},"2":{"catalogId":"2","level":606,"n":876}}}';
        $kanji = new Kanji();
        $kanji->kanji = '届';
        $kanji->kun = [
            $this->reading("とどーく", "K", 3108),
            $this->reading("とどーける","K",4813)
        ];
        $kanji->on = [
            $this->reading("カイ", "O", null)
        ];
        $kanji->meanings = ["Enviar", "Notificar"];
        $kanji->catalogs = [
            1 => new KanjiCatalogEntry(["catalogId" => "1", "level" => 353, "n" => 792]),
            2 => new KanjiCatalogEntry(["catalogId" => "2", "level" => 606, "n" => 876])
        ];
        $this->assertEquals($kanji, new Kanji(json_decode($json)));
    }

    /**
     * @param $reading
     * @param $type
     * @param $helpWord
     * @return KanjiReading
     */
    private function reading($reading, $type, $helpWord)
    {
        $r = new KanjiReading();
        $r->reading = $reading;
        $r->type = $type;
        if ($helpWord) {
            $r->helpWord = new KanjiWord(["id" => $helpWord]);
        }
        return $r;
    }


}
