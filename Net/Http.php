<?php

namespace LessPHP\Net;

/**
 * Class LessPHP\Net\Http
 *
 * Example: GET
 *  $client = new LessPHP\Net\Http::NewInstance('http://www.example.com/get.php?var=value');
 *  if ($client->doGet() == 200) {
 *      $response = $client->GetBody();
 *  }
 *
 * Example: POST/PUT
 *  $client = new LessPHP\Net\Http::NewInstance('http://www.example.com/post.php);
 *  $data = '<?xml version="1.0" encoding="utf-8"?>
 *      <feed xmlns="http://www.w3.org/2005/Atom">
 *          <entry>...</entry>
 *      </feed>';
 *  if ($client->doPost($data) == 200) {
 *      $response = $client->GetBody();
 *  }
 * 
 */
class Http
{
    private $_uri      = '';
    private $_headers  = array('Accept' => '');
    private $_body     = null;
    
    private $_conn     = null;
    
    private $_timeout  = 60;

    const AUTH_TEMPLATE  = 'Authorization: auth="?"';
    const AUTH_DIVIDE    = ':';

    /**
     * Content attributes
     */
    const CONTENT_TYPE   = 'Content-Type';
    const CONTENT_LENGTH = 'Content-Length';
   
    public static function NewInstance($uri = null)
    {
        return new Http($uri);
    }
    
    public function __construct($uri)
    {
        $this->_uri = $uri;
    }
    
    
    

    public function setUri($uri)
    {
        $this->_uri = $uri;
        $this->_conn($uri);
    }

    public function setHeader($k, $v)
    {
        $this->_headers[$k] = $v;
    }
    
    public function setTimeout($v)
    {
        $this->_timeout = $v;
    }

    private function _conn($uri = null)
    {
        if ($this->_conn !== null && $uri === null) {
            return;
        }
        
        if ($uri !== null) {
            $this->_uri = $uri;
        }

        $this->_conn = curl_init();
        
        curl_setopt($this->_conn, CURLOPT_URL, $this->_uri);
        curl_setopt($this->_conn, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($this->_conn, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
        curl_setopt($this->_conn, CURLOPT_TIMEOUT, $this->_timeout);        
        curl_setopt($this->_conn, CURLOPT_ENCODING, "gzip");
        curl_setopt($this->_conn, CURLOPT_USERAGENT, 'LessPHPNetHttp v1');
        curl_setopt($this->_conn, CURLOPT_RETURNTRANSFER, true);
    }

    final public function close()
    {
        if ($this->_conn !== null) {
            curl_close($this->_conn);
        }
    }

    final public function Get($body = null)
    {
        $this->_conn();
        
        curl_setopt($this->_conn, CURLOPT_HTTPGET, true);
        
        return $this->_request($body);
    }

    final public function Post($body)
    {
        $this->_conn();
        
        $this->setHeader(self::CONTENT_LENGTH, strlen($body));
        curl_setopt($this->_conn, CURLOPT_POST, true);
        curl_setopt($this->_conn, CURLOPT_POSTFIELDS, $body);
        
        return $this->_request($body);
    }

    final public function Put($body)
    {
        $this->_conn();
        
        $this->setHeader(self::CONTENT_LENGTH, strlen($body));
        curl_setopt($this->_conn, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->_conn, CURLOPT_POSTFIELDS, $body);
        
        return $this->_request($body);
    }

    final public function Delete($body)
    {
        $this->_conn();
        
        curl_setopt($this->_conn, CURLOPT_CUSTOMREQUEST, 'DELETE');
        
        return $this->_request($body);
    }

    private function _request($body)
    {
        curl_setopt($this->_conn, CURLOPT_HTTPHEADER, $this->_headers);
    
        $this->_body = curl_exec($this->_conn);
        
        return curl_getinfo($this->_conn, CURLINFO_HTTP_CODE);
    }

    final public function GetBody()
    {
        return $this->_body;
    }

    final public function GetInfo()
    {
        return curl_getinfo($this->_conn);
    }
}
