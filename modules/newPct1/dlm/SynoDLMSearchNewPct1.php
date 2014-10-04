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

namespace modules\newPct1\dlm;

class SynoDLMSearchNewPct1
{
    private $qurl = 'http://www.newpct1.com/index.php?page=buscar&q=%s&categoryIDR=%s&idioma=%s&pg=%u';
    private $purl = 'http://www.newpct1.com/';
    private $query = '';
    private $cat = '';
    private $lang = '';

    public function __construct()
    {
    }

    /**
     *
     * @param curl   $curl  objeto curl
     * @param string $query cadena a buscar
     */
    public function prepare($curl, $query, $category = '', $lang = '')
    {
        $this->query = iconv('UTF-8', 'ISO-8859-1', $query);
        $this->cat = $category;
        $this->lang = $lang;
        curl_setopt($curl, CURLOPT_COOKIE, "language=es_ES");
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_REFERER, $this->purl);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        //curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1;
        //en; rv:1.9.0.4) Gecko/2008102920 AdCentriaIM/1.7 Firefox/3.0.4');
        curl_setopt($curl, CURLOPT_URL, sprintf($this->qurl, rawurlencode($this->query), $this->cat, $this->lang, 1));
    }

    /**
     *
     * @param  plugin $plugin   contendrá los resultados ya extraídos de la página
     * @param  string $response respuesta html de la página
     * @return int    número de resultados
     */
    public function parse($plugin, $response, $maxPages = 10)
    {
        $regexResultados = '<ul.*class="buscar-list".*>(?<resultados>.*)<\/ul>';
        $regexInfo = '<li.*>.*<div.*class="info">.*<a.*href="(?<enlace_pagina>.'
                . '*)".*.*<h2.*>(?<titulo>.*)<\/h2>.*<\/a>.*<span.*>(?<dia>\d+)'
                . '-(?<mes>\d+)-(?<anyo>\d+)<\/span>.*<span.*>(?<tamanyo>\d+?('
                . '\.\d*?)??) (?<tipo_tamanyo>[KMGT]B).*<\/span>.*<\/div>.*<\/li>';
        $numRes = 0;
        $pagActual = 1;
        do {
            $resultados = $this->regexp($regexResultados, $response);
            if ($resultados === null) {
                break;
            }
            $resultadosInfo = $this->regexp($regexInfo, $resultados['resultados'], true);
            if ($resultadosInfo === null) {
                break;
            }
            $numRes += $this->procesarResultados($plugin, $resultadosInfo);
            $response = $this->obtenerSiguientePagina($response, $pagActual);
            $pagActual++;
        } while ($pagActual <= $maxPages && $response !== null);

        return $numRes;
    }

    private function obtenerSiguientePagina($html, $pagActual)
    {
        $res = $this->regexp('<ul.*class="pagination".*>.*<a href="(?<enlace_pag_sig>[^>]*)">Next<\/a>.*<\/ul>', $html);
        $ret = null;
        if ($res !== null) {
            $pagUrl = sprintf($this->qurl, rawurlencode($this->query), $this->cat, $this->lang, $pagActual + 1);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_COOKIE, "language=es_ES");
            curl_setopt($curl, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl, CURLOPT_REFERER, $this->purl);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            //curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.0.4) Gecko/20
            //08102920 AdCentriaIM/1.7 Firefox/3.0.4');
            curl_setopt($curl, CURLOPT_URL, $pagUrl);
            $ret = curl_exec($curl);
        }

        return $ret;
    }

    private function procesarResultados($plugin, $resultados)
    {
        $res = 0;
        foreach ($resultados as $resultado) {
            $fecha = "{$resultado['anyo']}-{$resultado['mes']}-{$resultado['dia']}";
            $titulo = str_replace('&nbsp;', ' ', strip_tags(iconv('ISO-8859-1', 'UTF-8', $resultado['titulo'])));
            $hash = md5($titulo);
            $enlacePagina = $resultado['enlace_pagina'];
            $tamano = $this->obtenerTamanyo($resultado['tamanyo'], $resultado['tipo_tamanyo']);
            $plugin->addResult(
                $titulo,
                $enlacePagina . '/dlm/',
                $tamano,
                $fecha,
                $enlacePagina,
                $hash,
                -1,
                -1,
                "Sin clasificar"
            );
            $res++;
        }

        return $res;
    }

    private function obtenerTamanyo($tamano, $tipoTamano)
    {
        $tamanoCalculado = $tamano;
        switch ($tipoTamano) {
            case 'KB':
                $tamanoCalculado *= 1024;
                break;
            case 'MB':
                $tamanoCalculado *= 1024 * 1024;
                break;
            case 'GB':
                $tamanoCalculado *= 1024 * 1024 * 1024;
                break;
            case 'TB':
                $tamanoCalculado *= 1024 * 1024 * 1024 * 1024;
                break;
        }

        return $tamanoCalculado;
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
