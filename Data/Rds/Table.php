<?php


namespace LessPHP\Data\Rds;

use LessPHP\Data\Rds;
use LessPHP\Data\Rds\Query;

final class Table
{
    private $dbname = null;
    // * @param    string $tableName table name
    private $table = null;
    
    public function __construct($dbset, $table)
    {
        $this->dbname = $dbset;
        $this->table  = $table;
    }
    
    public function fetch($value, $key = 'id')
    {
        try {

            $query = $this->select()
                ->where("$key = ?", $value)
                ->assemble($this->table);
            
            $cn = Rds::getConn($this->dbname);
            
            $sth = $cn->prepare($query);
            $sth->execute();
            
            $rs = $sth->fetch(\PDO::FETCH_ASSOC);
        
        } catch(\Exception $e) {
            throw $e;
        }

        return $rs;
    }

    public function insert($entry)
    {
        $keys = array_keys((array)$entry);
        $bind = array();
        foreach ($keys as $k => $v) {
            $keys[$k] = "`$v`";
            $bind[] = '?';
        }
        $keys = implode(",", $keys);
        $bind = implode(",", $bind);
            
        $vals = array_values((array)$entry);
            
        $sql = "INSERT INTO `{$this->table}` ($keys) VALUES ($bind)";

        try {

            $cn = Rds::getConn($this->dbname);

            $sth = $cn->prepare($sql);
            
            if (!$sth->execute($vals)) {
                $arr = $sth->errorInfo();
                throw new \Exception($arr[2]);
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function replace($entry)
    {

    }
    
    public function update($entry, $where)
    {            
        $keys = array_keys((array)$entry);
        $sql = NULL;//
        foreach ($keys as $k => $v) {
            if ($sql == NULL) {
                $sql = " $v = ?";
            } else {
                $sql .= ",$v = ?";
            }
        }    
        $vals = array_values((array)$entry);
        
        $sqlw = NULL;//
        foreach ($where as $k => $v) {
            if ($sqlw == NULL) {
                $sqlw = " $k = ?";
            } else {
                $sqlw .= " AND $k = ?";
            }
            $vals[] = $v;
        }
        
        $sql = "UPDATE `{$this->table}` SET {$sql} WHERE {$sqlw}";

        try {

            $cn = Rds::getConn($this->dbname);

            $sth = $cn->prepare($sql);
            
            if (!$sth->execute($vals)) {
                $arr = $sth->errorInfo();
                throw new \Exception($arr[2]);
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function delete($key, $pid = 'id')
    {
        try {
            $ids = explode(",", $key);

            $sql = "DELETE FROM `{$this->table}` WHERE $pid IN (?)";

            $cn = Rds::getConn($this->dbname);
            $sth = $cn->prepare($sql);
            $sth->execute($ids);

        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function deleteWhere($where)
    {
        $sqlw = NULL;//
        foreach ($where as $k => $v) {
            if ($sqlw == NULL) {
                $sqlw = " $k ";
            } else {
                $sqlw .= " AND $k";
            }
            $vals[] = $v;
        }
        
        try {

            $sql = "DELETE FROM `{$this->table}` WHERE {$sqlw}";

            $cn = Rds::getConn($this->dbname);
            $sth = $cn->prepare($sql);
            $sth->execute($vals);

        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Query for documents stored in the document service. If a string is passed in
     * $query, the query string will be passed directly to the service.
     *
     * @param  string $query
     * @param  array $options
     * @return array Rds
     */
    public function query($query, $options = null)
    {
        try {
        
            if ($query instanceof Query) {
                $query = $query->assemble($this->table);
            }
            
            $sth = Rds::getConn($this->dbname)->query($query);
            
            $rs = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
        } catch(\Exception $e) {
            throw $e;
        }

        return $rs;
    }
    
    /**
     * Create query statement
     *
     * @param  string $fields
     * @return Query
     */
    public function select($fields = null)
    {
        $query = new Query();
        $query->select($fields);
        
        return $query;
    }
}
