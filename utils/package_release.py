#!/usr/bin/python
# -*- coding: utf-8 -*-

'''
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
'''

__author__ = "luskaner"
__date__ = "$5-jul-2014 19:17:30$"

import os
from os.path import basename
import glob
import json
import zipfile


def borrar_contenido_carpeta(carpeta):
    if os.path.exists(carpeta):
        files = glob.glob(os.path.join(carpeta, '*'))
        for f in files:
            os.remove(f)
    else:
        os.makedirs(carpeta)

if __name__ == "__main__":
    print 'Empaquetando los ficheros DLM y HOST...'
    print '---------------------------------------'
    os.system("python package_module.py")
    ruta_lanz = os.path.join('..', 'release')

    borrar_contenido_carpeta(ruta_lanz)

    print 'Creando los ficheros ZIP...'
    print '---------------------------'
    for dir in os.listdir(os.path.join('..', 'modules')):
        ruta_mod = os.path.join('..', 'modules', dir)
        if os.path.isdir(ruta_mod):
            host = False
            dlm = False
            ruta_host = os.path.join(ruta_mod, 'host')
            ruta_dlm = os.path.join(ruta_mod, 'dlm')
            nombre_fic = os.path.join(ruta_lanz, basename(ruta_mod))

            if os.path.isdir(ruta_dlm):
                dlm = True
                nombre_fic += ' DLM '
                json_info = open(os.path.join(ruta_dlm, 'INFO'))
                info = json.load(json_info)
                nombre_fic += info["version"]

            if os.path.isdir(ruta_host):
                host = True
                nombre_fic += ' HOST '
                json_info = open(os.path.join(ruta_host, 'INFO'))
                info = json.load(json_info)
                nombre_fic += info["version"]

            nombre_fic += '.zip'

            with zipfile.ZipFile(nombre_fic, 'w') as myzip:
                print 'Agregando el complemento ' + basename(ruta_mod)
                myzip.write(os.path.join(ruta_mod, 'LEEME.txt'), 'LEEME.txt')
                if host:
                    myzip.write(
                        os.path.join(
                            ruta_host,
                            'release',
                            basename(ruta_mod) + '.host'
                        ),
                        basename(ruta_mod) + '.host'
                    )
                if dlm:
                    myzip.write(
                        os.path.join(
                            ruta_dlm,
                            'release',
                            basename(ruta_mod) + '.dlm'
                        ),
                        basename(ruta_mod) + '.dlm'
                    )
