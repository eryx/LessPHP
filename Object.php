<?php

class LessPHP_Object
{
    public function __construct($array = null)
    {
        if ($array !== null) 
            foreach ((array)$array as $key => $val)
                $this->$key = $val;
    }
    
    public function __set($key, $val)
    {
        if ('_' != substr($key, 0, 1)) $this->$key = $val;
    }
    
    public function __get($key)
    {
        return null;
    }
}
