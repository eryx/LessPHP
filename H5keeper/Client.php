<?php

namespace LessPHP\H5keeper;

use LessPHP\Net\Http;

class Client
{
    const StatusOK                  = "0";
    const StatusErr                 = "1";

    const ReplyOK                   = 0;
    const ReplyError                = 1;
    const ReplyTimeout              = 2;
    const ReplyNotExist             = 3;
    const ReplyAccessDenied         = 4;
    
    const ReplyNil                  = 10;
    const ReplyInteger              = 11;
    const ReplyString               = 12;
    const ReplyJson                 = 13;
    const ReplyMulti                = 14;
    const ReplyWatch                = 15;

    const EventNone                 = "10";
    const EventNodeCreated          = "11";
    const EventNodeDeleted          = "12";
    const EventNodeDataChanged      = "13";
    const EventNodeChildrenChanged  = "14";

    // Http connection to the h5keeper service
    // @var resource
    // @access private
    private $http;
    
    // Creates a connection to the h5keeper at the address specified by {@link $dsn}.
    // The default connection is to the server running on localhost on port 9528.
    // @param string $dsn The data source name of the h5keeper server
    // @param float $timeout The connection timeout in seconds
    public function __construct($dsn = '127.0.0.1:9528', $timeout = null)
    {
        $this->http = new Http("http://{$dsn}/h5keeper/api");
    }

    private function request($obj)
    {
        $st = $this->http->Post(json_encode($obj));
        if ($st != 200) {
            return false;
        }

        return json_decode($this->http->GetBody(), false);
    }
    
    private function _nodegen($method, $path)
    {
        $req = array(
            'method' => $method,
            'path'   => $path
        );
        return $this->request($req);
    }
    
    private function _nodegenset($method, $path, $val, $ttl = 0)
    {
        $req = array(
            'method' => $method,
            'path'   => $path,
            'val'    => "".$val,
            'ttl'    => intval($ttl)
        );
        return $this->request($req);
    }
    
    public function NodeGet($path)
    {
        return $this->_nodegen('get', $path);
    }

    public function NodeList($path)
    {
        return $this->_nodegen('list', $path);
    }    
    
    public function NodeDel($path)
    {
        return $this->_nodegen('del', $path);
    }
    
    public function NodeSet($path, $val)
    {
        return $this->_nodegenset('set', $path, $val);
    }
    
    public function LocalNodeGet($path)
    {
        return $this->_nodegen('locget', $path);
    }

    public function LocalNodeList($path)
    {
        return $this->_nodegen('loclist', $path);
    }    
    
    public function LocalNodeDel($path)
    {
        return $this->_nodegen('locdel', $path);
    }
    
    public function LocalNodeSet($path, $val, $ttl = 3600)
    {
        return $this->_nodegenset('locset', $path, $val, $ttl);
    }

    public function Info()
    {
        $req = array(
            'method' => 'info',
        );
        return $this->request($req);
    }

    public function KprMemberSet($addr, $port)
    {
        $req = array(
            'method' => 'kprmemset',
            'addr' => $addr,
            'port' => $port
        );
        return $this->request($req);
    }
}
