<?php
/**
 * Hooto Web library
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   LessPHP
 * @package    LessPHP_Util_String
 * @copyright  Copyright 2013 HOOTO.COM
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
 
/** ensure this file is being included by a parent file */
defined('SYS_ROOT') or die('Access Denied!');

/**
 * Class LessPHP_Util_String
 *
 * @category   LessPHP
 * @package    LessPHP_Util_String
 * @copyright  Copyright 2013 HOOTO.COM
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
class LessPHP_Util_String
{
    public static function cutstr($string, $length, $dot = ' ...') 
    {
        if (strlen($string) <= $length) {
            return $string;
        }
    
        $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
        $strcut = '';
        if (true) { // fix utf-8
            $n = $tn = $noc = 0;
            while ($n < strlen($string)) {
                $t = ord($string[$n]);
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1; $n++; $noc++;
                } elseif (194 <= $t && $t <= 223) {
                    $tn = 2; $n += 2; $noc += 2;
                } elseif (224 <= $t && $t < 239) {
                    $tn = 3; $n += 3; $noc += 2;
                } elseif (240 <= $t && $t <= 247) {
                    $tn = 4; $n += 4; $noc += 2;
                } elseif (248 <= $t && $t <= 251) {
                    $tn = 5; $n += 5; $noc += 2;
                } elseif ($t == 252 || $t == 253) {
                    $tn = 6; $n += 6; $noc += 2;
                } else {
                    $n++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $strcut = substr($string, 0, $n);
        } else {
            for ($i = 0; $i < $length - strlen($dot) - 1; $i++) {
                $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
            }
        }
        // $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
        return $strcut.$dot;
    }
    
    public static function removeBOM($str)
    {
        if (substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
            $str = substr($str, 3);
        }
    
        return $str;
    }
    
    public static function rand($len = 12, $t = 1)
    {
        if ($t == 0) {
            $s = mt_rand(1,9);
            $c = str_split('0123456789');
        } else if ($t == 1) {
            $s = chr(mt_rand(97,102));
            $c = str_split('0123456789abcdef');
        } else {
            $s = chr(mt_rand(97,122));
            $c = str_split('0123456789abcdefghijklmnopqrstuvwxyz');
        }
        $len--;
        for ($i=0; $i<$len; $i++) {
            $s .= $c[array_rand($c)];
        }
        return$s;
    }
  
    /**
     * Generates a Universally Unique IDentifier, version 4.
     *
     * RFC 4122 (http://www.ietf.org/rfc/rfc4122.txt) defines a special type of Globally
     * Unique IDentifiers (GUID), as well as several methods for producing them. One
     * such method, described in section 4.4, is based on truly random or pseudo-random
     * number generators, and is therefore implementable in a language like PHP.
     *
     * We choose to produce pseudo-random numbers with the Mersenne Twister, and to always
     * limit single generated numbers to 16 bits (ie. the decimal value 65535). That is
     * because, even on 32-bit systems, PHP's RAND_MAX will often be the maximum     *signed*
     * value, with only the equivalent of 31 significant bits. Producing two 16-bit random
     * numbers to make up a 32-bit one is less efficient, but guarantees that all 32 bits
     * are random.
     *
     * The algorithm for version 4 UUIDs (ie. those based on random number generators)
     * states that all 128 bits separated into the various fields (32 bits, 16 bits, 16 bits,
     * 8 bits and 8 bits, 48 bits) should be random, except : (a) the version number should
     * be the last 4 bits in the 3rd field, and (b) bits 6 and 7 of the 4th field should
     * be 01. We try to conform to that definition as efficiently as possible, generating
     * smaller values where possible, and minimizing the number of base conversions.
     *
     * @copyright   Copyright (c) CFD Labs, 2006. This function may be used freely for
     *              any purpose ; it is distributed without any form of warranty whatsoever.
     * @author      David Holmes <dholmes@cfdsoftware.net>
     *
     * @return  string  A UUID, made up of 32 hex digits and 4 hyphens.
     */
    public static function uuid()
    {
        // The field names refer to RFC 4122 section 4.1.2
        return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
            mt_rand(0, 65535), // 16 bits for "time_mid"
            mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
                // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
                // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
                // 8 bits for "clk_seq_low"
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node" 
        );
    }
    
    /**
     * Check the validity of a given uuid
     *
     * ([0-9a-f]{8})-([0-9a-f]{4})-([0-9a-f]{4})-([0-9a-f]{4})-([0-9a-f]{12})
     * ex. 00000000-0000-0000-0000-000000000000
     *
     * @param string $uuid
     * @return bool
     */
    public static function uuidIsValid($uuid)
    {
        $uuid = strtolower($uuid);
        if (preg_match('/^([0-9a-f]{8})-([0-9a-f]{4})-([0-9a-f]{4})-([0-9a-f]{4})-([0-9a-f]{12})$/', $uuid)) {
            return true;
        } else {
            return false;
        }
    }
}
