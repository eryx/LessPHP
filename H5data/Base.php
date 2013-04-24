<?php

namespace LessPHP\H5data;

class Base
{
    public static $kpr  = null;
    public static $inst = null;
    
    public $data_instance = null;
    public $data_table    = null;
    public $data_shard    = null;
   
    public function __construct($tableid = null)
    {
        $longopts = array(
            "data_instance::",
            "data_shard::",
        );

        $opts = getopt(null, $longopts);
        if (isset($opts['data_instance'])) {
            $this->data_instance = $opts['data_instance'];
        }
        if (isset($opts['data_shard'])) {
            $this->data_instance = $opts['data_shard'];
        }
       
        if ($tableid !== null) {
            $this->data_table = $tableid;
        }
    }
}