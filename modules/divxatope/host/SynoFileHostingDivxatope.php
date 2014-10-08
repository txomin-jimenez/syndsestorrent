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

namespace modules\divxatope\host;

class SynoFileHostingDivxatope
{

    private $url;

    const COOKIE = '/tmp/divxatope.cookie';

    /**
     * @param string $url URL a descargar (no el fichero directo)
     * @link http://ukdl.synology.com/download/ds/userguide/Developer_Guide_to_File_Hosting_Module.pdf
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @return string URL de la descarga
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function GetDownloadInfo()
    {
        $downloadInfo[DOWNLOAD_URL] = $this->getTorrentUrl();
        $downloadInfo[DOWNLOAD_COOKIE] = SynoFileHostingDivxatope::COOKIE;
        $ret = $downloadInfo;

        return $ret;
    }

    /**
     *
     * @return string Devuelve la url
     */
    private function getTorrentUrl()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->url);

        $dlPage = curl_exec($curl);

        $ret = '';
        $resultadoPreUrl = [];
        if (preg_match(
            '/<a.*href="(http:\/\/(www.)?divxatope.com\/descarga-torrent\/.*)".*>Descarga Torrent<\/a>/siU',
            $dlPage,
            $resultadoPreUrl
        )) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $resultadoPreUrl[1]);

            $dlPageTorrent = curl_exec($curl);
            $regexpUrl = "(http:\/\/tumejorserie.*)\"";
            $matchesUrl = array();

            if (preg_match("/$regexpUrl/iU", $dlPageTorrent, $matchesUrl)) {
                $ret = $matchesUrl[1];
            }
        }

        return $ret;
    }
}
