<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 18/09/2018
 * Time: 0:52
 */

namespace maesierra\Japo\UTF8;

use PHPUnit\Framework\TestCase;


class UTF8UtilsTest extends TestCase {


    public function testOrd() {
        $this->assertEquals(0x0061, UTF8Utils::ord('a'));
        $this->assertEquals(0x00E1, UTF8Utils::ord('á'));
        $this->assertEquals(0x304B, UTF8Utils::ord('か'));
        $this->assertEquals(0x6F22, UTF8Utils::ord('漢'));
        $this->assertEquals(0x1F985, UTF8Utils::ord('🦅'));
    }

    public function testChr() {
        $this->assertEquals('a', UTF8Utils::chr(0x0061));
        $this->assertEquals('á', UTF8Utils::chr(0x00E1));
        $this->assertEquals('か', UTF8Utils::chr(0x304B));
        $this->assertEquals('漢', UTF8Utils::chr(0x6F22));
        $this->assertEquals('🦅', UTF8Utils::chr(0x1F985));
    }
    public function testToCharArray() {
        $this->assertEquals(['a','á','か',' ','漢','🦅'], UTF8Utils::toCharArray('aáか 漢🦅'));
        $this->assertEquals([], UTF8Utils::toCharArray(''));
    }
}
