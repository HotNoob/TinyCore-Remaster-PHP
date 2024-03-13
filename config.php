<?php

define('image_name', 'HotNoob 2024');
define('compression_level', 4); //0 for quick testing, 4 for max


#region x86 config
$c_arch = 'x86';
$_CONFIG[$c_arch]['tinycore_iso'] = 'tinycore.iso';
$_CONFIG[$c_arch]['tinycore_version'] = '13.x';

$_CONFIG[$c_arch]['out_iso'] = '/tmp/hn-custom_tinycore-image.iso';
$_CONFIG[$c_arch]['image_name'] = image_name.' x86';

$_CONFIG[$c_arch]['temp_folder'] = '/tmp';
$_CONFIG[$c_arch]['temp_mount'] = '/mnt/tmp';  #where tinycore image gets mounted to

define('folder_x86', 'custom_image_files'); #only used for config below
$_CONFIG[$c_arch]['files'] =  folder_x86.'/files'; 
$_CONFIG[$c_arch]['bootfiles'] = folder_x86.'/bootfiles'; 
$_CONFIG[$c_arch]['extensions_folder'] = folder_x86.'/extensions'; 
$_CONFIG[$c_arch]['extensions_txt'] = folder_x86.'/extensions.txt'; 

$_CONFIG[$c_arch]['core_file'] = 'core.gz'; 
$_CONFIG[$c_arch]['sas_driver_hotfix'] = false; 

#endregion x86

#region x64 config
//x64 config / uefi image
$c_arch = 'x86_64';
$_CONFIG[$c_arch]['tinycore_iso'] = 'TinyCorePure64-13.1.iso';
$_CONFIG[$c_arch]['tinycore_version'] = '13.x';

$_CONFIG[$c_arch]['out_iso'] = '/tmp/hn-custom_tinycore-image-64.iso';
$_CONFIG[$c_arch]['image_name'] = image_name.' x64 UEFI';

$_CONFIG[$c_arch]['temp_folder'] = '/tmp';
$_CONFIG[$c_arch]['temp_mount'] = '/mnt/tmp';  #where tinycore image gets mounted to

define('folder_x86_64', 'custom_image_files_64'); #only used for config below
$_CONFIG[$c_arch]['bootfiles'] = folder_x86_64.'/bootfiles'; 
$_CONFIG[$c_arch]['efifiles'] = folder_x86_64.'/EFIfiles'; 
$_CONFIG[$c_arch]['extensions_folder'] = folder_x86_64.'/extensions'; 
$_CONFIG[$c_arch]['extensions_txt'] = folder_x86_64.'/extensions.txt'; 

#using files dir for 32bit config
$_CONFIG[$c_arch]['files'] =  folder_x86.'/files'; 

$_CONFIG[$c_arch]['core_file'] = 'corepure64.gz'; 
$_CONFIG[$c_arch]['sas_driver_hotfix'] = false; 

#endregion x64 config

?>