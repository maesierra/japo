<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 20:42
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity\JDict;


/**
 * @Entity @Table(name="jdict_entry_reading")
 */

class JDictEntryReading
{
    /** @Id @Column(type="bigint", name="id") */
    private $id;
    /** @Id @Column(type="bigint", name="reading_id")*/
    private $readingId;
    /** @Column(type="string", name="reading")*/
    private $reading;

    /**
     * @ManyToOne(targetEntity="JDictEntry", inversedBy="JDictEntryReading")
     * @JoinColumn(name="id", referencedColumnName="id")
     */
    private $entry;


    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->reading;
    }

    public function getId() { return $this->id; }
    public function getReadingId() { return $this->readingId; }
    public function getReading() { return $this->reading; }
    public function setId($x) { $this->id = $x; }
    public function setReadingId($x) { $this->readingId = $x; }
    public function setReading($x) { $this->reading = $x; }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry($entry)
    {
        $this->entry = $entry;
        $this->id = $entry->getId();
    }

}