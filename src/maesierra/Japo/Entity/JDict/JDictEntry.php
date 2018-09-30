<?php
namespace maesierra\Japo\Entity\JDict;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity @Table(name="jdict_entry")
 */
class JDictEntry
{
	/** @Id @Column(type="bigint", name="id") */
	private $id;
	/**
      * @var JDictEntryGloss[]
 	  * @OneToMany(targetEntity="JDictEntryGloss", mappedBy="entry", cascade={"persist", "remove"})
      * @OrderBy({"glossId" = "ASC"})
      */	
	private $gloss;
	/**
      * @var JDictEntryKanji[]
 	  * @OneToMany(targetEntity="JDictEntryKanji", mappedBy="entry", cascade={"persist", "remove"})
      * @OrderBy({"common" = "DESC", "kanjiId" = "ASC"})
      */	
	private $kanji;
	/**
      * @var JDictEntryReading[]
 	  * @OneToMany(targetEntity="JDictEntryReading", mappedBy="entry", cascade={"persist", "remove"})
      * @OrderBy({"readingId" = "ASC"})
      */	
	private $readings;

    /**
     * @var JDictEntryMeta[]
     * @OneToMany(targetEntity="JDictEntryMeta", mappedBy="entry", cascade={"persist", "remove"})
     * @OrderBy({"metaId" = "ASC"})
     */
    private $meta;
	

	public function __construct()
    {
        $this->gloss = new ArrayCollection();
        $this->kanji = new ArrayCollection();
        $this->readings = new ArrayCollection();
        $this->meta = new ArrayCollection();
    }
    public function __toString()
    {
    	if ($this->kanji->count() > 0)
    	{
    		return (string)$this->kanji->first();
    	}
    	else
    	{
    		return (string)$this->readings->first();	
    	}        
    }
	
	public function getId() 
	{
		return $this->id;
	}
	public function setId($id) 
	{
		$this->id = $id;
	}

    /**
     * @return JDictEntryGloss[]
     */
    public function getGloss()
    {
        return $this->gloss;
    }

    /**
     * @return JDictEntryKanji[]
     */
    public function getKanji()
    {
        return $this->kanji;
    }

    /**
     * @return JDictEntryReading[]
     */
    public function getReadings()
    {
        return $this->readings;
    }

    /**
     * @return JDictEntryMeta[]
     */
    public function getMeta()
    {
        return $this->meta;
    }
	

}
?>