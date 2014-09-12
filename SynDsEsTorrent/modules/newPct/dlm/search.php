<?php

class SynoDLMSearchNewPct {

    private $qurl = 'http://www.newpct1.com/buscar';
    private $purl = 'http://www.newpct1.com/';

    public function __construct() {
        
    }

    /**
     * 
     * @param curl $curl objeto curl
     * @param string $query cadena a buscar
     */
    public function prepare($curl, $query) {
        curl_setopt($curl, CURLOPT_COOKIE, "language=es_ES");
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_REFERER, $this->purl);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.0.4) Gecko/2008102920 AdCentriaIM/1.7 Firefox/3.0.4');
        curl_setopt($curl, CURLOPT_URL, $this->qurl);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'q=' . urlencode(iconv('UTF-8', 'ISO-8859-1', $query)));
    }

    /**
     * 
     * @param plugin $plugin contendrá los resultados ya extraídos de la página
     * @param string $response respuesta html de la página
     * @return int número de resultados
     */
    public function parse($plugin, $response) {
        $regex_resultados = '<ul.*class="buscar-list".*>(?<resultados>.*)<\/ul>';
        $regex_info = '<li.*>.*<div.*class="info">.*<a.*href="(?<enlace_pagina>.*)".*<\/a>.*<h2.*>(?<titulo>.*)<\/h2>.*<span.*>(?<dia>\d+)-(?<mes>\d+)-(?<anyo>\d+)<\/span>.*<span.*>(?<tamanyo>\d+?(\.\d*?)??) (?<tipo_tamanyo>[KMGT]B).*<\/span>.*<\/div>.*<\/li>';

        $res = 0;
        $resultados = $this->regexp($regex_resultados, $response);

        if ($resultados !== '') {
            $resultados_info = $this->regexp($regex_info, $resultados['resultados'], true);
            if ($resultados_info !== '') {
                $res = $this->procesarResultados($plugin, $resultados_info);
            }
        }

        return $res;
    }
    
    private function procesarResultados($plugin, $resultados) {
        $res = 0;
        foreach ($resultados as $resultado) {
            $fecha = "{$resultado['anyo']}-{$resultado['mes']}-{$resultado['dia']}";
            $titulo = str_replace('&nbsp;', ' ', strip_tags(iconv('ISO-8859-1', 'UTF-8', $resultado['titulo'])));
            $hash = md5($titulo);
            $enlace_pagina = $resultado['enlace_pagina'];
            $tamano = $this->obtenerTamanyo($resultado['tamanyo'], $resultado['tipo_tamanyo']);
            $plugin->addResult($titulo, $enlace_pagina, $tamano, $fecha, $enlace_pagina, $hash, -1, -1, "Sin clasificar");
            $res++;
        }
        return $res;
    }

    private function obtenerTamanyo($tamano, $tipo_tamano) {
        $tamano_calculado = $tamano;
        switch ($tipo_tamano) {
            case 'KB':
                $tamano_calculado *= 1024;
                break;
            case 'MB':
                $tamano_calculado *= 1024 * 1024;
                break;
            case 'GB':
                $tamano_calculado *= 1024 * 1024 * 1024;
                break;
            case 'TB':
                $tamano_calculado *= 1024 * 1024 * 1024 * 1024;
                break;
        }
        return $tamano_calculado;
    }

    private function regexp($regexp, $texto, $global = false) {
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
