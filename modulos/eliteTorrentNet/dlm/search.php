<?php

class SynoDLMSearchEliteTorrentNet {

    private $qurl = 'http://www.elitetorrent.net/busqueda/%s/modo:listado';
    private $purl = 'http://www.elitetorrent.net';

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
        $regexp_tabla = "<table.*class=\"fichas-listado\".*>(.*)<\/table>";
        $regexp_fila = "<tr.*>(.*)<\/tr>";
        $res = 0;

        $res_tabla = array();
        if (preg_match("/$regexp_tabla/siU", $response, $res_tabla)) {
            $res_filas = array();
            if (preg_match_all("/$regexp_fila/siU", $res_tabla[0], $res_filas, PREG_SET_ORDER)) {
                $res = $this->procesarFilas($res_filas, $plugin);
            }
        }
        return $res;
    }

    private function procesarFilas($filas, $plugin) {
        $res = 0;

        for ($i = 1; isset($filas[$i][1]); $i++) {
            $titulo = $this->procesarTitulo($filas[$i][1]);
            $urlTorrentPagina = $this->procesarUrlTorrentPagina($filas[$i][1]);
            $fecha = $this->procesarFecha($filas[$i][1]);
            $hash = md5($titulo);
            $semillas = $this->procesarSemillas($filas[$i][1]);
            $pares = $this->procesarPares($filas[$i][1]);
            $categoria = $this->procesarCategoria($filas[$i][1]);
            $plugin->addResult($titulo, $urlTorrentPagina[0], 0, $fecha, $urlTorrentPagina[1], $hash, $semillas, $pares, $categoria);
            $res++;
        }

        return $res;
    }

    private function procesarTitulo($fila) {
        $titulo = $this->regexp("<a.*title=\"(.*)\"", $fila, true);
        return $titulo[1][1];
    }

    private function procesarUrlTorrentPagina($fila) {
        $url = $this->regexp("(<a.*href=\"(.*)\".*>.*<\/a>)", $fila, true);
        return array($this->purl . $url[0][2], $this->purl . $url[1][2]);
    }

    private function procesarFecha($fila) {
        $fecha = $this->regexp("<td.*class=\"fecha\">(.*)<\/td>", $fila);
        $fecha_str = $fecha[1];
        $fecha_split = explode(' ', $fecha_str);
        $cantidad = $fecha_split[1];

        if ($cantidad == 'un' || $cantidad == 'una') {
            $cantidad = '1';
        }

        $tipo = $fecha_split[2];
        $tipos_es = array('hora', 'horas', 'día', 'días', 'sem', 'mes', 'meses',
            'año', 'años');
        $tipos_en = array('hour', 'hours', 'day', 'days', 'week', 'month',
            'months', 'year', 'years');
        $tipo_trad = str_replace($tipos_es, $tipos_en, $tipo);
        
        if ($tipo == 'week' && $cantidad > 1){
            $tipo_trad .= 's';
        } else if ($tipo_trad == "monthes"){
            $tipo_trad = "months";
        }

        return date('Y-m-d H:i:s', strtotime("-$cantidad $tipo_trad"));
    }
    
    private function procesarSemillas($fila) {
        $semillas = $this->regexp("<td.*class=\"semillas\">.*>(\d+)<", $fila);
        if (!isset($semillas[1])) {
            return -1;
        } else {
            return $semillas[1];
        }
    }

    private function procesarPares($fila) {
        $pares = $this->regexp("<td.*class=\"clientes\">.*>(\d+)<", $fila);
        if (!isset($pares[1])) {
            return -1;
        } else {
            return $pares[1];
        }
    }

    private function procesarCategoria($fila) {
        $categoria = $this->regexp("<span.*class=\"categoria\">(.*)<\/span>", $fila);
        $categoria_sin_espacios = trim($categoria[1]);
        $categoria_limpia = explode(' ', $categoria_sin_espacios);
        unset($categoria_limpia[0]);        
        return join(' ', $categoria_limpia);
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