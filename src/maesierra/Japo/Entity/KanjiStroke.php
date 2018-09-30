<?php
namespace maesierra\Japo\Entity;
/**
 * @Entity
 * @Table(name="kanji_strokes")
 */

class KanjiStroke
{
    /** @Id @Column(type="bigint", name="id") */
    private $id;

	/** @Column(type="bigint", name="id_kanji") */
	private $idKanji;

	/** @Column(type="smallint")*/
	private $position;

	/** @Column(type="string")*/
	private $path;

	/** @Column(type="string")*/
	private $type;
	
	/**
 	  * @ManyToOne(targetEntity="Kanji", inversedBy="strokes"))
 	  * @JoinColumn(name="id_kanji", referencedColumnName="id")
      */	
	private $kanji; 
	
	
	public function __construct()
    {
        
    }
    
	public function __toString()
    {
        return "$this->position ($this->type) =>  $this->path";
    }

    /**
     * @return int
     */
	public function getIdKanji() {
		return $this->idKanji;
	}

    /**
     * @return int
     */
	public function getPosition() {
		return $this->position;
	}

    /**
     * @return string
     */
	public function getPath() {
		return $this->path;
	}

    /**
     * @return string
     */
	public function getType() {
		return $this->type;
	}

    /**
     * @param $kanji Kanji
     */
	public function setKanji($kanji) {
		$this->idKanji = $kanji->getIdKanji();
		$this->kanji = $kanji;
	}

    /**
     * @param $position int
     */
	public function setPosition($position) {
		$this->position = $position;
	}

    /**
     * @param $path string
     */
	public function setPath($path) {
		$this->path = $path;
	}

    /**
     * @param $type string
     */
	public function setType($type) {
		$this->type = $type;
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


}