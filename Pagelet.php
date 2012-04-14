<?php

define('LessPHP_DIR', realpath(__DIR__ . '/..'));

if (!in_array(LessPHP_DIR, explode(':', get_include_path()))) {
    set_include_path(LessPHP_DIR . PATH_SEPARATOR . get_include_path());
}

// autoload_register
function pagelet_autoload($class) {    
    $t = str_replace(array('_', "\\"), '/', $class.'.php');
    require_once ($t);
}
spl_autoload_register("pagelet_autoload");

//set_error_handler(array('hwl_error','handler'));
//register_shutdown_function(array('hwl_error','fatal'));
//set_exception_handler(array('hwl_error','exception'));

class LessPHP_Pagelet
{
    private $path   = array();
    
    public  $req    = NULL;
    public  $sess   = NULL;
    
    public  $app    = NULL;

    public function __construct($opt = array())
    {
        $this->_initSetting();
        $this->req  = new LessPHP_Object();        
        $this->sess = new LessPHP_Object();
        $this->app  = new LessPHP_Object();
        
        $this->app->url = getenv('HTTPS') ? 
            "https://".getenv('HTTP_HOST').getenv('REQUEST_URI') : 
            "http://".getenv('HTTP_HOST').getenv('REQUEST_URI');

        if (isset($opt['path'])) {
            $this->setPath($opt['path']);
        }
        
        if (isset($opt['uri_default'])) {
            list($this->app->appid, $this->app->action) = explode('/', $opt['uri_default']);
        }
        
        foreach (array('REQUEST_URI','PATH_INFO','ORIG_PATH_INFO') as $v) {
	        preg_match('/^\/[\w\-~\/\.+%]{1,600}/', getenv($v), $p);
	        if (!empty($p)) {
	            $this->app->uri = trim($p[0], '/');
	            if (stristr($this->app->uri, '/')) {
	                $this->app->appid  = stristr($this->app->uri, '/', true);
	                $this->app->action = trim(stristr($this->app->uri, '/'), '/');
	            }
	            break;
	        }
	    }
        
        $this->app->method = getenv('REQUEST_METHOD');
       
	    
        foreach ($_REQUEST as $key => $val) {
            $this->req->$key = $val;
        }
        
        foreach ($_COOKIE as $key => $val) {
            $this->sess->$key = $val;
        }
    }
    
    public function setPath($path)
    {
        array_unshift($this->path, rtrim($path, '/'));
    }
    
    public function render($action = NULL, $vars = NULL)
    {
        ob_start();
        
        if ($action === NULL)
            $action = $this->app->action;
        
        if (is_array($vars)) {
            foreach ($vars as $key => $val)
                $$key = $val;
            unset($vars);
        }
        
        foreach ($this->path as $v) {
            $t = "{$v}/{$this->app->appid}/pagelet/{$action}.php";
            if (file_exists($t)) {
                include $t;
                break;
            }
        }

        return ob_get_clean();
    }
    
    private function _initSetting()
    {
        ini_set('zlib.output_compression', 'Off');

        // Don't escape quotes when reading files from the database, disk, etc.
        ini_set('magic_quotes_runtime', '0');
        
        // Use session cookies, not transparent sessions that puts the session id include_path
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        // Don't send HTTP headers using PHP's session handler.
        ini_set('session.cache_limiter', 'none');
        // Use httponly session cookies.
        ini_set('session.cookie_httponly', '1');
        
        ini_set('short_open_tag', 'On');
        
        // iconv encoding
        iconv_set_encoding("internal_encoding", "UTF-8");
        // multibyte encoding
        mb_internal_encoding('UTF-8');

        // to ensure consistent string, dates, times and numbers handling.
        setlocale(LC_ALL, 'en_US.utf-8');
        
        if (ini_get('magic_quotes_gpc')) {
	        function stripa(&$v) {
		        $v = stripslashes($v);
	        }	
	        function stripf(&$v, $k) {
	            if ($k != 'tmp_name') $v = stripslashes($v);
	        }
	        array_walk_recursive($_GET,     'stripa');
	        array_walk_recursive($_POST,    'stripa');
	        array_walk_recursive($_COOKIE,  'stripa');
	        array_walk_recursive($_REQUEST, 'stripa');
	        array_walk_recursive($_FILES,   'stripf');
        }
    }
}
