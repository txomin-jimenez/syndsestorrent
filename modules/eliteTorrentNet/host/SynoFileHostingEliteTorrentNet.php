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

namespace modules\eliteTorrentNet\host;

class SynoFileHostingEliteTorrentNet
{
    private $url;
    const WEB_URL = 'http://www.elitetorrent.net/get-torrent/%s';

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function GetDownloadInfo()
    {
        $downloadInfo = array();

        if (strrpos($this->url, '/get-torrent/') === false) {
            $downloadInfo[DOWNLOAD_URL] = $this->getTorrentId($this->url);
        } else {
            $downloadInfo[DOWNLOAD_URL] = $this->url;
        }

        $downloadInfo[DOWNLOAD_COOKIE] = "/tmp/elitetorrentnet.cookie";

        return $downloadInfo;
    }

    private function getTorrentId($url)
    {
        $matches = array();
        $ret = '';
        if (preg_match("/elitetorrent.net\/torrent\/(\d+)/", $url, $matches)) {
            $ret = sprintf(SynoFileHostingEliteTorrentNet::WEB_URL, $matches[1]);
        }

        return $ret;
    }
}
