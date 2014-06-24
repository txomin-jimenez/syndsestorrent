<?php

class SynoFileHostingNewPct {

    private $Url;
    // Ya no se requiere para NewPct1.com
    /*private $Username;
    private $Password;
    private $HostInfo;
    private $NEWPCT_LOGIN_URL = 'http://www.newpct1.com/doLoginFx/';*/
    private $NEWPCT_COOKIE = '/tmp/newpct1.cookie';    
    private $NEWPCT_DOWNLOAD_URL = 'http://www.tumejorjuego.com/descargar/index.php?link=descargar/torrent/%s/%s.html';

    /**
     * 
     * @param string $Url URL a descargar (no el fichero directo)
     * @param string $Username Usuario
     * @param string $Password Contraseña
     * @param array $HostInfo Información del host
     * @link http://ukdl.synology.com/download/ds/userguide/Developer_Guide_to_File_Hosting_Module.pdf
     */
    public function __construct($Url, $Username, $Password, $HostInfo) {
        $this->Url = $Url;
        // Ya no se requiere para NewPct1.com
        /* $this->Username = $Username;
          $this->Password = $Password;
          $this->HostInfo = $HostInfo; */
    }
    
    /**
     * 
     * @return string URL de la descarga
     * @link http://ukdl.synology.com/download/ds/userguide/Developer_Guide_to_File_Hosting_Module.pdf
     */
    public function GetDownloadInfo() {
        // Ya no se requiere para NewPct1.com
        /* $ret = FALSE;
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
          return $ret; */
        if (strrpos($this->Url, '/torrent/') === FALSE) {
            $DownloadInfo[DOWNLOAD_URL] = $this->getTorrentUrl();
        } else {
            $DownloadInfo[DOWNLOAD_URL] = trim($this->Url);
        }
        $DownloadInfo[DOWNLOAD_COOKIE] = $this->NEWPCT_COOKIE;
        $ret = $DownloadInfo;
        return $ret;
    }
    
    /**
     * 
     * @param type $ClearCookie Si se elimina la cookie (no se utiliza)
     * @return string tipo de usuario
     */
    public function Verify($ClearCookie) {
        return USER_IS_FREE;
        // Ya no se requiere para NewPct1.com
        /*$ret = LOGIN_FAIL;

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
        return $ret;*/
    }

    // No hace falta logearse para descargar de NewPct1.com
    /*private function newPctLogin() {
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
    }*/

    /**
     * 
     * @return string Devuelve la url dependiendo si proviene del RSS
     * o de la búsqueda
     */
    private function getTorrentUrl() {
        if (strstr($this->Url, "newpct.com") !== FALSE) {
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
        $regexp_url = "<a.+href='(.*tumejorjuego.*)'";
        
        $matches_url = array();
        if (preg_match("/$regexp_url/iU", $dlPage, $matches_url)) {
            return $matches_url[1];
        }
        return "";
    }

}

?>