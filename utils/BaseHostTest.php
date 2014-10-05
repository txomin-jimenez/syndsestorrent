<?php

/*
    This file is part of SynDsEsTorrent.

    SynDsEsTorrent is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    SynDsEsTorrent is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with SynDsEsTorrent.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace utils;

abstract class BaseHostTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function testGetDownloadInfo();

    protected $host;
    protected $curl;

    protected function setObject($object)
    {
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

    protected function getDownloadInfo()
    {
        $dlInfo = $this->host->GetDownloadInfo();
        if (isset($dlInfo[DOWNLOAD_COOKIE])) {
            curl_setopt($this->curl, CURLOPT_COOKIEFILE, $dlInfo[DOWNLOAD_COOKIE]);
        }
        curl_setopt($this->curl, CURLOPT_URL, $dlInfo[DOWNLOAD_URL]);
        $res = curl_exec($this->curl);
        $info = curl_getinfo($this->curl);
        $this->assertTrue($this->isTorrentFile($res, $info, "El fichero a descargar no es un .torrent"));
    }

    private function isTorrentFile($res, $info)
    {
        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $header = substr($res, 0, $headerSize);
        $filename = $this->regexp('Content-Disposition:.*filename=[\'"]?+(.*)[\'";\n\r]', $header);

        $ret = false;
        if ($filename !== null) {
            if ($this->endsWith($filename[1], '.torrent')) {
                $ret = true;
            }
        } elseif ($info["content_type"] == "application/x-bittorrent") {
            $ret = true;
        }
        
        return $ret;
    }

    private function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    private function regexp($regexp, $texto, $global = false)
    {
        $res = array();
        if ($global) {
            if (!preg_match_all("/$regexp/siU", $texto, $res, PREG_SET_ORDER)) {
                $res = null;
            }
        } else {
            if (!preg_match("/$regexp/siU", $texto, $res)) {
                $res = null;
            }
        }

        return $res;
    }
}
