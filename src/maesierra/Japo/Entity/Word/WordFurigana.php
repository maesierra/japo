<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 21:01
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity\Word;

use maesierra\Japo\Entity\Kanji\Kanji;


/**
 * @Entity @Table(name="word_furigana")
 */

class WordFurigana
{
    /**
     * @var int
     * @Column(type="smallint", name="pos_start")
     */
    private $start;

    /**
     * @var int
     * @Column(type="smallint", name="pos_end")
     */
    private $end;

    /**
     * @var Word
     * @Id @ManyToOne(targetEntity="Word", inversedBy="WordMeaning")
     * @JoinColumn(name="word_id", referencedColumnName="id")
     */
    private $word;
    /**
     * @var Kanji
     * @Id @ManyToOne(targetEntity="maesierra\Japo\Entity\Kanji\Kanji", inversedBy="WordFurigana")
     * @JoinColumn(name="id_kanji", referencedColumnName="id")
     */
    private $kanji;

    public function __construct() {
    }

    /**
     * @return int
     */
    public function getIdWord() {
        return $this->word->getIdWord();
    }


    /**
     * @return int
     */
    public function getIdKanji() {
        return $this->kanji->getId();
    }

    /**
     * @return int
     */
    public function getStart() {
        return $this->start;
    }

    /**
     * @param $start int
     */
    public function setStart($start) {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getEnd() {
        return $this->end;
    }

    /**
     * @param $end int
     */
    public function setEnd($end) {
        $this->end = $end;
    }

    /**
     * @return Word
     */
    public function getWord() {
        return $this->word;
    }

    /**
     * @param $word Word
     */
    public function setWord($word) {
        $this->word = $word;
    }

    /**
     * @param $kanji Kanji
     */
    public function setKanji($kanji) {
        $this->kanji = $kanji;
    }

    /**
     * @return Kanji
     */
    public function getKanji() {
        return $this->kanji;
    }
}