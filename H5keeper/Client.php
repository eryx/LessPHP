<?php

namespace LessPHP\H5keeper;

use LessPHP\Net\Http;

class Client
{
    const StatusOK                  = "0";
    const StatusErr                 = "1";

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
    // The default connection is to the server running on localhost on port 9530.
    // @param string $dsn The data source name of the h5keeper server
    // @param float $timeout The connection timeout in seconds
    public function __construct($dsn = '127.0.0.1:9528', $timeout = null)
    {
        $this->http = new Http("http://{$dsn}/h5keeper/apiv2");
    }

    private function request($obj)
    {
        $st = $this->http->Post(json_encode($obj));
        if ($st != 200) {
            return false;
        }

        return json_decode($this->http->GetBody(), false);
    }
    
    public function NodeGet($path)
    {
        $req = array(
            'method' => 'get',
            'path'   => $path
        );
        return $this->request($req);
    }
    
    public function NodeGets($path)
    {
        $req = array(
            'method' => 'gets',
            'path'   => $path
        );
        return $this->request($req);
    }

    public function NodeList($path)
    {
        $req = array(
            'method' => 'list',
            'path'   => $path,
        );
        return $this->request($req);
    }

    public function NodeSet($path, $val)
    {
        $req = array(
            'method' => 'set',
            'path'   => $path,
            'val'    => "".$val
        );
        return $this->request($req);
    }
}
