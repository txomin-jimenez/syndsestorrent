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

namespace modules\newPct\host;

class SynoFileHostingNewPct
{
    private $url;

    const QUERY_URL = 'https://de-free-proxy.cyberghostvpn.com/go/browse.php?u=%s&b=7&f=norefer';
    const HOST_PROXY = 'https://de-free-proxy.cyberghostvpn.com';

    /**
     *
     * @param string $Url      URL a descargar (no el fichero directo)
     * @param string $Username Usuario
     * @param string $Password Contraseña
     * @param array  $HostInfo Información del host
     * @link http://ukdl.synology.com/download/ds/userguide/Developer_Guide_to_File_Hosting_Module.pdf
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     *
     * @return string URL de la descarga
     * @link http://ukdl.synology.com/download/ds/userguide/Developer_Guide_to_File_Hosting_Module.pdf
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function GetDownloadInfo()
    {
        $this->logToFile("getDownloadInfo");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result[DOWNLOAD_URL] = $this->getTorrentUrlNewpct($curl);

        return $result;
    }

    private function logToFile($log){
      file_put_contents('/tmp/zzlog_'.date("j.n.Y").'.txt', $log.PHP_EOL, FILE_APPEND);
    }

    private function getTorrentUrlNewpct($curl)
    {
        $this->logToFile("................");
        $this->logToFile($this->url);
        $ret = '';
        if (strstr($this->url, 'newpct1.com') === false) {
            //curl_setopt($curl, CURLOPT_URL, sprintf(SynoFileHostingNewPct::QUERY_URL, urlencode($this->url)));
            curl_setopt($curl, CURLOPT_URL, $this->url);
            $res = curl_exec($curl);
            $this->logToFile("1");
            $this->logToFile($res);
            $resultadoRegex = array();
            if (preg_match(
                '/<span.*id=\'content-torrent\'.*>.*<a.*href=\'(.*tumejor.*)\'/siU',
                $res,
                $resultadoRegex
            )
            ) {
                $this->logToFile("2");
                $this->logToFile($resultadoRegex[1]);
                //$ret = SynoFileHostingNewPct::HOST_PROXY . $resultadoRegex[1];
                $ret = $resultadoRegex[1];
            }
        }

        $this->logToFile("3");
        $this->logToFile($ret);

        return $ret;
    }
}
