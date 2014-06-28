<?php
/**
 * Clase utilizada para testear el funcionamiento del addon de búsqueda
 */
class plugin {
	public $results = array();
        /**
         * 
         * @param string $title título del resultado
         * @param string $download URL de descarga
         * @param int $size tamaño en bytes, si no se sabe -> 0
         * @param string $datetime fecha en formato yyyy-mm-dd hh:mm
         * @param string $page URL de la página
         * @param string $hash hash de la descarga
         * @param int $seeds número de semillas, si no se sabe -> -1
         * @param int $leechs número de leechers, si no se sabe -> -1
         * @param string $category categoría, si no se sabe -> "Sin clasificar"
         * @return void No devuelve nada
         */
	public function addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category){
		$this->results[] = array($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
	}
}
?>