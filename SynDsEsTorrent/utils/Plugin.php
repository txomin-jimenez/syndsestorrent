<?php
namespace SynDsEsTorrent\utils;
/**
 * Class used to assure the search module is working
 */
class Plugin
{
    public $results = array();
        /**
         *
         * @param string $title title of the result
         * @param string $download URL of the download
         * @param int $size size in bytes, if unknown -> 0
         * @param string $datetime date in yyyy-mm-dd hh:mm format
         * @param string $page URL of the page
         * @param string $hash hash of the download
         * @param int $seeds number of seeds, if unknown -> -1
         * @param int $leechs number of leechers, if unknown -> -1
         * @param string $category category, if unknown -> "Sin clasificar"
         * @return void Does not return anything
         */
    public function addResult(
            $title,
            $download,
            $size,
            $datetime,
            $page,
            $hash,
            $seeds,
            $leechs,
            $category
        ) {
            $this->results[] = array(
                $title,
                $download,
                $size,
                $datetime,
                $page,
                $hash,
                $seeds,
                $leechs,
                $category
            );
    }
}
