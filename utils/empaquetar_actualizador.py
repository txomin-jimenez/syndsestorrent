#! /usr/bin/python
# -*- coding: utf-8 -*-

# Copyright (C) 2014 David
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

import os.path
import tarfile
from empaquetar_lanzamiento import borrar_contenido_carpeta

__author__="David"
__date__ ="$15-jul-2014 3:10:46$"

ruta_base = os.path.join('..', 'actualizador')
ruta_lanz = os.path.join(ruta_base, 'lanzamiento')
ruta_pq_b = os.path.join(ruta_lanz, 'package.tgz')
ruta_ico = os.path.join(ruta_base, 'PACKAGE_ICON.PNG')
ruta_256 = os.path.join(ruta_base, 'PACKAGE_ICON_256.PNG')
ruta_inf = os.path.join(ruta_base, 'INFO')
ruta_lic = os.path.join(ruta_base, 'LICENSE')
ruta_scr = os.path.join(ruta_base, 'scripts')

def set_permissions(tarinfo):
    tarinfo.mode = 511
    return tarinfo

def construir_tar_gz(*arg):
    """
    Hace un fichero comprimido .tar.gz como nombre arg(0) y añadiendo dentro
    el resto de parámetros como ficheros
    """
    if os.path.isfile(arg[0]):
        os.remove(arg[0])
        
    with tarfile.open(arg[0], "w:gz") as tar:
        for f in xrange(1, len(arg)):
            split_path = arg[f].split(os.sep)
            tar.add(arg[f], split_path[len(split_path) - 1], filter=set_permissions)
            
def construir_paq_base():
    construir_tar_gz(ruta_pq_b, os.path.join(ruta_base, 'codigo'))
    
def construir_paq():
    construir_paq_base()
    construir_tar_gz(os.path.join(ruta_lanz, 'synDsEsTorrent actualizador.spk'), 
                    ruta_pq_b, ruta_ico, ruta_256, ruta_inf, ruta_lic, ruta_scr)
    
if __name__ == "__main__":
    borrar_contenido_carpeta(ruta_lanz)
    construir_paq()
    os.remove(ruta_pq_b)
