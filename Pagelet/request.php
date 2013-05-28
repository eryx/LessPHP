<?php


class hwl_pagelet_request extends hwl_object
{
    public $method  = 'GET';
    
    /** URL/URI */
    public $url     = '';
    public $uri     = 'hww/index';
    
    /** AppID/Action */
    public $appid   = NULL;
    public $action  = NULL;
    
    /**  **/
    public $params  = NULL;    
    public $cookie  = NULL;
    
    public function __construct()
    {
        $this->url = (!empty($_SERVER['HTTPS'])) ? 
            "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : 
            "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        foreach (array('REQUEST_URI','PATH_INFO','ORIG_PATH_INFO') as $v) {
	        preg_match('/^\/[\w\-~\/\.+%]{1,600}/', server($v), $p);
	        if (!empty($p)) {
	            $this->uri = trim($p[0], '/');
	            if (stristr($this->uri, '/')) {
	                $this->appid  = stristr($this->uri, '/', true);
	                $this->action = trim(stristr($this->uri, '/'), '/');
	            }
	            break;
	        }
	    }
        
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->method = $_SERVER['REQUEST_METHOD'];
        }
        
	    $this->params = new hwl_object();        
        foreach ($_REQUEST as $key => $val) {
            $this->params->$key = $val;
        }
        
        $this->cookie = new hwl_object();
        foreach ($_COOKIE as $key => $val) {
            $this->cookie->$key = $val;
        }
    }
}
