<?php

namespace SynDsEsTorrent\utils;

require_once 'common.php';

abstract class BaseHostTest extends \PHPUnit_Framework_TestCase {

    abstract protected function testGetDownloadInfo();

    protected $host;
    protected $curl;

    protected function setObject($object) {
        $this->host = $object;
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        curl_setopt($this->curl, CURLOPT_COOKIE, "language=es_ES");
        curl_setopt($this->curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
    }

    protected function GetDownloadInfo() {
        $dlInfo = $this->host->GetDownloadInfo();
        if (isset($dlInfo[DOWNLOAD_COOKIE])){
           curl_setopt($this->curl, CURLOPT_COOKIEFILE, $dlInfo[DOWNLOAD_COOKIE]); 
        }        
        curl_setopt($this->curl, CURLOPT_URL, $dlInfo[DOWNLOAD_URL]);
        $res = curl_exec($this->curl);
        $info = curl_getinfo($this->curl);
        $this->assertTrue($this->isTorrentFile($res, $info, "El fichero a descargar no es un .torrent"));
    }

    private function isTorrentFile($res, $info) {
        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $header = substr($res, 0, $header_size);
        $filename = $this->regexp('Content-Disposition:.*filename=[\'"]?+(.*)[\'";\n\r]', $header);
        
        if ($filename !== '') {
            if ($this->endsWith($filename[1], '.torrent')){
                return true;
            }
        }
        if ($info["content_type"] == "application/x-bittorrent"){
            return true;
        } else {
            return false;
        }
    }

    private function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    private function regexp($regexp, $texto, $global = false) {
        $res = array();
        if ($global) {
            if (preg_match_all("/$regexp/siU", $texto, $res, PREG_SET_ORDER)) {
                return $res;
            } else {
                return '';
            }
        } else {
            if (preg_match("/$regexp/siU", $texto, $res)) {
                return $res;
            } else {
                return '';
            }
        }
    }

}
