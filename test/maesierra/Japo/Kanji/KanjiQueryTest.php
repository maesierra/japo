<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 15/09/18
 * Time: 22:50
 */

namespace maesierra\Japo\Kanji;


use maesierra\Japo\Common\Query\Sort;

class KanjiQueryTest extends \PHPUnit_Framework_TestCase {


    public function testDefaultValues() {
        $query = new KanjiQuery();
        $this->assertEquals(null, $query->catalogId);
        $this->assertEquals(null, $query->catalog);
        $this->assertEquals(null, $query->level);
        $this->assertEquals(null, $query->reading);
        $this->assertEquals(null, $query->meaning);
        $this->assertEquals(null, $query->sort);
        $this->assertEquals(null, $query->page);
        $this->assertEquals(null, $query->pageSize);
        $this->assertFalse($query->kunOnly);
        $this->assertFalse($query->onOnly);
    }

    public function testSetValues() {
        $query = new KanjiQuery([
            'catalogId' => 1,
            'catalog' => 2,
            'level' => 3,
            'reading' => 'aa',
            'meaning' => 'bb',
            'kunOnly' => true,
            'onOnly' => 'true',
            'sort' => 'catalogId',
            'order' => 'desc',
            'page' => 6,
            'pageSize' => 55,
            'extra' => 'ignored'
        ]);
        $this->assertEquals(1, $query->catalogId);
        $this->assertEquals(2, $query->catalog);
        $this->assertEquals(3, $query->level);
        $this->assertEquals('aa', $query->reading);
        $this->assertEquals('bb', $query->meaning);
        $this->assertEquals(new Sort('catalogId', Sort::SORT_DESC), $query->sort);
        $this->assertEquals(6, $query->page);
        $this->assertEquals(55, $query->pageSize);
        $this->assertTrue($query->kunOnly);
        $this->assertTrue($query->onOnly);
        $this->assertFalse(isset($query->extra));
    }

    public function testInvalidSortDirection() {
        $query = new KanjiQuery([
            'sort' => 'catalogId',
            'order' => 'aa'
        ]);
        $this->assertEquals(new Sort('catalogId', Sort::SORT_ASC), $query->sort);
    }

    public function testSortDirectionWithNoSortIgnored() {
        $query = new KanjiQuery([
            'order' => 'desc'
        ]);
        $this->assertNull($query->sort);
    }

    public function testKunOnlyOnOnly() {
        $query = new KanjiQuery([
            'kunOnly' => '',
            'onOnly' => ''
        ]);
        $this->assertEquals(false, $query->kunOnly);
        $this->assertEquals(false, $query->onOnly);
    }
}
