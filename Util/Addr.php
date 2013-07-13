<?php

namespace LessPHP\Util;

final class Addr
{
    /**
     * Get remote address/client ip  
     *
     * @return string
     */
    public static function RemoteAddr()
    {
        $ip = false;
        
        if (strlen(@$_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $tmp_ip = explode(',', $addr);
            $ip = $tmp_ip[0];
        }
        
        return($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
}
