<?php

namespace maesierra\Japo\Common\Http;

use PHPUnit\Framework\TestCase;

class HttpHelperTest extends TestCase {


    public function testGetHost() {
        $instance = new HttpHelper(['HTTP_HOST' => "192.168.0.10"]);
        $this->assertEquals("192.168.0.10", $instance->getHost());
    }

    public function testGetHost_defaultValue() {
        $instance = new HttpHelper([]);
        $this->assertEquals("localhost", $instance->getHost());
    }

    public function testIsHttps() {
        $instance = new HttpHelper(['HTTPS' => 'on']);
        $this->assertTrue($instance->isHttps());
    }

    public function testIsHttpsOff() {
        $instance = new HttpHelper(['HTTPS' => 'off']);
        $this->assertFalse($instance->isHttps());
    }

    public function testIsHttps_defaultValue() {
        $instance = new HttpHelper([]);
        $this->assertFalse($instance->isHttps());
    }
}
