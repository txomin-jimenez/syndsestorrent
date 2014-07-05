<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$RUTA_BASE_MOD = '/usr/syno/etc/packages/DownloadStation/download/';
$RUTA_HOST = $RUTA_BASE_MOD . 'userhosts/';
$RUTA_DLM = $RUTA_BASE_MOD . 'userplugins/';

$htmlPagDescarga = file_get_contents('https://syndsestorrent.codeplex.com/SourceControl/latest');
$idCambio = array();
if (preg_match("/\"changesetId\":\"(.*)\"/i", $htmlPagDescarga, $idCambio)) {
    $ruta_zip = tempnam(sys_get_temp_dir(), 'codigo_fuente');
    file_put_contents($ruta_zip, fopen("http://download-codeplex.sec.s-msft.com/Download/SourceControlFileDownload.ashx?ProjectName=syndsestorrent&changeSetId=" . $idCambio[1], 'r'));
    $zip = new ZipArchive;
    if ($zip->open($ruta_zip) === TRUE) {
        $ficheroTemp = tempnam(sys_get_temp_dir(), 'syndsestorrent');
        if (unlink($ficheroTemp) === TRUE) {
            if (mkdir($ficheroTemp) === TRUE) {
                if ($zip->extractTo($ficheroTemp) === TRUE) {
                    $modulos = glob($ficheroTemp . '/modulos/*', GLOB_ONLYDIR);
                    foreach ($modulos as $modulo) {
                        if (is_dir($modulo . '/host')){
                            $info = json_decode(utf8_encode(file_get_contents($modulo . '/host/INFO')), true);
                            if (is_dir($RUTA_HOST . $info['name'])){
                                echo "Copiar de $modulo/host/INFO a $RUTA_HOST{$info['name']}/INFO<br/>";
                                copy($modulo . '/host/INFO', $RUTA_HOST . $info['name'] . '/INFO');
                                echo "Copiar de $modulo/host/{$info['module']} a $RUTA_HOST{$info['name']}/{$info['module']}<br/>";
                                copy($modulo . '/host/' . $info['module'], $RUTA_HOST . $info['name'] . '/' . $info['module']);
                            }
                        }
                        if (is_dir($modulo . '/dlm')){
                            $info = json_decode(utf8_encode(file_get_contents($modulo . '/dlm/INFO')), true);
                            if (is_dir($RUTA_HOST . $info['name'])){
                                echo "Copiar de $modulo/dlm/INFO a $RUTA_DLM{$info['name']}/INFO<br/>";
                                copy($modulo . '/dlm/INFO', $RUTA_DLM . $info['name'] . '/INFO');
                                echo "Copiar de $modulo/dlm/{$info['module']} a $RUTA_DLM{$info['name']}/{$info['module']}<br/>";
                                copy($modulo . '/dlm/' . $info['module'], $RUTA_DLM . $info['name'] . '/' . $info['module']);
                            }
                        }
                    }
                } else {
                    echo 'No se ha podido descomprimir el codigo fuente';
                }
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ficheroTemp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                    $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
                }
                rmdir($ficheroTemp);
            } else {
                echo 'No se ha podido crear el directorio temporal';
            }
        } else {
            echo 'No se ha podido cambiar el fichero temporal a directorio';
        }
        $zip->close();
    } else {
        echo 'No se ha podido abrir el fichero zip';
    }
    unlink($ruta_zip);
} else {
    echo 'No se ha podido encontrar la id de descarga';
}