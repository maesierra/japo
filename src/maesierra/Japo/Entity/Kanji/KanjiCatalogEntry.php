<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maesierra
 * Date: 10/10/14
 * Time: 20:44
 * To change this template use File | Settings | File Templates.
 */

namespace maesierra\Japo\Entity\Kanji;


/**
 * @Entity @Table(name="kanji_catalogs_rel")
 */

class KanjiCatalogEntry {

    /** @GeneratedValue @Id @Column(type="bigint", name="id") */
    private $id;

    /** @Column(type="bigint", name="id_catalog") */
    private $idCatalog;
    /** @Column(type="bigint", name="id_kanji") */
    private $idKanji;
    /** @Column(type="integer", name="n")*/
    private $n;
    /** @Column(type="integer", name="level")*/
    private $level;

    /**
     * @ManyToOne(targetEntity="KanjiCatalog", inversedBy="KanjiCatalogEntry"))
     * @JoinColumn(name="id_catalog", referencedColumnName="id")
     * @var KanjiCatalog
     */

    private $catalog;
    /**
     * @ManyToOne(targetEntity="Kanji", inversedBy="KanjiCatalogEntry"))
     * @JoinColumn(name="id_kanji", referencedColumnName="id")
     * @var Kanji
     */
    private $kanji;

    public function __construct()
    {
    }

    public function getIdKanji()
    {
        return $this->idKanji;
    }

    public function setIdKanji($idKanji)
    {
        $this->idKanji = $idKanji;
    }

    public function getIdCatalog()
    {
        return $this->idCatalog;
    }

    public function setIdCatalog($idCatalog)
    {
        $this->idCatalog = $idCatalog;
    }
    public function getN()
    {
        return $this->n;
    }

    public function setN($n)
    {
        $this->n = $n;
    }
    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return KanjiCatalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * @param $catalog KanjiCatalog
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
        $this->idCatalog = $catalog->getId();
    }


    /**
     * @param $kanji Kanji
     */
    public function setKanji($kanji)
    {
        $this->idKanji = $kanji->getId();
        $this->kanji = $kanji;
    }

    /**
     * @return Kanji
     */
    public function getKanji()
    {
        return $this->kanji;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


}
