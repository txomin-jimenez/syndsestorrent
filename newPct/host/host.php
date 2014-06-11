<?php

class SynoFileHostingNewPct {

    private $Url;
    private $Username;
    private $Password;
    private $HostInfo;
    private $NEWPCT_COOKIE = '/tmp/newpct1.cookie';
    private $NEWPCT_LOGIN_URL = 'http://www.newpct1.com/doLoginFx/';
    private $NEWPCT_DOWNLOAD_URL = 'http://www.tumejorjuego.com/descargar/index.php?link=descargar/torrent/%s/%s.html';

    public function __construct($Url, $Username, $Password, $HostInfo) {
        $this->Url = $Url;
        $this->Username = $Username;
        $this->Password = $Password;
        $this->HostInfo = $HostInfo;
    }

    public function GetDownloadInfo() {
        $ret = FALSE;
        $VerifyRet = $this->Verify(FALSE);
        if ($VerifyRet) {
            $DownloadInfo = array();

            // We check if the download link is for RSS
            if (strrpos($this->Url, '/torrent/') === FALSE) {
                $DownloadInfo[DOWNLOAD_URL] = $this->getTorrentUrl();
            } else {
                $DownloadInfo[DOWNLOAD_URL] = trim($this->Url);
            }

            $DownloadInfo[DOWNLOAD_COOKIE] = $this->NEWPCT_COOKIE;
            $ret = $DownloadInfo;
        }
        return $ret;
    }

    public function Verify($ClearCookie) {
        $ret = LOGIN_FAIL;

        $this->CookieValue = FALSE;
        if (!empty($this->Username) && !empty($this->Password)) {
            $this->CookieValue = $this->newPctLogin();
        }

        if (FALSE != $this->CookieValue) {
            $ret = USER_IS_FREE;
        }

        if ($ClearCookie && file_exists($this->NEWPCT_COOKIE)) {
            unlink($this->NEWPCT_COOKIE);
        }
        return $ret;
    }

    private function newPctLogin() {
        $ret = FALSE;
        //Save cookie file
        $PostData = array(
            'userName' => $this->Username,
            'userPass' => $this->Password);
        $PostData = http_build_query($PostData);

        $queryUrl = $this->NEWPCT_LOGIN_URL;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $PostData);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->NEWPCT_COOKIE);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $queryUrl);

        $LoginInfo = curl_exec($curl);

        curl_close($curl);
        if (FALSE != $LoginInfo && file_exists($this->NEWPCT_COOKIE)) {
            $ret = parse_cookiefile($this->NEWPCT_COOKIE);
            if (!empty($ret['newpctInfo'])) {
                $ret = $ret['newpctInfo'];
            } else {
                $ret = FALSE;
            }
        }
        return $ret;
    }

    private function getTorrentUrl() {
        if (strstr($this->url, "newpct.com") !== FALSE){
            return "";
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_URL, $this->Url);
            
        $dlPage = curl_exec($curl);

        $regexp_title = '<meta property="og:image" content="(.*).jpg"';
        $regexp_id = "'torrentID': '(.*)'";

        if (preg_match_all("/$regexp_title/siU", $dlPage, $matches_title, PREG_SET_ORDER)) {
            $title = substr($matches_title[0][1], strrpos($matches_title[0][1], '/') + 1);
            if (preg_match_all("/$regexp_id/", $dlPage, $matches_id, PREG_SET_ORDER)) {
                $id = $matches_id[0][1];
                return sprintf($this->NEWPCT_DOWNLOAD_URL, $id, $title);
            }
        }
        return "";
    }

}

?>