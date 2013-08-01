<?php

namespace LessPHP\Encoding;

class Json
{
    /**
     * Pretty-print JSON string
     * 
     * This function is a part of the Zend Framework
     *
     * Use 'indent' option to select indentation string - by default it's a tab
     * 
     * @param string $json Original JSON string
     * @param array $options Encoding options
     * @return string
     */
    public static function prettyPrint($json, $options = array())
    {
        if (is_array($json)) {
            $json = json_encode($json);
        }
        $tokens = preg_split('|([\{\}\]\[,])|', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        $indent = 0;

        $ind = '  ';

        // override the defined indent setting with the supplied option
        if (isset($options['indent'])) {
            $ind = $options['indent'];
        }

        $inLiteral = false;
        foreach ($tokens as $token) {

            if ($token == "") {
                continue;
            }

            $prefix = str_repeat($ind, $indent);
            if (!$inLiteral && ($token == "{" || $token == "[")) {
                $indent++;
                if ($result != "" && $result[strlen($result)-1] == "\n") {
                    $result .= $prefix;
                }
                $result .= "$token\n";
            } else if (!$inLiteral && ($token == "}" || $token == "]")) {
                $indent--;
                $prefix = str_repeat($ind, $indent);
                $result .= "\n$prefix$token";                
            } else if (!$inLiteral && $token == ",") {
                $result .= "$token\n";
            } else {
                $result .= ($inLiteral ?  '' : $prefix) . $token;

                // Count # of unescaped double-quotes in token, subtract # of
                // escaped double-quotes and if the result is odd then we are
                // inside a string literal
                if ((substr_count($token, "\"") - substr_count($token, "\\\"")) % 2 != 0) {
                    $inLiteral = !$inLiteral;
                }
            }
        }
        
        return $result;
        //return str_replace(array('":'), array('": '), $result);
   }
}
