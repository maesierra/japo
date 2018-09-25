<?php
namespace maesierra\Japo\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="kanji")
 */

class Kanji 
{
    /** @Id @Column(type="bigint", name="id") */
    private $id;

	/** @Column(type="string")*/
	private $kanji;

	
	/**
     * @var KanjiCatalogEntry[]
 	 * @OneToMany(targetEntity="KanjiCatalogEntry", mappedBy="kanji", cascade={"persist"})
     * @OrderBy({"idCatalog" = "ASC"})
     */
	private $catalogs;
	
	/**
      * @var KanjiReading[]
 	  * @OneToMany(targetEntity="KanjiReading", mappedBy="kanji", cascade={"persist"})
      * @OrderBy({"kind" = "ASC", "reading" = "ASC"})
      */	
	private $readings;	
	
	/**
 	  * @OneToMany(targetEntity="KanjiMeaning", mappedBy="kanji", cascade={"persist"})
      * @OrderBy({"meaning" = "ASC"})
      */	
	private $meanings;		
	
	public function __construct() {
        $this->catalogs = new ArrayCollection();
        $this->readings = new ArrayCollection();
        $this->meanings = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->kanji;
    }

	
	public function getKanji()
	{
		return $this->kanji;
	}
		
	public function setKanji($kanji)
	{
		$this->kanji = $kanji;
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
     * @return ArrayCollection
     */
    public function getCatalogs() {
		return $this->catalogs;
	}

    /**
     * @return ArrayCollection
     */
    public function getReadings() {
        return $this->readings;
    }

    /**
     * @return ArrayCollection
     */
    public function getMeanings() {
        return $this->meanings;
    }
}?>