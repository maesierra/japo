<?php
namespace maesierra\Japo\Entity\Kanji;
/**
 * @Entity @Table(name="kanji_catalogs")
 */

class KanjiCatalog 
{
	/** @Id @Column(type="bigint", name="id") */
	private $id;
	/** @Column(type="string", name="name")*/
	private $name;
    /** @Column(type="string", name="slug")*/
    private $slug;

	

	public function __construct() {
    }
    
    public function __toString() {
        return $this->name;
    }
    

	public function getId() {
		return $this->id;
	}
			
	public function setId($id) {
		$this->id = $id;
	}

	public function getName() {
		return $this->name;
	}
			
	public function setName($name) {
		$this->name = $name;
	}

    /**
     * @param string $slug
     */
    public function setSlug($slug) {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }
}?>