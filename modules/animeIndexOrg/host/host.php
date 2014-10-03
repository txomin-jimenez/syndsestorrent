<?php

class SynoFileHostingAnimeIndex
{
    private $Url;
    private $ANIME_INDEX_URL = 'http://tracker.anime-index.org/';

    public function __construct($Url, $Username, $Password, $HostInfo)
    {
        $this->Url = $Url;
    }

    /**
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function GetDownloadInfo()
    {
        // @codingStandardsIgnoreEnd
        $DownloadInfo = array();

        if (strrpos($this->Url, "download.php") === false) {
            $DownloadInfo[DOWNLOAD_URL] = $this->getTorrentUrl();
        } else {
           $DownloadInfo[DOWNLOAD_URL] = $this->Url;
        }

        return $DownloadInfo;
    }

    public function Verify($ClearCookie)
    {
        return USER_IS_FREE;
    }

    private function getTorrentUrl()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_REFERER, $this->ANIME_INDEX_URL);
        curl_setopt($curl, CURLOPT_URL, $this->Url);

        $dlPage = curl_exec($curl);

        $regexp_title = '<td align="right" class="header">Torrent(.*)<td class="lista" align="center" style="text-align:left;"><a href="(.*)"';

        $matches_title = array();
        if (preg_match_all("/$regexp_title/siU", $dlPage, $matches_title, PREG_SET_ORDER)) {
            $relative_url = $matches_title[0][2];

            return $this->ANIME_INDEX_URL . html_entity_decode($relative_url);
        }

        return "";
    }

}
