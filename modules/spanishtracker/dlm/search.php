<?php

class SynoDLMSearchSpanishTracker
{
    private $url = 'http://www.shieldbypass.com/browse.php?u=%s&b=28&f=norefer';
    private $durl = 'http://spanishtracker.com/details.php?id=%s';
    private $qurl = 'http://spanishtracker.com/torrents.php?search=%s&category=0&active=1';
    private $purl = 'http://spanishtracker.com/';
    private $cookie = '/tmp/spanishtracker.cookie';

    public function __construct()
    {
    }

    /**
     *
     * @param curl   $curl  objeto curl
     * @param string $query cadena a buscar
     */
    public function prepare($curl, $query)
    {
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_REFERER, $this->purl);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_URL, 'http://www.shieldbypass.com');

        curl_exec($curl);

        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($curl, CURLOPT_COOKIE, "language=es_ES");
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_REFERER, $this->purl);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->url, urlencode(sprintf($this->qurl, iconv('ISO-8859-1', 'UTF-8', $query)))));
    }

    /**
     *
     * @param  plugin $plugin   contendrá los resultados ya extraídos de la página
     * @param  string $response respuesta html de la página
     * @return int    número de resultados
     */
    public function parse($plugin, $response)
    {
        // Definimos las cadenas REGEXP hasta llegar a los torrent
        // Definimos las cadenas REGEXP hasta llegar a los torrent
        $regexp_tabla = "<table.*class=\"lista\".*>(.*)<\/table>";
        $regexp_fila = "<tr.*>(.*)<\/tr>";
        $res = 0;

        if ($res_tabla = $this->regexp($regexp_tabla, $response, true)) {
            if ($res_filas = $this->regexp($regexp_fila, $res_tabla[3][1], true)) {
                $res = $this->procesarFilas($res_filas, $plugin);
            }
        }

        return $res;
    }

    private function procesarFilas($filas, $plugin)
    {
        $res = 0;

        for ($i = 2; isset($filas[$i][1]); $i++) {
            $info = $this->procesarMultiple($filas[$i][1]);
            $plugin->addResult($info['titulo'], $info['urlDescarga'], $info['tamano'], $info['fecha'], $info['urlPagina'], $info['hash'], $info['semillas'], $info['clientes'], 'Sin clasificar');
            $res++;
        }

        return $res;
    }

    private function procesarMultiple($fila)
    {
        $resInfo = $this->regexp("<td.*><a.*href=.*id%3D(?P<id>.*)%.*f%3D(?<nombre>.*)\.torrent.*>.*<\/a>\s*<\/td>.*<td.*>(?P<dia>\d+)\/(?P<mes>\d+)\/(?P<ano>\d+)<\/td>\s*<td.*>(?<tamano>\d+\.\d*)\s(?<medida_tamano>[KMG]B)<\/td>\s*<td.*>(?P<semillas>\d+)<\/td>\s*<td.*>(?P<clientes>\d+)<\/td>", $fila);
        $tamano = $resInfo['tamano'];

        switch ($resInfo['medida_tamano']) {
            case 'KB':
                $tamano *= 1024;
                break;
            case 'MB':
                $tamano *= 1024 * 1024;
                break;
            case 'GB':
                $tamano *= 1024 * 1024 * 1024;
        }

        $info = array(
            'urlDescarga'   => "magnet:?xt=urn:btih:{$resInfo['id']}&dn=" . urldecode($resInfo['nombre']) . '&tr=udp%3a//tracker.openbittorrent.com:80el29&tr=udp%3a//tracker.publicbt.com:80el39&tr=http%3a//www.spanishtracker.com:2710/announceel45&tr=http%3a//tracker.openbittorrent.com:80/announceel35',
            'urlPagina'     => sprintf($this->url, urlencode(sprintf($this->durl, $resInfo['id']))),
            'titulo'        => urldecode(urldecode($resInfo['nombre'])),
            'tamano'        => $tamano,
            'fecha'         => "{$resInfo['ano']}-{$resInfo['mes']}-{$resInfo['dia']}",
            'hash'          => $resInfo['id'],
            'semillas'      => $resInfo['semillas'],
            'clientes'      => $resInfo['clientes'],

        );

        return $info;
    }

    private function regexp($regexp, $texto, $global = false)
    {
        $res = array();
        if ($global) {
            if (preg_match_all("/$regexp/siU", $texto, $res, PREG_SET_ORDER)) {
                return $res;
            } else {
                return '';
            }
        } else {
            if (preg_match("/$regexp/siU", $texto, $res)) {
                return $res;
            } else {
                return '';
            }
        }
    }

}
