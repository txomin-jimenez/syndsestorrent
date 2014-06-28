<?php

class SynoDLMSearchDivxatope {

    private $qurl = 'http://divxatope.com/main.php?q=%s';
    private $purl = 'http://divxatope.com/';

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
        curl_setopt($curl, CURLOPT_URL, sprintf($this->qurl, iconv('ISO-8859-1', 'UTF-8', $query)));
    }

    /**
     * 
     * @param plugin $plugin contendrá los resultados ya extraídos de la página
     * @param string $response respuesta html de la página
     * @return int número de resultados
     */
    public function parse($plugin, $response) {
        // Definimos las cadenas REGEXP hasta llegar a los torrent
        $regexp_fila = "<div.*class=\"torrent-info\".*>(.*)<\/div>";
        $res = 0;

        $res_filas = array();
        if (preg_match_all("/$regexp_fila/siU", $response, $res_filas, PREG_SET_ORDER)) {
            $res = $this->procesarFilas($res_filas, $plugin);
        }
        return $res;
    }

    private function procesarFilas($filas, $plugin) {
        $res = 0;

        for ($i = 0; isset($filas[$i][1]); $i++) {
            $info = $this->procesarMultiple($filas[$i][1]);
            $fecha = $this->procesarFecha($filas[$i][1]);
            $hash = md5($info['titulo']);
            $plugin->addResult($info['titulo'], $info['urlPagina'], $info['tamano'], $fecha, $info['urlPagina'], $hash, -1, -1, $info['categoria']);
            $res++;
        }

        return $res;
    }
    
    private function procesarMultiple($fila){
        $resInfo = $this->regexp("<a.*href\s*=\s*\"(?<url>.*)\".*>(?<contenido>.*)<\/a>", $fila, true);
        $info = array(
            'urlPagina'     => $this->purl . html_entity_decode($resInfo[0]['url']),
            'titulo'        => trim($resInfo[0]['contenido']),         
            'categoria'     => trim($resInfo[1]['contenido']),
            'tamano'        => $resInfo[3]['contenido'] * 1024 * 1024
        );
        return $info;
    }
    
    private function procesarFecha($fila) {
        $resFecha = $this->regexp("<p.*>(?P<dia>\d+)-(?P<mes>\d+)-(?P<ano>\d+)<\/p>", $fila);       
        return "{$resFecha['ano']}-{$resFecha['mes']}-{$resFecha['dia']}";
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
?>