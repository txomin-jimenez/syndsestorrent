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

namespace modules\spanishtracker\dlm;

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
        curl_setopt(
            $curl,
            CURLOPT_URL,
            sprintf($this->url, urlencode(sprintf($this->qurl, iconv('ISO-8859-1', 'UTF-8', $query))))
        );
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
        $regexpTabla = "<table.*class=\"lista\".*>(.*)<\/table>";
        $regexpFila = "<tr.*>(.*)<\/tr>";
        $res = 0;

        if ($resTabla = $this->regexp($regexpTabla, $response, true)) {
            if ($resFilas = $this->regexp($regexpFila, $resTabla[3][1], true)) {
                $res = $this->procesarFilas($resFilas, $plugin);
            }
        }

        return $res;
    }

    private function procesarFilas($filas, $plugin)
    {
        $res = 0;

        for ($i = 2; isset($filas[$i][1]); $i++) {
            $info = $this->procesarMultiple($filas[$i][1]);
            $plugin->addResult(
                $info['titulo'],
                $info['urlDescarga'],
                $info['tamano'],
                $info['fecha'],
                $info['urlPagina'],
                $info['hash'],
                $info['semillas'],
                $info['clientes'],
                'Sin clasificar'
            );
            $res++;
        }

        return $res;
    }

    private function procesarMultiple($fila)
    {
        $resInfo = $this->regexp(
            "<td.*><a.*href=.*id%3D(?P<id>.*)%.*f%3D(?<nom"
            . "bre>.*)\.torrent.*>.*<\/a>\s*<\/td>.*<td.*>(?P<dia>\d+)\/(?P"
            . "<mes>\d+)\/(?P<ano>\d+)<\/td>\s*<td.*>(?<tamano>\d+\.\d*)\s("
            . "?<medida_tamano>[KMG]B)<\/td>\s*<td.*>(?P<semillas>\d+)<\/td"
            . ">\s*<td.*>(?P<clientes>\d+)<\/td>",
            $fila
        );
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
            'urlDescarga'   => "magnet:?xt=urn:btih:{$resInfo['id']}&dn=" . urldecode($resInfo['nombre'])
                                . '&tr=udp%3a//tracker.openbittorrent.com:80el29&tr=udp%3a//tracker.publi'
                                . 'cbt.com:80el39&tr=http%3a//www.spanishtracker.com:2710/announceel45&tr'
                                . '=http%3a//tracker.openbittorrent.com:80/announceel35',
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
            if (!preg_match_all("/$regexp/siU", $texto, $res, PREG_SET_ORDER)) {
                $res = null;
            }
        } else {
            if (!preg_match("/$regexp/siU", $texto, $res)) {
                $res = null;
            }
        }

        return $res;
    }
}
