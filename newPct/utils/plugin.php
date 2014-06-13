<?php

class plugin {
	public $results = array();
	public function addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category){
		$this->results[] = array($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
	}
}
?>