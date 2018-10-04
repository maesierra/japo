<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 20:44
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity;

use \maesierra\Japo\Entity\Word\Word;

/**
 * @Entity @Table(name="kanji_readings")
 */

class KanjiReading
{
    /** @GeneratedValue @Id @Column(type="bigint", name="id") */
    private $id;

    /** @Column(type="bigint", name="id_kanji") */
    private $idKanji;
    /** @Column(type="string", name="reading") */
    private $reading;
    /** @Column(type="string", name="reading_type")*/
    private $kind;
    /** @Column(type="bigint", name="help_word")*/
    private $helpWordId;

    /**
     * @ManyToOne(targetEntity="Kanji", inversedBy="KanjiReading"))
     * @JoinColumn(name="id_kanji", referencedColumnName="id")
     * @var Kanji
     */
    private $kanji;

    /**
     * @var Word
     * @ManyToOne(targetEntity="maesierra\Japo\Entity\Word\Word", inversedBy="KanjiReading"))
     * @JoinColumn(name="help_word", referencedColumnName="id")
     */

    private $helpWord;


    public function __construct()
    {
    }
    public function __toString()
    {
        return $this->reading;
    }

    /**
     * @return int
     */
    public function getIdKanji() {
        return $this->idKanji;
    }

    /**
     * @param $idKanji int
     */
    public function setIdKanji($idKanji) {
        $this->idKanji = $idKanji;
    }


    /**
     * @return string
     */
    public function getReading() {
        return $this->reading;
    }

    /**
     * @param $reading string
     */
    public function setReading($reading) {
        $this->reading = $reading;
    }

    /**
     * @return string
     */
    public function getKind() {
        return $this->kind;
    }

    /**
     * @param $kind string
     */
    public function setKind($kind) {
        $this->kind = $kind;
    }

    /**
     * @return int
     */
    public function getHelpWordId() {
        return $this->helpWordId;
    }

    /**
     * @param $helpWordId int
     */
    public function setHelpWordId($helpWordId) {
        $this->helpWordId = $helpWordId;
    }

    /**
     * @param $kanji Kanji
     */
    public function setKanji($kanji) {
        $this->idKanji = $kanji->getId();
        $this->kanji = $kanji;
    }

    /**
     * @return Kanji
     */
    public function getKanji() {
        return $this->kanji;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return Word
     */
    public function getHelpWord() {
        return $this->helpWord;
    }

    /**
     * @param Word $helpWord
     */
    public function setHelpWord($helpWord) {
        $this->helpWord = $helpWord;
    }

}