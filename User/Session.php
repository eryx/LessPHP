<?php

namespace LessPHP\User;

use LessPHP\H5keeper\Client;


final class Session
{   
    protected static $_instance = null;
    
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
    public static function isLogin($uid = '0')
    {
        if ($uid == '0') {
            return (self::Instance()->uid != '0' ? true : false);
        }

        return (($uid === self::Instance()->uid) ? true : false);
    }
}
