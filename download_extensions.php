<?php

chdir(__DIR__);
require_once('config.php');
require_once('download_extension.php');

echo PHP_EOL.'==DOWNLOAD EXTENSIONS=='.PHP_EOL;
echo __DIR__.'/'.$_CONFIG[$arch]['extensions_folder'].PHP_EOL;

$extensions = explode("\n",file_get_contents($_CONFIG[$arch]['extensions_txt']));


$c = 0;
foreach($extensions as $ext)
{
    $ext = trim($ext);
    if(empty($ext))
        continue;

    if(substr( $ext, 0, 1) === '#') //skip comments
        continue;
 

    $c += download_package($ext, $_CONFIG[$arch]['extensions_folder'], $_CONFIG[$arch]['tinycore_version'], $arch);
}

echo PHP_EOL.$c.' Extensions Downloaded'.PHP_EOL;


?>