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

namespace modules\divxtotal\host;

class SynoFileHostingDivxTotal
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function GetDownloadInfo()
    {
        $downloadInfo[DOWNLOAD_URL] = $this->url;
        $resTorrent = $this->regexp("\/torrent\/(?<id>\d+)\/", $this->url);
        $resSerie = $this->regexp("\/series\/", $this->url);

        if ($resTorrent !== false) {
            $downloadInfo[DOWNLOAD_URL] = "http://www.divxtotal.com/download.ph"
                    . "p?id={$resTorrent['id']}";
        } elseif ($resSerie !== false) {
            $downloadInfo[DOWNLOAD_URL] = $this->getSerieUrl();
        }

        return $downloadInfo;
    }

    private function getSerieUrl()
    {
        $resUrl = $this->regexp('(?<url>.*)\?cap=(?<nombre>.*?)', $this->url);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $resUrl['url']);

        $dlPage = curl_exec($curl);
        $nombre = rawurldecode($resUrl['nombre']);
        $regexpUrl = '<a href="(?<url>\/torrents_tor\/.*\.torrent)".*>' . $nombre . '<\/a>';
        $resTrueUrl = $this->regexp($regexpUrl, $dlPage, false, 'i');
        $ret = '';
        if ($resTrueUrl !== false) {
            $ret = 'http://www.divxtotal.com' . $resTrueUrl['url'];
        }

        return $ret;
    }

    private function regexp($regexp, $texto, $global = false, $flags = 'siUu')
    {
        $res = array();
        $ret = false;
        if ($global) {
            if (preg_match_all("/$regexp/$flags", $texto, $res, PREG_SET_ORDER)) {
                $ret = $res;
            }
        } else {
            if (preg_match("/$regexp/$flags", $texto, $res)) {
                $ret = $res;
            }
        }

        return $ret;
    }
}
