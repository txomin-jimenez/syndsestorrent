<?php

require_once dirname(__FILE__) . '/../../../../modulos/eliteTorrentNet/dlm/search.php';
require_once dirname(__FILE__) . '/../../../../utils/plugin.php';

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-06-16 at 03:05:14.
 */
class SynoDLMSearchEliteTorrentNetTest extends PHPUnit_Framework_TestCase {

    /**
     * @var SynoDLMSearchEliteTorrentNet
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new SynoDLMSearchEliteTorrentNet;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }


    /**
     * @covers SynoDLMSearchEliteTorrentNet::parse
     * @todo   Implement testParse().
     */
    public function testParse() {
        $plugin = new plugin();
        $curl = curl_init();
        $query = "hola";
        $this->object->prepare($curl, $query);
        $data = curl_exec($curl);
        $this->assertGreaterThan(0, $this->object->parse($plugin, $data), "No hay resultados");
        var_dump($plugin->results);
    }

}
