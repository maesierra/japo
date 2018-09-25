<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 20:45
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity;


/**
 * @Entity @Table(name="kanji_meanings")
 */

class KanjiMeaning {

    /** @Id @Column(type="bigint", name="id") */
    private $id;

    /**
     * @Column(type="bigint", name="id_kanji")
     */
    private $idKanji;

    /** @Column(type="string", name="meaning") */
    private $meaning;

    /**
     * @var Kanji
     * @ManyToOne(targetEntity="Kanji", inversedBy="KanjiReading"))
     * @JoinColumn(name="id_kanji", referencedColumnName="id")
     */
    private $kanji;

    public function __construct() {
    }

    public function __toString() {
        return $this->meaning;
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
    public function getMeaning() {
        return $this->meaning;
    }

    /**
     * @param $meaning string
     */
    public function setMeaning($meaning) {
        $this->meaning = $meaning;
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
}?>