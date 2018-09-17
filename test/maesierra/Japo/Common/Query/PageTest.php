<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 14/09/18
 * Time: 21:14
 */

namespace maesierra\Japo\Common\Query;


class PageTest extends \PHPUnit_Framework_TestCase
{

    public function testGetNPages() {
        $page = new Page(1, 20);
        $this->assertEquals(0, $page->getNPages());
        $page = new Page(0, 20, 100);
        $this->assertEquals(5, $page->getNPages());
        $page = new Page(0, 20, 99);
        $this->assertEquals(5, $page->getNPages());
        $page = new Page(0, 20, 101);
        $this->assertEquals(6, $page->getNPages());
        $page = new Page(0, 20, 120);
        $this->assertEquals(6, $page->getNPages());
    }

    public function testHasMore()
    {
        $page = new Page(0, 20, 120);
        $this->assertTrue($page->hasMore());
        $page = new Page(6, 20, 120);
        $this->assertFalse($page->hasMore());
    }

    public function testGetOffset()
    {
        $page = new Page(0, 20, 120);
        $this->assertEquals(0, $page->getOffset());
        $page = new Page(1, 20, 120);
        $this->assertEquals(20, $page->getOffset());
    }
}
