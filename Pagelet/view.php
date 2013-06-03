<?php

class hwl_pagelet_view extends hwl_object
{
    private $_paths = array();

    public $headStylesheet = '';
    public $headJavascript = '';

    public function __construct()
    {
        //$this->_paths[] = SYS_ROOT.'app/hww';
    }
    
    public function setPath($path)
    {
        array_unshift($this->_paths, rtrim($path, '/'));
    }
    
    public function prerender($name, $action, $vars = NULL, $projid = NULL)
    {
        $this->{$name} = $this->render($action, $vars, $projid);
    }
    
    public function render($action, $vars = NULL, $projid = NULL)
    {
        ob_start();
        
        if (is_array($vars)) {
            foreach ($vars as $key => $val) {
                $$key = $val;
            }
            unset($vars);
        }
        
        if ($projid !== NULL) {
        
            $f = SYS_ROOT."/{$projid}/pagelet/{$action}.php";            
            if (file_exists($f)) {
                include $f;
            }
            
        } else {
        
            foreach ($this->_paths as $val) {
            
                if ($action != NULL) {
                    $f = $val."/pagelet/{$action}.php";
                    if (file_exists($f)) {
                        include $f;
                        $action = NULL;
                    }                    
                }
            }
        }

        return ob_get_clean();
    }
    
    public function headStylesheet($val)
    {
        if (!in_array($val, $this->headStylesheet)) {
            $this->headStylesheet[] = $val;
        }
    }
    
    public function headJavascript($val)
    {
        if (!in_array($val, $this->headJavascript)) {
            $this->headJavascript[] = $val;
        }
    }
    
    public function response()
    {
        foreach ($this->$headStylesheet as $val) {
            $this->headStylesheet .= '<link rel="stylesheet" href="'.$val.'" type="text/css" media="all" />'."\n";
        }

        foreach ($this->$headJavascript as $val) {
            $this->headJavascript .= '<script type="text/javascript" src="'.$val.'"></script>'."\n";
        }
    }
    
    public function siteurl($url, $instance = NULL, $rpl = array(), $personal = NULL)
    {
        if ($instance === NULL) {
            $instance = $this->reqs->ins;
        }
        
        $conf = hwl_cfg::get('global');

        if (preg_match("/^http/", $url)) {
            $link = '';
        } else if (strlen($personal) > 1 && $conf["instance"][$instance]["type"] == "personal") {
            $link = $personal.'/'.$instance;
        } else if (isset($conf["instance"][$instance]["url"])) {
            $link = $conf["instance"][$instance]["url"];
        } else if ($conf["instance"][$instance]["type"] == "personal") {
            $link = $conf["url_personal"].'/'.$instance.'/';
        } else {
            $link = $conf["url_base"].'/'.$instance.'/';
        }

        $link .= $url;
        $link = preg_replace("/\/+/", '/', $link);
        
        $mat = array(':/');
        $rep = array('://');
        
        if (isset($rpl[':uname'])) {
            $mat[] = ':uname';
            $rep[] = $rpl[':uname'];
        } else if (isset($this->reqs->uname)) {
            $mat[] = ':uname';
            $rep[] = $this->reqs->uname;
        }
        
        foreach ($rpl as $key => $val) {
            $mat[] = $key;
            $rep[] = $val;
        }
        
        $link = str_replace($mat, $rep, $link);
        
        return $link;
    }
}
