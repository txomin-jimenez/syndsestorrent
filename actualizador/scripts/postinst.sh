#!/bin/sh

echo -e "@hourly\troot\tphp ${SYNOPKG_PKGDEST}/syndsestorrent_actualizador.php > log.txt 2>&1" >> /etc/crontab