<?php

namespace LessPHP\H5data;

use LessPHP\H5data\Base;
use LessPHP\H5keeper;

class BigTable extends Base
{
    private $v2n = array();
    private $n2v = array();

    private $conn = array();

    public static function NewInstance($tableid)
    {
        return new BigTable($tableid);
    }

    public function __construct($tableid)
    {
        parent::__construct($tableid);

        self::$kpr = new LessKeeper\Keeper("127.0.0.1:9530");
        
        $inst = self::$kpr->Get("/h5db/inst/". $this->data_instance);
        self::$inst = json_decode($inst, true);

        foreach (self::$inst['ShardMap'] as $key => $val) {
            
            $this->v2n[$key][] = $val;
            $this->n2v[$val][] = $key;
        }

        ksort($this->v2n, SORT_REGULAR);

        //print_r($this);
    }

    private function connPull($node)
    {
        if (!isset($this->conn[$node])) {
        
            try {

                $drvopts = array(
                    '1002' => "SET NAMES 'utf8'",
                );

                $cfg = array(
                    'dsn'  => 'mysql:host=127.0.0.1;dbname='. $this->data_instance,
                    'user' => 'root',
                    'pass' => '123456',
                );

                $pdo = new \PDO($cfg['dsn'], $cfg['user'], $cfg['pass'], $drvopts);
                
                $this->conn[$node] = $pdo;
                
            } catch (PDOException $e) {
                throw $e;
            }
        }

        return $this->conn[$node];
    }

    public function Insert($items, $pid = 'id')
    {
        if (!isset($items[0])) {
            $items = array($items);
        }

        foreach ($items as $item) {
        
            if (!isset($item[$pid])) {
                continue;
            }
            
            $keys = array_keys($item);
            $bind = array();
            foreach ($keys as $k => $v) {
                $keys[$k] = "`$v`";
                $bind[] = '?';
            }
            $keys = implode(",", $keys);
            $bind = implode(",", $bind);
            
            $vals = array_values($item);
            
            $nm = $this->mapNode($item[$pid]);

            $sql  = "INSERT INTO `{$this->data_instance}`.`{$this->data_table}_{$nm[1]}` ($keys) ";
            $sql .= "VALUES ($bind)";

            try {

                $this->connPull($nm[0])->prepare($sql)->execute($vals);

            } catch (Exception $e) {
                throw $e;
            }
        }
        
        return true;
    }
    
    public function Query()
    {
        return self::$opts;
    }

    public function Fetch($keys, $pid = 'id')
    {
        if (!is_array($keys) || !isset($keys[0])) {
            $keys = array($keys);
        }

        $ret = array();

        try {
        
            $_bind = array();

            foreach ($keys as $val) {
                $nm = $this->mapNode($val);
                $_bind[$nm[0]][$nm[1]][] = "'$val'";
            }

            $inst  = $this->data_instance;
            
            foreach ($_bind as $node => $vnodes) {
                
                foreach ($vnodes as $vnode => $ids) {

                    $ids = implode(",", $ids);
                    
                    $sql  = "SELECT * FROM `{$this->data_instance}`.`{$this->data_table}_{$vnode}` ";
                    $sql .= "WHERE $pid IN ($ids)";
                    
                    $sth = $this->connPull($node)->query($sql);

                    $_ret = $sth->fetchAll();

                    if (isset($_ret[0])) {
                        $ret = array_merge($ret, $_ret);
                    }
                }
            }
            
        } catch (Exception $e) {
            throw $e;
        }

        return $ret;
    }

    public function Delete($keys, $pid = 'id')
    {
        if (!is_array($keys) || !isset($keys[0])) {
            $keys = array($keys);
        }

        try {
        
            $_bind = array();

            foreach ($keys as $val) {
                $nm = $this->mapNode($val);
                $_bind[$nm[0]][$nm[1]][] = "'$val'";
            }

            $inst  = $this->data_instance;
            
            foreach ($_bind as $node => $vnodes) {
                
                foreach ($vnodes as $vnode => $ids) {

                    $ids = implode(",", $ids);
                    
                    $sql = "DELETE FROM `{$this->data_instance}`.`{$this->data_table}_{$vnode}` ";
                    $sql.= "WHERE $pid IN ($ids)";
                    
                    $sth = $this->connPull($node)->prepare($sql);

                    $sth->execute();
                }
            }
            
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function Update($item, $pid = 'id')
    {
        if (!isset($item[$pid])) {
            throw new Exception();
        }
        $id = $item[$pid];
        unset($item[$pid]);
        
        $nm = $this->mapNode($id);
        
        $keys = array_keys($item);
        $bind = array();
        $cols  = null;
        
        foreach ($keys as $k => $v) {
            if ($cols == null) {
                $cols = " $v = ?";
            } else {
                $cols .= ",$v = ?";
            }
        }
        
        $sql = "UPDATE `{$this->data_instance}`.`{$this->data_table}_{$nm[1]}` ";
        $sql.= "SET {$cols} WHERE $pid = ?";
        
        $vals = array_values($item);
        $vals[] = $id;
            
        try {

            $this->connPull($nm[0])->prepare($sql)->execute($vals);

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function hash($key)
    {
        if (preg_match('/^([0-9a-zA-Z]{1,32}).([0-9a-zA-Z]{0,32})$/', $key, $mat)) {
            return hexdec(substr(md5($mat[1]), 0, 4));
        } else if (preg_match('/^([0-9a-zA-Z]{40})$/', $key)) {
            return hexdec(substr($key, 0, 4));
        } else {
            return hexdec(substr(md5($key), 0, 4));
        }
    }

    private function mapNode($key)
    {
        $key = $this->hash($key);

        $start = 0;
        foreach ($this->v2n as $v => $n) {
            
            if ($start == 0 && $v == 0) { 
                $start = $n;
            }

            if ($v >= $key) {
                shuffle($n);
                return array(current($n), $v);
            }
        }

        return array($start, 0);
    }
}