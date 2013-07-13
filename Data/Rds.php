<?php

namespace LessPHP\Data;

use LessPHP\Data\Rds\Table;
use LessPHP\Data\Rds\Query;

final class Rds
{
    private static $confs = array();
    private static $conns = array();
    private static $insts = array();
    private static $tbdef = null;

    /**
     * Importing Configuration Information.
     *
     * @param  string $datasetid
     * @param  string $filename
     * @return void
     * @throws Exception
     */
    public static function Config($filename, $datasetid)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \Exception(sprintf(
                "File '%s' doesn't exist or not readable", $filename));
        }
        
        $config = json_decode(file_get_contents($filename), false);
        if (json_last_error()) {
            throw new \Exception("A valid json string");
        }

        self::$confs[$datasetid] = $config;
        self::$tbdef = $datasetid;
    }

    public static function set($key, $val)
    {
        self::$confs[$key] = $val;
    }
    
    public static function setAll($var)
    {
        foreach ($var as $key => $val) {
            self::$confs[$key] = $val;
        }
    }
    
    public static function Table($table, $dbset = null)
    {
        if ($dbset == null) {
            $dbset = self::$tbdef;
        }

        if ($dbset == null) {
            return false;
        }

        if (!isset(self::$insts[$table])) {
            self::$insts[$table] = new Table($dbset, $table);
        }
        
        return self::$insts[$table];
    }
    
    public static function getConn($dbset)
    {
        if (!isset(self::$conns[$dbset])) {
            
            if (!isset(self::$confs[$dbset])) {
                return false;
            }
            
            $c = self::$confs[$dbset];
            $o = array();
            
            //if ($c['adapter'] == 'mysql') {
                $o['1002'] = "SET NAMES 'utf8'";
                $o[\PDO::ATTR_PERSISTENT] = true;
            //}
            $dsn = "mysql:host={$c->accessaddr};dbname={$c->datainstid}";

            self::$conns[$dbset] = new \PDO($dsn, $c->accessuser, $c->accesspass, $o);
        }
        
        return self::$conns[$dbset];
    }
}
