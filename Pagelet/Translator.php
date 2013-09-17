<?php

namespace LessPHP\Pagelet;


class Translator
{
    private static $i18n   = array();
    private static $locale = "zh_CN";
    
    public static function translationFileAdd($locale, $file)
    {
        $ls = require $file;
        if (!is_array($ls)) {
            return;
        }
        
        if (!isset(self::$i18n[$locale])) {

            self::$i18n[$locale] = $ls;
        } else {
            
            self::$i18n[$locale] = array_merge(self::$i18n[$locale], $ls);
        }
    }
    
    public static function T($string, $locale = null)
    {
        if ($locale === null) {
            $locale = self::$locale;
        }
        
        if (isset(self::$i18n[$locale])
            && isset(self::$i18n[$locale][$string])) {
        
            return self::$i18n[$locale][$string];
        }
        
        return $string;
    }
}
