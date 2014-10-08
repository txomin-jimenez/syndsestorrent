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

namespace utils;

abstract class BaseDlmTest extends \PHPUnit_Framework_TestCase
{

    abstract public function testParse();

    abstract public function testLoadInfo();

    protected $search;

    protected function setObject($object)
    {
        $this->search = $object;
    }

    protected function parse()
    {
        $plugin = new Plugin();
        $curl = curl_init();
        $this->search->prepare($curl, 'a');
        $data = curl_exec($curl);
        $this->assertGreaterThan(0, $this->search->parse($plugin, $data), "No hay resultados");
    }

    protected function loadInfoTest()
    {
        $infoClase = new \ReflectionClass($this->search);
        $carpetaClase = dirname($infoClase->getFileName());
        $ficheroInfo = "$carpetaClase/INFO";
        $this->assertFileExists($ficheroInfo);
        $infoFichero = json_decode(file_get_contents($ficheroInfo), true);

        $atributos = ['name', 'displayname', 'description', 'version', 'site', 'module', 'type', 'class'];

        foreach ($atributos as $atributo) {
            $this->assertArrayHasKey($atributo, $infoFichero);
            $this->assertNotEmpty($infoFichero[$atributo]);
        }

        $this->assertRegExp('/^\d(\.\d)*$/', $infoFichero['version']);
        $this->assertNotFalse(
            filter_var(
                $infoFichero['site'],
                FILTER_VALIDATE_URL
            ),
            "Url '{$infoFichero['site']}' inválida"
        );
        $this->assertEquals(basename($infoClase->getFileName()), $infoFichero['module']);
        $this->assertEquals('search', $infoFichero['type']);
        $this->assertTrue(class_exists($infoFichero['class']), "Clase '{$infoFichero["class"]}' inexistente");
        $this->assertEquals(
            get_class($this->search),
            $infoFichero['class'],
            "La clase {$infoFichero['class']} debería ser " . get_class($this->search)
        );
    }
}
