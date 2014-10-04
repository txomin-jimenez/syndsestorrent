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

namespace modules\divxtotal\dlm;

class SynoDLMSearchDivxTotal
{

    const QUERY_URL = 'http://www.divxtotal.com/buscar.php?busqueda=%s';
    const REFERER_URL = 'http://www.divxtotal.com/';

    public function __construct()
    {

    }

    /**
     *
     * @param curl   $curl  curl object
     * @param string $query string to search
     */
    public function prepare($curl, $query)
    {
        curl_setopt($curl, CURLOPT_COOKIE, "language=es_ES");
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_REFERER, self::QUERY_URL);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt(
            $curl,
            CURLOPT_URL,
            sprintf(self::QUERY_URL, iconv('UTF-8', 'ISO-8859-1', $query))
        );
    }

    /**
     *
     * @param  plugin $plugin   contendrá los resultados ya extraídos de la pág.
     * @param  string $response respuesta html de la página
     * @return int    número de resultados
     */
    public function parse($plugin, $response)
    {
        // Definimos las cadenas REGEXP hasta llegar a los torrent
        $regexpRes = "<ul.*class=\"section_list\".*>(.*)<\/ul>";
        $res = 0;

        $resTabla = $this->regexp($regexpRes, $response);
        if ($resTabla != '') {
            $resInfo = $this->regexp(
                "<li.*class=\"section_item\d*\".*>.*<a.*href=\"(?P<url>.*)\".*>("
                . "?P<nombre>.*)<\/a>.*<p.*class=\"seccontgen\".*><a.*>(?P<categor"
                . "ia>.*)<\/a>.*<\/p>.*<p.*class=\"seccontfetam\".*>(?P<dia>\d+"
                . ")-(?P<mes>\d+)-(?P<ano>\d+)<\/p>.*<p.*class=\"seccontfetam\""
                . ".*>(?P<tamano>\d+\.\d*)\s(?P<tipo_tamano>[MG]B)<\/p>.*<\/li>",
                $resTabla[1],
                true
            );
            $res = $this->procesarFilas($resInfo, $plugin);
        }

        return $res;
    }

    private function procesarFilas($filas, $plugin)
    {
        $res = 0;

        for ($i = 0; isset($filas[$i]); $i++) {
            $info = $this->procesarMultiple($filas[$i]);
            $hash = md5($info['titulo']);

            $plugin->addResult(
                $info['titulo'],
                $info['urlDescarga'],
                $info['tamano'],
                $info['fecha'],
                $info['urlPagina'],
                $hash,
                -1,
                -1,
                $info['categoria']
            );

            $res++;
        }

        return $res;
    }

    private function procesarMultiple($fila)
    {
        $tamano = $fila['tamano'];

        switch ($fila['tipo_tamano']) {
            case 'MB':
                $tamano *= 1024 * 1024;
                break;
            case 'GB':
                $tamano *= 1024 * 1024 * 1024;
                break;

        }

        $resId = $this->regexp('\/torrent\/(?P<id>\d+)\/', $fila['url']);

        if (!isset($resId['id'])) {
            $fila['url'] = substr($fila['url'], 1);
            $urlDescarga = self::REFERER_URL . $fila['url'] . '?cap='
                            . rawurlencode(trim($fila['nombre']));
        } else {
            $urlDescarga = 'http://www.divxtotal.com/download.php?id='
                            . $resId['id'];
        }

        $info = array(
            'urlPagina' => self::REFERER_URL . $fila['url'],
            'urlDescarga' => $urlDescarga,
            'titulo'    => iconv('ISO-8859-1', 'UTF-8', trim($fila['nombre'])),
            'categoria' => iconv('ISO-8859-1', 'UTF-8', trim($fila['categoria'])),
            'fecha'     => "{$fila['ano']}-{$fila['mes']}-{$fila['dia']}",
            'tamano'    => $tamano
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
