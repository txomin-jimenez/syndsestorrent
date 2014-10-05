#! /usr/bin/python
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

import tarfile
import os
import os.path
import json
import sys

__author__ = "luskaner"
__date__ = "$14-jun-2014 17:16:45$"


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
            tar.add(arg[f], split_path[len(split_path) - 1])


def obt_ruta_base(addon):
    return os.path.join('..', 'modules', addon)


def obt_ruta_paq(addon, tipo):
    return os.path.join(obt_ruta_base(addon), tipo, "release", addon + '.tar')


def obt_ruta_fic(addon, tipo):
    fichero_info = ''
    fichero_base = ''
    if tipo == 'dlm':
        fichero_info = os.path.join(obt_ruta_base(addon), 'dlm', 'INFO')
        json_info = open(fichero_info)
        fichero_base = os.path.join(obt_ruta_base(addon), 'dlm')
    elif tipo == 'host':
        fichero_info = os.path.join(obt_ruta_base(addon), 'host', 'INFO')
        json_info = open(fichero_info)
        fichero_base = os.path.join(obt_ruta_base(addon), 'host')

    info = json.load(json_info)
    fichero_base = os.path.join(fichero_base, info["module"])
    return fichero_info, fichero_base


def construir(addon, tipo):
    print "Construyendo el modulo " + tipo + " para " + addon
    ruta_mod = os.path.join(obt_ruta_base(addon), tipo)

    if not os.path.isdir(ruta_mod):
        print "No se crea el modulo " + tipo + ": falta la carpeta " + tipo
        return

    fic = obt_ruta_fic(addon, tipo)

    if not os.path.isfile(fic[0]):
        print "No se crea el modulo " + tipo + ": falta el fichero " + fic[0]
        return
    if not os.path.isfile(fic[1]):
        print "No se crea el modulo " + tipo + ": falta el fichero " + fic[1]
        return

    paq_tar = obt_ruta_paq(addon, tipo)
    ruta_mod_paq = os.path.join(ruta_mod, "release")

    if not os.path.isdir(ruta_mod_paq):
        os.makedirs(ruta_mod_paq)

    construir_tar_gz(paq_tar, fic[0], fic[1])

    paq = paq_tar.replace('.tar', '.' + tipo)
    if os.path.isfile(paq):
        os.remove(paq)

    os.rename(paq_tar, paq)
    print "OK"

if __name__ == "__main__":
    if len(sys.argv) == 1:
        for dir in os.listdir(os.path.join('..', 'modules')):
            if os.path.isdir(os.path.join('..', 'modules', dir)):
                dir_split = dir.split(os.sep)
                construir(dir_split[len(dir_split) - 1], "dlm")
                construir(dir_split[len(dir_split) - 1], "host")
    elif len(sys.argv) == 2:
        construir(sys.argv[1], "dlm")
        construir(sys.argv[1], "host")
    elif len(sys.argv) == 3:
        construir(sys.argv[1], sys.argv[2])
    else:
        print "Use:"
        print "\t" + sys.argv[0]
        print "\t" + sys.argv[0] + " [<addon>]"
        print "\t" + sys.argv[0] + " [<addon>] " + "[<tipo:dlm/host>]"
