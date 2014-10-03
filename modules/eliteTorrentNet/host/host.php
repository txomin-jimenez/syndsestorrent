<?php

class SynoFileHostingEliteTorrentNet
{
    private $url;
    private $DOWNLOAD_URL = 'http://www.elitetorrent.net/get-torrent/%s';

    public function __construct($Url, $Username, $Password, $HostInfo)
    {
        $this->url = $Url;
    }

    public function Verify($ClearCookie)
    {
        return USER_IS_FREE;
    }

    public function GetDownloadInfo()
    {
        $DownloadInfo = array();

        if (strrpos($this->url, '/get-torrent/') === FALSE) {
            $DownloadInfo[DOWNLOAD_URL] = $this->getTorrentId($this->url);
        } else {
            $DownloadInfo[DOWNLOAD_URL] = $this->url;
        }

        $DownloadInfo[DOWNLOAD_COOKIE] = "/tmp/elitetorrentnet.cookie";

        return $DownloadInfo;
    }

    private function getTorrentId($url)
    {
        $matches = array();
        if (preg_match("/elitetorrent.net\/torrent\/(\d+)/", $url, $matches)) {
            return sprintf($this->DOWNLOAD_URL, $matches[1]);
        } else {
            return "";
        }
    }

}
