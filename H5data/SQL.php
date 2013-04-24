<?php

namespace LessPHP\H5data;

use LessPHP\H5data\Base;

class SQL extends Base
{
    public static function NewInstance($tableid)
    {
        return new BigTable($tableid);
    }

    public function insert($table, $feed, $pid = 'id')
    {

    }
    
    public function getList()
    {
        return self::$opts;
    }

    public function getById($table, $sql = NULL, $id, $pid = 'id')
    {

    }

    public function getByIds($table, $bind, $pid = 'id')
    {

    }

    public function delByIds($table, $bind, $pid = 'id')
    {

    }

    public function updateEntry($table, $entry, $pid = 'id')
    {

    }
}