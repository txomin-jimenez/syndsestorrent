<?php

class SynoDLMSearchNewPct {

    private $qurl = 'http://www.newpct1.com/buscar-descargas/';
    private $purl = 'http://www.newpct1.com/';

    public function __construct() {  
    }
    
    /**
     * 
     * @param curl $curl objeto curl
     * @param string $query cadena a buscar
     */
    public function prepare($curl, $query) {
        $fields = array(
            'cID' => 0,
            'tLang' => 0,
            'oBy' => 0,
            'oMode' => 0,
            // Transformamos el texto por el tema de accentos, etc..
            'q' => urlencode(iconv('UTF-8', 'ISO-8859-1', $query)),
            'doSearch.x' => 0,
            'doSearch.y' => 0
        );
        $fields_string = '';
        
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        } 
        
        rtrim($fields_string, '&');        
        curl_setopt($curl, CURLOPT_COOKIE, "language=es_ES");
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_REFERER, $this->purl);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.0.4) Gecko/2008102920 AdCentriaIM/1.7 Firefox/3.0.4');
        curl_setopt($curl, CURLOPT_URL, $this->qurl);
        curl_setopt($curl, CURLOPT_POST, count($fields));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
    }
    /**
     * 
     * @param plugin $plugin contendrá los resultados ya extraídos de la página
     * @param string $response respuesta html de la página
     * @return int número de resultados
     */
    public function parse($plugin, $response) {
        $regexp_main = "<div id=\"categoryList\".*>(.*)<\/div>";
        $regexp_tabla = "<tbody>(.*)<td class=\"center tdpagination\"";
        $regexp_fila = "<tr>(.*)<\/tr>";
        $regexp_celda = "<td\b[^>]*>(.*?)<\/td>";
        $regexp_titulo = "<a.*>(.*)<\/a>";
        $regexp_enlace_descarga = "href=\"(.*)\"";
        
        $oldHost = "pctorrent";
        $newHost = "tumejorjuego";
        
        $res = 0;

        if (preg_match_all("/$regexp_main/siU", $response, $matches2, PREG_SET_ORDER)) {
            if (preg_match_all("/$regexp_tabla/siU", $matches2[0][0], $matches3, PREG_SET_ORDER)) {
                if (preg_match_all("/$regexp_fila/siU", $matches3[0][0], $matches4, PREG_SET_ORDER)) {
                    foreach ($matches4 as $fila) {
                        if (preg_match_all("/$regexp_celda/si", $fila[1], $matches5)) {
                            for ($i = 1; isset($matches5[$i][1]); $i += 2) {
                                // FECHA                                
                                $fecha_separada = explode("-", $matches5[$i][0]);
                                $fecha = "20{$fecha_separada[2]}-{$fecha_separada[1]}-{$fecha_separada[0]}";

                                // TITULO
                                if (preg_match("/$regexp_titulo/siU", $matches5[$i][1], $matches6)) {
                                    //var_dump($matches6);
                                    $titulo = strip_tags(trim($matches6[1]));
                                }

                                // ENLACE PAGINA

                                if (preg_match("/$regexp_enlace_descarga/siU", $matches5[$i][1], $matches8)) {
                                    $enlace_pagina = $matches8[1];
                                }

                                // ENLACE DESCARGA

                                if (preg_match("/$regexp_enlace_descarga/siU", $matches5[$i][3], $matches9)) {
                                    $enlace_descarga = $matches9[1];
                                    $enlace_descarga = str_replace($oldHost, $newHost, $enlace_descarga);
                                }

                                // TAMAÑO
                                $tamano_separado = explode(" ", $matches5[$i][2]);
                                switch ($tamano_separado[1]) {
                                    case 'KB':
                                        $tamano = $tamano_separado[0] * 1024;
                                        break;
                                    case 'MB':
                                        $tamano = $tamano_separado[0] * 1024 * 1024;
                                        break;
                                    case 'GB':
                                        $tamano = $tamano_separado[0] * 1024 * 1024 * 1024;
                                        break;
                                }

                                // HASH
                                $hash = md5($titulo);
                                $plugin->addResult(iconv('ISO-8859-1', 'UTF-8', $titulo), $enlace_descarga, $tamano, $fecha, $enlace_pagina, $hash, -1, -1, "Sin clasificar");
                            }
                        }
                        $res++;
                    }
                }
            }
        }        
        
        return $res;
    }

}

?>