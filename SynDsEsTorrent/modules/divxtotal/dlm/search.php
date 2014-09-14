<?php
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
        $regexp_res = "<ul.*class=\"section_list\".*>(.*)<\/ul>";
        $res = 0;

        $res_tabla = $this->regexp($regexp_res, $response);
        if ($res_tabla != '') {
            $res_info = $this->regexp(
            "<li.*class=\"section_item\d*\".*>.*<a.*href=\"(?P<url>.*)\".*>(?P<"
                . "nombre>.*)<\/a>.*<p.*class=\"seccontgen\".*><a.*>(?P<categor"
                . "ia>.*)<\/a>.*<\/p>.*<p.*class=\"seccontfetam\".*>(?P<dia>\d+"
                . ")-(?P<mes>\d+)-(?P<ano>\d+)<\/p>.*<p.*class=\"seccontfetam\""
                . ".*>(?P<tamano>\d+\.\d*)\s(?P<tipo_tamano>[MG]B)<\/p>.*<\/li>"
            ,
            $res_tabla[1],
            true
            );
            $res = $this->procesarFilas($res_info, $plugin);
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

        $res_id = $this->regexp('\/torrent\/(?P<id>\d+)\/', $fila['url']);

        if (!isset($res_id['id'])) {
            $fila['url'] = substr($fila['url'], 1);
            $url_descarga = self::REFERER_URL . $fila['url'] . '?cap='
                            . rawurlencode(trim($fila['nombre']));
        } else {
            $url_descarga = 'http://www.divxtotal.com/download.php?id='
                            . $res_id['id'];
        }

        $info = array(
            'urlPagina' => self::REFERER_URL . $fila['url'],
            'urlDescarga' => $url_descarga,
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
