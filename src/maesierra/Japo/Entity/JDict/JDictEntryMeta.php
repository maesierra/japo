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
 * @Entity @Table(name="jdict_entry_meta")
 */

class JDictEntryMeta
{
    /** @Id @Column(type="bigint", name="id") */
    private $id;
    /** @Id @Column(type="bigint", name="meta_id")*/
    private $metaId;
    /** @Column(type="string", name="meta")*/
    private $meta;

    /**
     * @var JDictEntry
     * @ManyToOne(targetEntity="JDictEntry", inversedBy="JDictEntryMeta")
     * @JoinColumn(name="id", referencedColumnName="id")
     */
    private $entry;


    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->meta;
    }


    public function getId() { return $this->id; }
    public function getMetaId() { return $this->metaId; }
    public function getMeta() { return $this->meta; }
    public function setId($x) { $this->id = $x; }
    public function setMetaId($x) { $this->metaId = $x; }
    public function setMeta($x) { $this->meta = $x; }
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
