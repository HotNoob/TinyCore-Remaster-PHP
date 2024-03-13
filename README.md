# TinyCore-Remaster-PHP
Simple PHP Script for creating custom tiny core images ( remastering )

The images made through this script are PXE BOOTABLE.

```
KERNEL    /kernels/memdisk
INITRD    /images/you-tinycore.iso
APPEND    iso raw
```

# usage
1.  Download TinyCore Iso
2.  Install Requirements
3.  Edit config.php
4.  run php script
    ```
    php -f build-tinycore.php
    ```
    or to specify arch. currently only x86 works. 
    ```
    php -f build-tinycore.php x86_64
    ```
5. Enjoy



### requirements
on the todo to make a list. 
```
rsync
xorriso
advdef
unsquashfs
```

### isohdpfx.bin
this is from: apt-install isolinux && cp /usr/lib/ISOLINUX/isohdpfx.bin custom_image_files_64/bootfiles/isolinux/isohdpfx.bin
put in repo for convenience. 
