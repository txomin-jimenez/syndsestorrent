<?php

namespace utils;

abstract class BaseDlmTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function testParse();
    protected $search;

    protected function setObject($object)
    {
        $this->search = $object;
    }

    protected function parse()
    {
        $plugin = new Plugin();
        $curl = curl_init();
        $this->search->prepare($curl, 'a');
        $data = curl_exec($curl);
        $this->assertGreaterThan(0, $this->search->parse($plugin, $data), "No hay resultados");
    }
}
