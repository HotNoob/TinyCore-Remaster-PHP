<?php

//reqs
//apt install mkisofs advancecomp

chdir(__DIR__);
require_once('config.php');

$arch = 'x86';
if(!empty($argv[1]))
    $arch = strtolower($argv[1]);

if(empty($_CONFIG[$arch]))
    {
        echo 'Invalid Arch; use: '.implode(',', array_keys($_CONFIG)); 
        exit;
    }

function clean()
{
    global $_CONFIG;
    global $arch;

    echo '==CLEAN==';
    echo shell_exec('rm -rf '.$_CONFIG[$arch]['temp_folder'].'/newiso');
    echo shell_exec('sudo umount '.$_CONFIG[$arch]['temp_mount']);
    echo shell_exec('rm -rf '.$_CONFIG[$arch]['temp_mount']);
    echo shell_exec('rm -rf '.$_CONFIG[$arch]['temp_folder'].'/extract');
    echo '==CLEAN DONE==';
}

$fileDir = $_CONFIG[$arch]['files'];
$outIso = $_CONFIG[$arch]['out_iso'];
if(file_exists($outIso))
{
    echo '==ISO EXISTS==';
    echo shell_exec('rm -rf '.$_CONFIG[$arch]['out_iso'].'');
}

clean();

echo PHP_EOL.'==EXTRACT=='.PHP_EOL;
echo shell_exec('sudo mkdir '.$_CONFIG[$arch]['temp_mount']);
echo shell_exec('sudo mount '.$_CONFIG[$arch]['tinycore_iso'].' '.$_CONFIG[$arch]['temp_mount'].' -o loop,ro');
echo shell_exec('cp -a '.$_CONFIG[$arch]['temp_mount'].'/boot /tmp');

#efi only
if(is_dir($_CONFIG[$arch]['temp_mount'].'/cde'))
    echo shell_exec('cp -a '.$_CONFIG[$arch]['temp_mount'].'/cde /tmp');

if(is_dir($_CONFIG[$arch]['temp_mount'].'/EFI'))
    echo shell_exec('cp -a '.$_CONFIG[$arch]['temp_mount'].'/EFI /tmp');



echo shell_exec('mv '.$_CONFIG[$arch]['temp_folder'].'/boot/'.$_CONFIG[$arch]['core_file'].' '.$_CONFIG[$arch]['temp_folder'].'');
echo shell_exec('sudo umount '.$_CONFIG[$arch]['temp_mount']);

echo shell_exec('mkdir '.$_CONFIG[$arch]['temp_folder'].'/extract');
echo shell_exec('cd '.$_CONFIG[$arch]['temp_folder'].'/extract && zcat '.$_CONFIG[$arch]['temp_folder'].'/'.$_CONFIG[$arch]['core_file'].' | sudo cpio -i -H newc -d');

require_once('download_extensions.php');

echo PHP_EOL.'==INSTALL EXTENSIONS=='.PHP_EOL;

$files = scandir($_CONFIG[$arch]['extensions_folder']);

foreach($files as $file)
{
        $ext =  pathinfo($file, PATHINFO_EXTENSION);
        if($ext == 'tcz')
        {
            echo 'Installing : '.$file.PHP_EOL;
            shell_exec('unsquashfs -f -d '.$_CONFIG[$arch]['temp_folder'].'/extract '.$_CONFIG[$arch]['extensions_folder'].'/'.$file);
        }
}

echo PHP_EOL.'==EDIT BOOT FILES=='.PHP_EOL;
#/ is very important for rsync
echo shell_exec('rsync -c -a '.$_CONFIG[$arch]['bootfiles'].'/ '.$_CONFIG[$arch]['temp_folder'].'/boot');
if(!empty($_CONFIG[$arch]['efifiles']))
    echo shell_exec('rsync -c -a '.$_CONFIG[$arch]['efifiles'].'/ '.$_CONFIG[$arch]['temp_folder'].'/EFI');



echo PHP_EOL.'==EDIT FILES=='.PHP_EOL;
echo shell_exec('rsync -c -a '.$_CONFIG[$arch]['files'].'/ '.$_CONFIG[$arch]['temp_folder'].'/extract');



if(!empty($_CONFIG[$arch]['sas_driver_hotfix']) && $_CONFIG[$arch]['sas_driver_hotfix'])
{
    //driver hotfix
    echo PHP_EOL.'==MOVING DRIVERS=='.PHP_EOL;
    $tinyCoreKernel = get_kernel($_CONFIG[$arch]['tinycore_version'], $arch);
    shell_exec('mkdir '.$_CONFIG[$arch]['temp_folder'].'/extract/lib/modules/'.$tinyCoreKernel.'/kernel/drivers/scsi');
    shell_exec('cp -a '.$_CONFIG[$arch]['temp_folder'].'/extract/usr/local/lib/modules/'.$tinyCoreKernel.'/kernel/drivers/scsi/megaraid '.$extractDir.'/lib/modules/'.$tinyCoreKernel.'/kernel/drivers/scsi');
    shell_exec('cp -a '.$_CONFIG[$arch]['temp_folder'].'/extract/usr/local/lib/modules/'.$tinyCoreKernel.'/kernel/drivers/scsi/mpt3sas '.$extractDir.'/lib/modules/'.$tinyCoreKernel.'/kernel/drivers/scsi');
    shell_exec('cp -a '.$_CONFIG[$arch]['temp_folder'].'/extract/usr/local/lib/modules/'.$tinyCoreKernel.'/kernel/drivers/scsi/scsi_transport_sas.ko.gz '.$extractDir.'/lib/modules/'.$tinyCoreKernel.'/kernel/drivers/scsi');
}


#repacking
echo PHP_EOL.'==PACKING '.$_CONFIG[$arch]['core_file'].'=='.PHP_EOL;
echo shell_exec('sudo ldconfig -r '.$_CONFIG[$arch]['temp_folder'].'/extract');
echo shell_exec('cd '.$_CONFIG[$arch]['temp_folder'].'/extract && sudo find | sudo cpio -o -H newc | gzip -2 > '.$_CONFIG[$arch]['temp_folder'].'/'.$_CONFIG[$arch]['core_file']);

echo PHP_EOL.'==REPACKING '.$_CONFIG[$arch]['core_file'].'=='.PHP_EOL;
//z4 for prod. z0 for dev
echo shell_exec('cd '.$_CONFIG[$arch]['temp_folder'].'/ && advdef -f -z'.compression_level.' '.$_CONFIG[$arch]['temp_folder'].'/'.$_CONFIG[$arch]['core_file']);


sleep(7);

#make iso
echo PHP_EOL.'==MAKING ISO=='.PHP_EOL;
//cd /tmp
echo shell_exec('mv '.$_CONFIG[$arch]['temp_folder'].'/'.$_CONFIG[$arch]['core_file'].' '.$_CONFIG[$arch]['temp_folder'].'/boot');
echo shell_exec('mkdir '.$_CONFIG[$arch]['temp_folder'].'/newiso');
echo shell_exec('mv '.$_CONFIG[$arch]['temp_folder'].'/boot '.$_CONFIG[$arch]['temp_folder'].'/newiso');



if($arch == 'x86_64')
{
    echo shell_exec('mv '.$_CONFIG[$arch]['temp_folder'].'/cde '.$_CONFIG[$arch]['temp_folder'].'/newiso');
    echo shell_exec('mv '.$_CONFIG[$arch]['temp_folder'].'/EFI '.$_CONFIG[$arch]['temp_folder'].'/newiso');
    //uefi version
    echo shell_exec('cd '.$_CONFIG[$arch]['temp_folder'].' && xorriso -as mkisofs -r -V "'.$_CONFIG[$arch]['image_name'].'" -o '.$outIso.' -isohybrid-mbr newiso/boot/isolinux/isohdpfx.bin -J -joliet-long -c boot/isolinux/boot.cat -b boot/isolinux/isolinux.bin -boot-load-size 4 -boot-info-table -no-emul-boot -eltorito-alt-boot -e "EFI/BOOT/efiboot.img" -no-emul-boot -isohybrid-gpt-basdat '.$_CONFIG[$arch]['temp_folder'].'/newiso');
}
else
    echo shell_exec('cd '.$_CONFIG[$arch]['temp_folder'].' && mkisofs -l -J -R -V "'.$_CONFIG[$arch]['image_name'].'" -no-emul-boot -boot-load-size 4 -boot-info-table -b boot/isolinux/isolinux.bin -c boot/isolinux/boot.cat -o '.$outIso.' '.$_CONFIG[$arch]['temp_folder'].'/newiso');

if(file_exists($outIso))
{
    clean();

    echo PHP_EOL.'==ISO CREATED=='.PHP_EOL;
    echo 'ISO: '.$outIso.PHP_EOL;
    exit;
}

echo PHP_EOL.'==FAILED TO CREATE ISO=='.PHP_EOL;



?>