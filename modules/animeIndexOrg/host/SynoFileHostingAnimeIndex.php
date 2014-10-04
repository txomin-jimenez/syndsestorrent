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

namespace modules\animeIndexOrg\host;

class SynoFileHostingAnimeIndex
{

    private $url;

    const WEB_URL = 'http://tracker.anime-index.org/';

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function GetDownloadInfo()
    {
        $downloadInfo = [];

        if (strrpos($this->url, "download.php") === false) {
            $downloadInfo[DOWNLOAD_URL] = $this->getTorrentUrl();
        } else {
            $downloadInfo[DOWNLOAD_URL] = $this->url;
        }

        return $downloadInfo;
    }

    private function getTorrentUrl()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_REFERER, SynoFileHostingAnimeIndex::WEB_URL);
        curl_setopt($curl, CURLOPT_URL, $this->url);

        $dlPage = curl_exec($curl);

        $regexpTitle = '<td align="right" class="header">Torrent(.*)<td class="'
                . 'lista" align="center" style="text-align:left;"><a href="(.*)"';

        $matchesTitle = array();
        $ret = '';
        if (preg_match_all("/$regexpTitle/siU", $dlPage, $matchesTitle, PREG_SET_ORDER)) {
            $relativeUrl = $matchesTitle[0][2];
            $ret = SynoFileHostingAnimeIndex::WEB_URL . html_entity_decode($relativeUrl);
        }

        return $ret;
    }
}
