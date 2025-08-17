<?php




# This is the repository the extensions get downloaded from.


function download_file($url, $file)
{
    //can put curl here if prefered
    file_put_contents($file, file_get_contents($url));
}

function url_exists($url) 
{
    $headers = @get_headers($url);
    return $headers && strpos($headers[0], '200') !== false;
}

function check_md5($md5File, $file)
{
    $md5 = explode(" ", file_get_contents($md5File))[0];
    $file_md5 = md5_file($file);
    if($md5 == $file_md5)
    {
        return true;
    }
    echo $md5."\n";
    echo $file_md5."\n";
    return false;
}   

function get_kernel($tc_version = "13.x", $tc_arch = "x86", $server ="http://repo.tinycorelinux.net")
{

    $info_url = "$server/$tc_version/$tc_arch/tcz/info.lst";

    //get kernel version
    $info = file_get_contents($info_url);
    $a = '';
    if($tc_arch == 'x86_64')
        $a = '64';

    if(str_starts_with($tc_arch, 'armv') || str_starts_with($tc_arch, 'aarch'))
        preg_match_all('/\-(\d+\.\d+\.\d+-piCore.*?)\.tcz/', $info, $matches);
    else
        preg_match_all('/\-(\d+\.\d+\.\d+-tinycore'.$a.')\.tcz/', $info, $matches);
    
    return $matches[1][0];

}

$kernel = '';
function download_package($package, $dir, $tc_version = "13.x", $tc_arch = "x86", $server ="http://repo.tinycorelinux.net")
{
    global $kernel;
    $package = trim($package);
    $package = str_replace('.tcz', '', $package);

    $base_url = "$server/$tc_version/$tc_arch/tcz/";
    
    if(empty($kernel)) //only have todo this once.
        $kernel = get_kernel($tc_version, $tc_arch, $server);

    $package = str_replace('-KERNEL', '-'.$kernel, $package);


    $tcz = $base_url.$package.".tcz";
    $deps = $tcz.".dep";
    $md5 = $tcz.".md5.txt";
    $tczFile = $dir.'/'.$package.".tcz";
    $depsFile = $dir.'/'.$package.".dep";
    $md5File = $dir.'/'.$package.".tcz.md5.txt";


    echo 'Checking Package: '.$package.PHP_EOL;

    $download = true;
    if(file_exists($md5File))
    {
        $download = false;
        if(filemtime($md5File) < time() - (60*60*24)) #md5 is older than one day
        {
            $download = true;
        }
    }


    #md5 check to see if any changes
    if($download & file_exists($md5File) && file_exists($tczFile))
    {
        download_file($md5, $md5File);
        if(check_md5($md5File, $tczFile))
        {
            $download = false;
        }
    }

    if($download)
    {
        echo 'Downloading Package: '.$package.PHP_EOL;
        echo $tcz.PHP_EOL;

        download_file($tcz, $tczFile);
        download_file($md5, $md5File);
        if(!check_md5($md5File, $tczFile))
        {
            echo 'ERROR: MD5 Hash incorrect ';
            unlink($md5File);
            unlink($tczFile);
            return 0;
        }

        #get dependancies
        if(url_exists($deps))
        {
            download_file($deps, $depsFile);
        }
    }

    $count = 0;
    if(file_exists($depsFile))
    {
        $d = file_get_contents($depsFile);
        $deps = explode("\n", $d);
        foreach($deps as $p)
        {
            $p = trim($p);
            if(!empty($p))
            {
                echo 'Found Dependancy: '.$p.PHP_EOL;
                //download package
                $count += download_package($p, $dir, $tc_version, $tc_arch, $server);
            }
        }
    }

    if(file_exists($tczFile))
        $count++;

    return $count;

}

//download_package('pci-utils.tcz', 'custom_image_files/extensions');





?>