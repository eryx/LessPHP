<?php

namespace LessPHP\Util;

final class Directory
{
    public static function listFiles($dir, $sub = NULL)
    {
        $a = array();
        $p = ($sub ? opendir("$dir/$sub") : opendir($dir));
        if ($p) {

            while (($file = readdir($p)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $tmp = realpath("$dir/$sub/$file");
                if (is_dir($tmp)) {
                    $a = array_merge($a, self::listFiles($dir, $sub.'/'.$file));
                } else {
                    $a[] = ($sub ? $sub."/" : "").$file;
                }
            }

            closedir($p);
        }
        
        return $a;
    }
    
    /**
     * 
     *
     * @param string $uuid
     * @return bool
     */
    public static function mkdir($path, $mode = 0777)
    {
        $dirs = explode('/', $path);
        $dirpath = '';
        foreach ($dirs as $directory) {

            if ($directory == null || $directory == "") {
                continue;
            } else if ($directory == ".." || $directory == ".") {
                $dirpath .= $directory;
            } else {
                $dirpath .= '/'.$directory;
            }

            if (!is_dir($dirpath)) {
                mkdir($dirpath, $mode);
            }
        }
    }

    /**
     *  
     *
     * @param string $uuid
     * @return bool
     */
    public static function mkfiledir($path, $mode = 0777)
    {
        $path = pathinfo($path);
        $path = $path['dirname'];
        self::mkdir($path, $mode);
    }
    
    public static function rmdir($path)
    {
        if (!$p = opendir($path)) {
            return false;
        }
        
        while (($file = readdir($p)) !== false) {
            
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir($path.'/'.$file)) {
                self::rmdir($path.'/'.$file);
            } else {
                unlink($path.'/'.$file);
            }            
        }

        closedir($p);        
        rmdir($path);
        
        return true;
    }
}
