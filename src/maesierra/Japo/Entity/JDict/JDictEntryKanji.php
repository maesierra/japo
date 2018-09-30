<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity\JDict;


/**
 * @Entity @Table(name="jdict_entry_kanji")
 */

class JDictEntryKanji
{
    /** @Id @Column(type="bigint", name="id") */
    private $id;
    /** @Id @Column(type="bigint", name="kanji_id")*/
    private $kanjiId;
    /** @Column(type="string", name="kanji")*/
    private $kanji;
    /** @Column(type="boolean", name="common")*/
    private $common = false;

    /**
     * @var JDictEntry
     * @ManyToOne(targetEntity="JDictEntry", inversedBy="JDictEntryKanji")
     * @JoinColumn(name="id", referencedColumnName="id")
     */
    private $entry;


    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->kanji;
    }

    public function getId() { return $this->id; }
    public function getKanjiId() { return $this->kanjiId; }
    public function getKanji() { return $this->kanji; }
    public function setId($x) { $this->id = $x; }
    public function setKanjiId($x) { $this->kanjiId = $x; }
    public function setKanji($x) { $this->kanji = $x; }


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

    /**
     * @param boolean $common
     */
    public function setCommon($common)
    {
        $this->common = $common;
    }

    /**
     * @return boolean
     */
    public function getCommon()
    {
        return $this->common;
    }



}