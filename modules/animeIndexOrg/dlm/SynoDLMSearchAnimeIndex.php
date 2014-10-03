<?php

namespace modules\animeIndexOrg\dlm;

class SynoDLMSearchAnimeIndex
{
    private $qurl = 'http://tracker.anime-index.org/index.php?page=torrents&search=%s&category=16&active=1';
    private $purl = 'http://tracker.anime-index.org/';

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
        $regexpTabla = "<table.*class=\"lista\">(.*)<\/table>";
        $regexpFila = "<tr.*>(.*)<\/tr>";
        $res = 0;

        $resTabla = array();
        if (preg_match_all("/$regexpTabla/siU", $response, $resTabla)) {
            $resFilas = array();
            if (preg_match_all("/$regexpFila/siU", $resTabla[1][1], $resFilas, PREG_SET_ORDER)) {
                $res = $this->procesarFilas($resFilas, $plugin);
            }
        }

        return $res;
    }

    private function procesarFilas($filas, $plugin)
    {
        $res = 0;

        for ($i = 1; isset($filas[$i][1]); $i++) {
            $info = $this->procesarMultiple($filas[$i][1]);
            $fecha = $this->procesarFecha($filas[$i][1]);
            $hash = md5($info['titulo']);
            $plugin->addResult(
                $info['titulo'],
                $info['urlDescarga'],
                0,
                $fecha,
                $info['urlPagina'],
                $hash,
                $info['semillas'],
                $info['clientes'],
                "Sin clasificar"
            );
            $res++;
        }

        return $res;
    }

    private function procesarMultiple($fila)
    {
        $resInfo = $this->regexp("<td.*>(.*)<\/td>", $fila, true);
        $resUrlPagina = $this->regexp('<a.*href="(.*)".*>', $resInfo[1][1]);
        $resUrlDescarga = $this->regexp('<a.*href="(.*)".*>', $resInfo[2][1]);
        $info = array(
            'urlPagina' => $this->purl . html_entity_decode($resUrlPagina[1]),
            'titulo' => strip_tags($resInfo[1][0]),
            'urlDescarga' => $this->purl . html_entity_decode($resUrlDescarga[1]),
            'semillas' => strip_tags($resInfo[4][0]),
            'clientes' => strip_tags($resInfo[5][0])
        );

        return $info;
    }

    private function procesarFecha($fila)
    {
        $resFecha = $this->regexp("<td.*>(\d+)\/(\d+)\/(\d+)\s(\d+):(\d+)<\/td>", $fila);
        $dia = $resFecha[1];
        $mes = $resFecha[2];
        $anyo = $resFecha[3];
        $hora = $resFecha[4];
        $minuto = $resFecha[5];

        return "$anyo-$mes-$dia $hora:$minuto";
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
