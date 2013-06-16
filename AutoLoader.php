<?php

function lessAutoload($class)
{
    $filename = __DIR__ . "/../"
        . trim(preg_replace(array('/\\\+/', "/\/+/", "/_/"), "/", $class))
        . ".php";
    
    include $filename;
}

spl_autoload_register("lessAutoload");


function lessRegistry($k, $v = null)
{
	static $o;
	return (func_num_args() > 1 ? $o[$k] = $v : (isset($o[$k]) ? $o[$k] : null));	
}

