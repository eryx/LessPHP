<?php

namespace LessPHP\User;

use LessPHP\H5keeper\Client;


final class Session
{   
    protected static $_instance = null;
    protected static $_navmenus = null;
    
    // TODO
    public static function Instance()
    {
        if (self::$_instance === null) {

            if (isset($_COOKIE['access_token'])) {
                $sid = trim($_COOKIE['access_token']);
            } else {
                $sid = null;
            }

            if ($sid != null) {

                try {

                    $kpr = new Client();

                    $rs = $kpr->LocalNodeGet("/u/s/{$sid}");

                    $rs = json_decode($rs->body, false);

                    if (isset($rs->id)) {
                        self::$_instance = $rs;
                    }

                } catch (\Exception $e) {

                }
            }
        }

        return self::$_instance;
    }

    //
    public static function IsLogin()
    {
        if (isset(self::Instance()->uid) && self::Instance()->uid != '0') {
            return true;
        } else {
            setcookie("access_token", '', 1, '/');
            @session_destroy();
        }

        return false;
    }
    
    // TODO
    public static function NavMenus($uname)
    {
        if (!self::IsLogin()) {
            return array();
        }
        
        if ($uname != 'ue' && $uname != self::Instance()->uname) {
            return array();
        }
        
        if (self::$_navmenus !== null) {
            return self::$_navmenus;
        }
        
        $ms = array();
        
        try {

            $kpr = new Client();

            $rs = $kpr->NodeListAndGet("/app/ui/{$uname}");            

            foreach ($rs->elems as $pkg) {
                
                $pkg = json_decode($pkg->body, false);
                
                $props = explode(",", $pkg->props);
                
                if (!in_array("pagelet", $props)) {
                    continue;
                }
                
                $ms[] = (object)array(
                    'name' => $pkg->name,
                    'projid' => $pkg->projid,
                );
            }
                    
            self::$_navmenus = $ms;

        } catch (\Exception $e) {

        }
        
        return $ms;
    }
}
