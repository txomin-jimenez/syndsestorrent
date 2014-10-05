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

namespace modules\eliteTorrentNet\dlm;

class SynoDLMSearchEliteTorrentNet
{
    private $qurl = 'http://www.elitetorrent.net/busqueda/%s/modo:listado';
    private $purl = 'http://www.elitetorrent.net';

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
        curl_setopt($curl, CURLOPT_COOKIE, "language=es_ES");
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_REFERER, $this->purl);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->qurl, iconv('ISO-8859-1', 'UTF-8', $query)));
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
        $regexpTabla = "<table.*class=\"fichas-listado\".*>(.*)<\/table>";
        $regexpFila = "<tr.*>(.*)<\/tr>";
        $res = 0;

        $resTabla = array();
        if (preg_match("/$regexpTabla/siU", $response, $resTabla)) {
            $resFilas = array();
            if (preg_match_all("/$regexpFila/siU", $resTabla[0], $resFilas, PREG_SET_ORDER)) {
                $res = $this->procesarFilas($resFilas, $plugin);
            }
        }

        return $res;
    }

    private function procesarFilas($filas, $plugin)
    {
        $res = 0;

        for ($i = 1; isset($filas[$i][1]); $i++) {
            $titulo = $this->regexp("<a.*title=\"(.*)\"", $filas[$i][1], true)[1][1];
            $urlTorrentPagina = $this->procesarUrlTorrentPagina($filas[$i][1]);
            $fecha = $this->procesarFecha($filas[$i][1]);
            $hash = md5($titulo);
            $semillas = $this->procesarSemillas($filas[$i][1]);
            $pares = $this->procesarPares($filas[$i][1]);
            $categoria = $this->procesarCategoria($filas[$i][1]);
            $plugin->addResult(
                $titulo,
                $urlTorrentPagina[0],
                0,
                $fecha,
                $urlTorrentPagina[1],
                $hash,
                $semillas,
                $pares,
                $categoria
            );
            $res++;
        }

        return $res;
    }

    private function procesarUrlTorrentPagina($fila)
    {
        $url = $this->regexp("(<a.*href=\"(.*)\".*>.*<\/a>)", $fila, true);

        return array($this->purl . $url[0][2], $this->purl . $url[1][2]);
    }

    private function procesarFecha($fila)
    {
        $fecha = $this->regexp("<td.*class=\"fecha\">(.*)<\/td>", $fila);
        $fechaStr = $fecha[1];
        $fechaSplit = explode(' ', $fechaStr);
        $cantidad = $fechaSplit[1];

        if ($cantidad == 'un' || $cantidad == 'una') {
            $cantidad = '1';
        }

        $tipo = $fechaSplit[2];
        $tiposEs = array('hora', 'horas', 'día', 'días', 'sem', 'mes', 'meses',
            'año', 'años');
        $tiposEn = array('hour', 'hours', 'day', 'days', 'week', 'month',
            'months', 'year', 'years');
        $tipoTrad = str_replace($tiposEs, $tiposEn, $tipo);

        if ($tipo == 'week' && $cantidad > 1) {
            $tipoTrad .= 's';
        } elseif ($tipoTrad == "monthes") {
            $tipoTrad = "months";
        }

        return date('Y-m-d H:i:s', strtotime("-$cantidad $tipoTrad"));
    }

    private function procesarSemillas($fila)
    {
        $semillas = $this->regexp("<td.*class=\"semillas\">.*>(\d+)<", $fila);
        $numSemillas = -1;
        if (!isset($semillas[1])) {
            $numSemillas = $semillas[1];
        }

        return $numSemillas;
    }

    private function procesarPares($fila)
    {
        $pares = $this->regexp("<td.*class=\"clientes\">.*>(\d+)<", $fila);
        if (!isset($pares[1])) {
            return -1;
        } else {
            return $pares[1];
        }
    }

    private function procesarCategoria($fila)
    {
        $categoria = $this->regexp("<span.*class=\"categoria\">(.*)<\/span>", $fila);
        $categoriaSinEspacios = trim($categoria[1]);
        $categoriaLimpia = explode(' ', $categoriaSinEspacios);
        unset($categoriaLimpia[0]);

        return join(' ', $categoriaLimpia);
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
