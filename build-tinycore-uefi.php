<?php

chdir(__DIR__);

function clean()
{
    echo '==CLEAN==';
    echo shell_exec('rm -rf /tmp/newiso');
    echo shell_exec('sudo umount /mnt/tmp');
    echo shell_exec('rm -rf /mnt/tmp');
    echo shell_exec('rm -rf /tmp/extract');
}

$fileDir = 'hn-specs64/';
$iso = 'TinyCorePure64-13.1.iso';
$outIso = '/tmp/tinycore-custom-uefi-hn-'.time().'.iso';
$extensions = $fileDir.'extensions64';

clean();

echo PHP_EOL.'==EXTRACT=='.PHP_EOL;
echo shell_exec('sudo mkdir /mnt/tmp');
echo shell_exec('sudo mount '.$iso.' /mnt/tmp -o loop,ro');
echo shell_exec('cp -a /mnt/tmp/boot /tmp');
echo shell_exec('cp -a /mnt/tmp/cde /tmp');
echo shell_exec('cp -a /mnt/tmp/EFI /tmp');
echo shell_exec('mv /tmp/boot/corepure64.gz /tmp');
echo shell_exec('sudo umount /mnt/tmp');

echo shell_exec('mkdir /tmp/extract');
echo shell_exec('cd /tmp/extract && zcat /tmp/corepure64.gz | sudo cpio -i -H newc -d');

echo PHP_EOL.'==INSTALL EXTENSIONS=='.PHP_EOL;

$files = scandir($extensions);

foreach($files as $file)
{
        $ext =  pathinfo($file, PATHINFO_EXTENSION);
        if($ext == 'tcz')
        {
            echo 'Installing : '.$file.PHP_EOL;
            shell_exec('unsquashfs -f -d /tmp/extract '.$extensions.'/'.$file);
        }
}

echo PHP_EOL.'==EDIT BOOT FILES=='.PHP_EOL;
echo shell_exec('rsync -c -a '.$fileDir.'bootfiles64/ /tmp/boot');
echo shell_exec('rsync -c -a '.$fileDir.'EFIfiles64/ /tmp/EFI');

echo PHP_EOL.'==EDIT FILES=='.PHP_EOL;
echo shell_exec('rsync -c -a '.$fileDir.'files/ /tmp/extract');



#repacking
echo PHP_EOL.'==PACKING corepure64.gz=='.PHP_EOL;
echo shell_exec('sudo ldconfig -r /tmp/extract');


echo shell_exec('sudo ldconfig -r /tmp/extract');
echo shell_exec('cd /tmp/extract && sudo find | sudo cpio -o -H newc | gzip -2 > /tmp/corepure64.gz');


echo PHP_EOL.'==REPACKING corepure64.gz=='.PHP_EOL;

//z4 for prod. z1 for dev
echo shell_exec('cd /tmp/ && advdef -f -z4 /tmp/corepure64.gz');


sleep(7);

#make iso
echo PHP_EOL.'==MAKING ISO=='.PHP_EOL;
//cd /tmp
echo shell_exec('mv /tmp/corepure64.gz /tmp/boot');
echo shell_exec('mkdir /tmp/newiso');
echo shell_exec('mv /tmp/boot /tmp/newiso');
echo shell_exec('mv /tmp/cde /tmp/newiso');
echo shell_exec('mv /tmp/EFI /tmp/newiso');
echo shell_exec('cp /usr/lib/ISOLINUX/isohdpfx.bin /tmp/newiso/boot/isolinux/isohdpfx.bin'); //requires apt-install isolinux
//echo shell_exec('cd /tmp && mkisofs -l -J -R -V TC-custom -no-emul-boot -boot-load-size 4 -boot-info-table -b boot/isolinux/isolinux.bin -c boot/isolinux/boot.cat -o '.$outIso.' /tmp/newiso');
echo shell_exec('cd /tmp/newiso && xorriso -as mkisofs -r -V "HN-Specs-TC64 UEFI" -o '.$outIso.' -isohybrid-mbr boot/isolinux/isohdpfx.bin -J -joliet-long -c boot/isolinux/boot.cat -b boot/isolinux/isolinux.bin -boot-load-size 4 -boot-info-table -no-emul-boot -eltorito-alt-boot -e "EFI/BOOT/efiboot.img" -no-emul-boot -isohybrid-gpt-basdat /tmp/newiso');

echo PHP_EOL.'==ISO CREATED=='.PHP_EOL;
echo 'ISO: '.$outIso.PHP_EOL;

?>