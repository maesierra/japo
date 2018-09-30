<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 20:40
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity\JDict;


/**
 * @Entity @Table(name="jdict_entry_gloss")
 */

class JDictEntryGloss
{
    /** @Id @Column(type="bigint", name="id") */
    private $id;
    /** @Id @Column(type="bigint", name="gloss_id")*/
    private $glossId;
    /** @Column(type="string", name="gloss")*/
    private $gloss;

    /**
     * @var JDictEntry
     * @ManyToOne(targetEntity="JDictEntry", inversedBy="JDictEntryGloss")
     * @JoinColumn(name="id", referencedColumnName="id")
     */
    private $entry;


    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->gloss;
    }


    public function getId() { return $this->id; }
    public function getGlossId() { return $this->glossId; }
    public function getGloss() { return $this->gloss; }
    public function setId($x) { $this->id = $x; }
    public function setGlossId($x) { $this->glossId = $x; }
    public function setGloss($x) { $this->gloss = $x; }
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param $entry JDictEntry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
        $this->id = $entry->getId();
    }

}
