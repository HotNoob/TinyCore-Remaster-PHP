<?php

//compile sed utils to make tcz package. 
//run in tinycore. 


shell_exec('mkdir /tmp/sedutils/');
shell_exec('cd /tmp/sedutils/');
/**
 * #install dependancies
 * tce-load -wi autoconf automake m4 util-macros libtool-dev gcc sed squashfs-tools compiletc

 *
 * mkdir /tmp/sedutils/
 * git clone https://github.com/Drive-Trust-Alliance/sedutil.git
 * cd sedutil
 * autoreconf -i
 * ./configure --enable-silent-rules
 * make
 * 
 * mkdir -p /tmp/sedutils_ext/usr/local/bin
 * cp /tmp/sedutils/sedutil/sedutil-cli /tmp/sedutils_ext/usr/local/bin
 * cp /tmp/sedutils/sedutil/sedutil-cli /usr/local/bin
 * 
 * mksquashfs /tmp/sedutils_ext sedutil.tcz
 * https://inventory.era.ca/share/tinycore/sedutil.tcz
 */
?>