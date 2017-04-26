<?php

use ForsakenThreads\Diplomatic\Support\BasicFilters;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase {

    public function testJsonFilter()
    {
        $this->assertNull(BasicFilters::json(null));
        $this->assertFalse(BasicFilters::json(false));
        $this->assertTrue(BasicFilters::json(true));
        $this->assertArrayHasKey('test', BasicFilters::json('{"test":"123"}', true));
        $this->assertObjectHasAttribute('test', BasicFilters::json('{"test":"123"}'));
        $this->assertEquals(['associative' => 'array'], BasicFilters::json(['associative' => 'array']));
    }

    public function testXmlFilter()
    {
        $this->assertNull(BasicFilters::simpleXml(null));
        $this->assertFalse(BasicFilters::simpleXml(false));
        $this->assertTrue(BasicFilters::simpleXml(true));
        $xml = <<<EOH
<?xml version="1.0" ?>
<testing>
    <abc>Hello</abc>
    <xyz>world</xyz>
</testing>
EOH;
        $this->assertEquals(new SimpleXMLElement($xml), BasicFilters::simpleXml($xml));
    }
}