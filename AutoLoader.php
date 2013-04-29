<?php

function lessAutoload($class)
{
    $filename = __DIR__ . "/../"
        . trim(preg_replace(array('/\\\+/', "/\/+/", "/_/"), "/", $class))
        . ".php";
    
    include $filename;
}

spl_autoload_register("lessAutoload");

