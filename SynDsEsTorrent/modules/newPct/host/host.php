<?php

class SynoFileHostingNewPct {

    private $Url;
    private $NEWPCT_COOKIE = '/tmp/newpct1.cookie';    

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
    }
    
    /**
     * 
     * @return string URL de la descarga
     * @link http://ukdl.synology.com/download/ds/userguide/Developer_Guide_to_File_Hosting_Module.pdf
     */
    public function GetDownloadInfo() {
        $DownloadInfo[DOWNLOAD_URL] = $this->getTorrentUrl();
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
    }

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
        curl_setopt($curl, CURLOPT_URL, str_replace('/serie/', '/descarga-torrent/serie/',  $this->Url));

        $dlPage = curl_exec($curl);
        $regexp_url = "<a.+href=\"(.*tumejorjuego.*)\"";
        
        $matches_url = array();
        if (preg_match("/$regexp_url/iU", $dlPage, $matches_url)) {
            return $matches_url[1];
        }
        return "";
    }

}

