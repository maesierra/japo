<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 21:00
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity\Word;


/**
 * @Entity @Table(name="word_meanings")
 */

class WordMeaning {

    /** @Id
     * @Column(type="integer", name="id")
     * @GeneratedValue
     * @var int
     */
    private $id;

    /**
     * @var int
     * @Column(type="smallint", name="n")
     */
    private $n;

    /**
     * @var string
     * @Column(type="string", name="meaning")
     */
    private $meaning;

    /**
     * @var Word
     * @ManyToOne(targetEntity="Word", inversedBy="WordMeaning")
     * @JoinColumn(name="word_id", referencedColumnName="id")
     */
    private $word;


    public function __construct() {
    }

    public function __toString() {
        return $this->meaning;
    }


    public function getIdWord() {
        return $this->word ? $this->word->getIdWord() : null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getN()
    {
        return $this->n;
    }

    /**
     * @param int $n
     */
    public function setN($n)
    {
        $this->n = $n;
    }

    /**
     * @return string
     */
    public function getMeaning()
    {
        return $this->meaning;
    }

    /**
     * @param string $meaning
     */
    public function setMeaning($meaning)
    {
        $this->meaning = $meaning;
    }

    /**
     * @return Word
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param Word $word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }



}