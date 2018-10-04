<?php
namespace maesierra\Japo\Entity\Word;

use Doctrine\Common\Collections\ArrayCollection;
use maesierra\Japo\JapaneseLanguage;
use maesierra\UTF8\UTF8Utils;


/**
 * @Entity @Table(name="words")
 */
class Word implements \JsonSerializable {

	/** @Id
     * @Column(type="integer", name="id")
     * @GeneratedValue
     * @var int
     */
	private $idWord;

	/**
     * @Column(type="string")
     * @var string
     */
	private $kanji;

	/**
     * @Column(type="string")
     * @var string
     */
	private $kana;

	/**
     * @Column(type="smallint", name="level")
     * @var int
     */
	private $level;

	/**
     * @Column(type="string", name="type")
     * @var string
     */
	private $wordType;

	/**
     * @var string
     * @Column(type="integer", name="verb_group")
     */
	private $verbGroup;

	/**
     * @var string
     * @Column(type="string", name="adj_type")
     */
	private $adjetiveGroup;

	/**
     * @var string
     * @Column(type="string")
     */
	private $notes;

	/**
      * @var WordMeaning
 	  * @OneToMany(targetEntity="WordMeaning", mappedBy="word", cascade={"persist", "remove"}, orphanRemoval=true)
      * @OrderBy({"n" = "ASC"})
      */	
	private $meanings;

    /**
     * @var WordFurigana[]
     * @OneToMany(targetEntity="WordFurigana", mappedBy="word", cascade={"persist", "remove"}, orphanRemoval=true)
     * @OrderBy({"start" = "ASC"})
     */
    private $furigana;



    public function __construct() {
        $this->meanings = new ArrayCollection();
        $this->furigana = new ArrayCollection();
    }

    public function __toString() {
        return $this->kanji ? $this->kanji : $this->kana;
    }

    /**
     * @return int
     */
	public function getIdWord() {
		return $this->idWord;
	}

    /**
     * @param $idWord int
     */
	public function setIdWord($idWord)  {
		$this->idWord = $idWord;
	}

    /**
     * @return string
     */
	public function getKanji() {
		return $this->kanji;
	}

    /**
     * @param string $kanji
     */
    public function setKanji($kanji) {
        $this->kanji = $kanji;
    }

    /**
     * @return string
     */
    public function getKana() {
        return $this->kana;
    }

    /**
     * @param string $kana
     */
    public function setKana($kana)
    {
        $this->kana = $kana;
    }

    /**
     * @return int
     */
    public function getLevel() {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getWordType() {
        return $this->wordType;
    }

    /**
     * @param string $wordType
     */
    public function setWordType($wordType) {
        $this->wordType = $wordType;
    }

    /**
     * @return string
     */
    public function getVerbGroup() {
        return $this->verbGroup;
    }

    /**
     * @param string $verbGroup
     */
    public function setVerbGroup($verbGroup) {
        $this->verbGroup = $verbGroup;
    }

    /**
     * @return string
     */
    public function getAdjetiveGroup() {
        return $this->adjetiveGroup;
    }

    /**
     * @param string $adjetiveGroup
     */
    public function setAdjetiveGroup($adjetiveGroup) {
        $this->adjetiveGroup = $adjetiveGroup;
    }

    /**
     * @return string
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes) {
        $this->notes = $notes;
    }

    /**
     * @return ArrayCollection
     */
    public function getMeanings() {
        return $this->meanings;
    }

    /**
     * @return ArrayCollection
     */
    public function getFurigana() {
        return $this->furigana;
    }

    /**
     * @return bool
     */
	public function isVerb() {
		return $this->wordType == "V";
	}

    /**
     * @return bool
     */
	public function isAdjetive() {
		return $this->wordType == "A";
	}
	

    public function jsonSerialize() {
        return [
            'id' => $this->idWord,
            'kana' => $this->kana,
            'kanji' => $this->kanji,
            'meanings' => array_map(function ($m) {
                /** @var WordMeaning $m */
                return $m->getMeaning();
            }, $this->meanings->toArray())
        ];

    }
}
?>