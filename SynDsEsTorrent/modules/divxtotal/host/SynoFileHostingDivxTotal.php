<?php

/*
 * Copyright (C) 2014 Luskaner
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class SynoFileHostingDivxTotal
{

    private $url;

    public function __construct($url, $user_name, $password, $host_info)
    {
        $this->url = $url;
    }

    public function GetDownloadInfo()
    {
        $DownloadInfo[DOWNLOAD_URL] = $this->url;
        $res_torrent = $this->regexp("\/torrent\/(?<id>\d+)\/", $this->url);
        $res_serie = $this->regexp("\/series\/", $this->url);
        if ($res_torrent !== false) {
            $DownloadInfo[DOWNLOAD_URL] = "http://www.divxtotal.com/download.ph"
                    . "p?id={$res_torrent['id']}";
        } else if ($res_serie !== false) {
            $DownloadInfo[DOWNLOAD_URL] = $this->getSerieUrl();
        }

        return $DownloadInfo;
    }

    private function getSerieUrl()
    {
        
        $res_url = $this->regexp('(?<url>.*)\?cap=(?<nombre>.*?)', $this->url);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $res_url['url']);
        
        $dl_page = curl_exec($curl);
        $nombre = rawurldecode($res_url['nombre']);
        $regexp_url = '<a href="(?<url>\/torrents_tor\/.*\.torrent)".*>' . $nombre . '<\/a>';
        $res_true_url = $this->regexp($regexp_url, $dl_page, false, 'i');
        if ($res_true_url !== false) {
            return 'http://www.divxtotal.com' . $res_true_url['url'];
        }

        return "";
    }

    private function regexp($regexp, $texto, $global = false, $flags = 'siUu')
    {
        $res = array();
        if ($global) {
            if (preg_match_all("/$regexp/$flags", $texto, $res, PREG_SET_ORDER)) {
                return $res;
            } else {
                return false;
            }
        } else {
            if (preg_match("/$regexp/$flags", $texto, $res)) {
                return $res;
            } else {
                return false;
            }
        }
    }
}
