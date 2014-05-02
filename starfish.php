<?php
/**
 * @author  Cristi DRAGHICI
 * @link    http://blog.draghici.net
 * @version 0.1a
 * 
 * @see     Parts from Dispatch PHP micro-framework were used.
 * @link    https://github.com/noodlehaus/dispatch
 * @license MIT
 * @link    http://opensource.org/licenses/MIT
 */

class starfish
{
    // Framework internal
    private static $instance;
    private static $objects;
    private static $variables   = array();
    
    public  static $config      = array();
    public  static $exec        = array();
    
    private static $routing     = array();
    
    /*
    Instantiate the class
    */
    public static function singleton()
    {
        if (!isset(self::$instance))
        {
            $obj = __CLASS__;
            self::$instance = new $obj;
        }
        
        return self::$instance;
    }
    public static function config($array=null)
    {
        if (is_array($array))
        {
            self::$config = array_merge(self::$config, $array);
			
			if (!isset(self::$config['objects']))
			{
				self::$config['objects'] = self::$config['root'] . 'objects/';
			}
			
            return self::$config;
        }
		elseif (is_string($array))
		{
			return self::$config[$array];
		}
        
        return false;
    }
    /*
    Internal variables
    */
    public static function regVar($target, $value=null)
    {
        if ($value != null)
        {
            self::$variables[$target] = $value;
            return $value;
        }
        elseif (isset(self::$variables[$target]))
        {
            return self::$variables[$target];
        }
		
		return null;
    }
    public static function regArr($target, $values=null, $value=null)
    {
        if ($value != null && gettype($values) == 'string')
        {
            self::$variables[$target][$values] = $value;
            return $value;
        }
        elseif ($value == null && $values != null && gettype($values) == 'array')
        {
            self::$variables[$target] = $values;
            return $values;
        }
        elseif ($value == null && $values != null && gettype($values) == 'string')
        {
            return self::$variables[$target][$values];
        }
        else
        {
            return self::$variables[$target];
        }
    }
    /*
    Framework initiation
    */
    public static function init()
    {
        self::$exec['method']   = self::method();
        self::$exec['path']     = self::path();
        self::$exec['params']   = self::params();
        self::$exec['ip']       = self::ip();
        self::$exec['requestHeaders']   = self::request_headers();
        self::$exec['requestBody']      = self::request_body();
        self::$exec['pathParts']        = self::pathParts( self::$exec['path'] );
        
        $aliases = self::$config['aliases'];
        if (is_array($aliases) && count($aliases) > 0)
        {
            foreach ($aliases as $key=>$value)
            {
                class_alias('starfish', $value);
            }
        }
        
        if (self::$config['debug'] == false)
        {
			error_reporting(0);
			@ini_set('display_errors', 'off');
        }
        else
        {
            error_reporting(E_ALL | E_STRICT);
			@ini_set('display_errors', 'on');
        }
		
        return true;
    }
    
    /*
    Routing / Inspired by PHP Dispatch Framework ( https://github.com/noodlehaus/dispatch / http://noodlehaus.mit-license.org/ )
    */
    public static function method()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if ($method == 'POST')
        {
            if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
            {
                $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
            }
        }
        else
        {
            $method = self::params('_method') ? self::params('_method') : $method;
        }
        
        return strtolower($method);
    }
    public static function path()
    {
        // get the request_uri basename
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // remove dir path if we live in a subdir
        if ($base = self::$config['site_url'])
        {
            $base = rtrim(parse_url($base, PHP_URL_PATH), '/');
            $path = preg_replace('@^'.preg_quote($base).'@', '', $path);
        }
        else
        {
            // improved base directory detection if no config specified
            $base = rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/');
            $path = preg_replace('@^'.preg_quote($base).'@', '', $path);
        }
        
        // remove router file from URI
        if ($stub = self::$config['site_url'])
        {
            $stub = self::$config['router'];
            $path = preg_replace('@^/?'.preg_quote(trim($stub, '/')).'@i', '', $path);
        }
        
        // Set the path on non-friendly servers
        if (self::$config['friendly'] == false)
        {
            $get = array_keys($_GET);
            if (isset($get[0]))
            {
                $path = $get[0];
            }
        }
        
        $path = trim($path, '/');
        
        return $path;
    }
    public static function params($name = null, $default = null)
    {
        static $source = null;
        
        // initialize source if this is the first call
        if (!$source)
        {
            $source = array_merge($_GET, $_POST);
            if (get_magic_quotes_gpc())
            {
                array_walk_recursive(
                    $source,
                    function (&$v) { $v = stripslashes($v); }
                );
            }
        }
        
        // this is a value fetch call
        if (is_string($name))
        {
            $params = (isset($source[$name]) ? $source[$name] : $default);
        }
        else
        {
            $params = $source;
        }
        
        return self::cleanInputs($params);
    }
    public static function session($name = null, $value = null)
    {
    
        static $session_active = false;
        
        // stackoverflow.com: 3788369
        if ($session_active === false)
        {
            if (($current = ini_get('session.use_trans_sid')) === false)
            {
                trigger_error(
                    'Call to session() requires that sessions be enabled in PHP',
                    E_USER_ERROR
                );
            }
            
            $test = "mix{$current}{$current}";
            
            $prev = @ini_set('session.use_trans_sid', $test);
            $peek = @ini_set('session.use_trans_sid', $current);
            
            if ($peek !== $current && $peek !== false)
            {
                session_start();
            }
            
            $session_active = true;
        }
        
        $args = func_num_args();
        if ($args === 1)
        {
            return (isset($_SESSION[$name]) ? $_SESSION[$name] : null);
        }
        elseif ($args === 0)
        {
            return (is_array($_SESSION) ? $_SESSION : null);
        }
        
        $_SESSION[$name] = $value;
    }
    public static function cookie($name, $value = null, $expire = 31536000, $path = '/')
    {
        static $quoted = -1;
        
        if ($quoted < 0)
        {
            $quoted = get_magic_quotes_gpc();
        }
        
        if (func_num_args() === 1)
        {
            return (isset($_COOKIE[$name]) ? ( $quoted ? stripslashes($_COOKIE[$name]) : $_COOKIE[$name] ) : null );
        }
        
        setcookie($name, $value, time() + $expire, $path);
    }
    private static function request_headers($key = null)
    {
        static $headers = null;
        
        // if first call, pull headers
        if (!$headers)
        {
            // if we're not on apache
            $headers = array();
            foreach ($_SERVER as $k => $v)
            if (substr($k, 0, 5) == 'HTTP_')
            {
                $headers[strtolower(str_replace('_', '-', substr($k, 5)))] = $v;
            }
            
            $headers = self::cleanInputs($headers);
        }
        
        // header fetch
        if ($key !== null)
        {
            $key = strtolower($key);
            return isset($headers[$key]) ? $headers[$key] : null;
        }
        
        return $headers;
    }
    private static function request_body($load = true)
    {
        static $content = null;
        
        // called before, just return the value
        if ($content)
        {
            return $content;
        }
        
        // get correct content-type of body (hopefully)
        $_SERVER['CONTENT_TYPE'] = isset( $_SERVER['CONTENT_TYPE'] ) ? $_SERVER['CONTENT_TYPE'] : '';
        $content_type = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : $_SERVER['CONTENT_TYPE'];
        
        // try to load everything
        if ($load)
        {
            $content = file_get_contents('php://input');
            $content_type = preg_split('/ ?; ?/', $content_type);
            
            // if json, cache the decoded value
            if ($content_type[0] == 'application/json')
            {
                $content = json_decode($content, true);
            }
            else if ($content_type[0] == 'application/x-www-form-urlencoded')
            {
                parse_str($content, $content);
            }
            
            $content = self::cleanInputs($content);
            return $content;
        }
        
        // create a temp file with the data
        $path = tempnam(sys_get_temp_dir(), 'disp-');
        $temp = fopen($path, 'w');
        $data = fopen('php://input', 'r');
        
        // 8k per read
        while ($buff = fread($data, 8192))
        {
            fwrite($temp, $buff);
        }
        
        fclose($temp);
        fclose($data);
        
        return $path;
    }
    public static function send($path, $filename, $sec_expires = 0)
    {
        $mime = 'application/octet-stream';
        $etag = md5($path);
        $lmod = filemtime($path);
        $size = filesize($path);
        
        // cache headers
        header('Pragma: public');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lmod).' GMT');
        header('ETag: '.$etag);
        
        // if we want this to persist
        if ($sec_expires > 0)
        {
            header('Cache-Control: maxage='.$sec_expires);
            header(
                'Expires: '.gmdate('D, d M Y H:i:s',
                time() + $sec_expires).' GMT'
            );
        }
        
        // file info
        header('Content-Disposition: attachment; filename='.urlencode($filename));
        header('Content-Type: '.$mime);
        header('Content-Length: '.$size);
        
        // no time limit, clear buffers
        set_time_limit(0);
        ob_clean();
        
        // dump the file
        $fp = fopen($path, 'rb');
        while (!feof($fp))
        {
            echo fread($fp, 1024*8);
            ob_flush();
            flush();
        }
        fclose($fp);
    }
    public static function files($name)
    {
        if (!isset($_FILES[$name]))
        {
            return null;
        }
        $result = null;
        
        // if file field is an array
        if (is_array($_FILES[$name]['name']))
        {
        
            $result = array();
            
            // consolidate file info
            foreach ($_FILES[$name] as $k1 => $v1)
            {
                foreach ($v1 as $k2 => $v2)
                {
                    $result[$k2][$k1] = $v2;
                }
            }
            
            // remove invalid uploads
            foreach ($result as $i => $f)
            {
                if (!is_uploaded_file($f['tmp_name']))
                {
                    unset($result[$i]);
                }
            }
            
            // if no entries, null, else, return it
            $result = (!count($result) ? null : array_values($result));
            
        }
        else
        {
            // only if file path is valid
            if (is_uploaded_file($_FILES[$name]['tmp_name']))
            $result = $_FILES[$name];
        }
        
        // null if no file or invalid, hash if valid
        return $result;
    }
    public static function ip()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }
    public static function redirect($path, $code = 302, $condition = true) 
	{
        if (!$condition) { return; }
		
		if (substr($path, 0, 2) == './') { $path = self::$config['site_url'] . substr($path, 2); }
		
        @header("Location: {$path}", true, $code);
        exit;
    }
    public static function nocache()
    {
        header('Expires: Tue, 13 Mar 1979 18:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
    public static function json($obj, $func = null)
    {
        self::nocache();
        if (!$func)
        {
            header('Content-type: application/json');
            echo json_encode($obj);
        }
        else
        {
            header('Content-type: application/javascript');
            echo ";{$func}(".json_encode($obj).");";
        }
    }
    public static function error($code, $callback=null)
    {
        $code = (string) $code;
        
        // this is a hook setup, save and return
        if (is_callable($callback))
        {
            call_user_func($callback, $code);
            return true;
        }
        
        // see if passed callback is a message (string)
        $message = (is_string($callback) ? $callback : 'Page Error');
        
        // set the response code
        header(
            "{$_SERVER['SERVER_PROTOCOL']} {$code} {$message}",
            true,
            (int) $code
        );
        
        #$message = "{$code} {$message}";
        
        exit ($message);
    }
    
    /*
    Method used to define what to do when a 
    */
    public static function on($method, $path, $callback=null)
    {        
        if ($callback != null)
        {
            // Get the parameters
            $params = array();
            preg_match_all('#/::([^/\s]*)#is', $path, $matches, PREG_SET_ORDER);
            foreach ($matches as $key=>$value)
            {
                $path = str_replace($value[0], '/*', $path);
                $params[] = $value[1];
            }
            
            switch (gettype($callback))
            {
                case 'array':
                    self::$routing[strtolower($method)][$path] = array(
                        'callback'  => $callback['callback'],
                        'class'     => $callback['class'],
                        'params'    => $params
                    );
                    break;
                
                default:
                    // Store the path
                    if (is_callable($callback))
                    {
                        self::$routing[strtolower($method)][$path] = array('callback'=>$callback, 'params'=>$params);
                        return true;
                    }
                    break;
            }
        }
        else
        {
            $callback   = '';
            $params     = array();
            
            // List the routes
            if (isset(self::$routing[$method]))
            {
                $all = self::$routing[$method];
                $routes = array_keys($all);
                
                // Get the proper function
                foreach ($routes as $key=>$check)
                {
                    $check = trim($check, '/');
                    $check = '#^'.str_replace('*', '([^\/]*)', $check).'$#is';
                    
                    #echo $path . ' - ' . $check . ' - '. preg_match($check, $path, $match) . ' - '. $match[1] ."<br>\n";
                    if (preg_match($check, $path, $match))
                    {
                        $route = $all[$routes[$key]];
                        
                        $callback = $route['callback'];
                        $list = $route['params'];
                        foreach ($list as $key=>$value)
                        {
                            $params[$value] = $match[$key+1];
                        }
                    }
                }
            }
            
            // Extract the parameters
            if (is_callable($callback))
            {
                call_user_func_array($callback, $params);
            }
            else
            {
                // Extract object from route
                preg_match('#([^\/]*)#is', $path, $match);
                $object = self::obj($match[1]);
                if ($object != false && (int)method_exists($object, 'exec') == 1)
                {
                    $object->exec();
                }
                elseif ($object == false)
                {
                    self::error(400, 'Bad request');
                }
            }
        }
        
        return true;
    }
    
    private static function pathParts($path)
    {
        $parts = array();
        $path = explode('/', $path);
        foreach ($path as $key=>$value)
        {
            $value = trim($value);
            if (strlen($value) > 0) { $parts[] = $value; }
        }
        
        return $parts;
    }
    
    private static function cleanInputs($data)
    {
        $clean_input = Array();
        if (is_array($data))
        {
            foreach ($data as $k => $v)
            {
                $clean_input[$k] = self::cleanInputs($v);
            }
        }
        else
        {
            $clean_input = trim(strip_tags($data));
        }
        
        return $clean_input;
    }
    
	/** Registry type object calling */
    public static function obj($name)
    {
        if (!isset(self::$objects[$name]))
        {
            /*
             * Create the api object
             */
            // if class exists within the current file
            if (class_exists($name))
            {
                self::$objects[$name] = new $name;
            }
            // if class exists within the system file
            elseif (file_exists( self::$config['root'] . 'system/' . $name . '.php' ))
            {
                include( self::$config['root'] . 'system/' . $name . '.php' );
                if (class_exists($name))
                {
                    self::$objects[$name] = new $name;
                }
                else
                {
                    if (self::$config['debug'] == false) { self::error(400, "Bad request."); }
                    
                    self::error(400, "Class '".$name."' does not exist.");
                    return false;
                }
            }
            // if class exists in the custom required objects list
            elseif (file_exists( self::$config['objects'] . $name . '.php' ))
            {
                include( self::$config['objects'] . $name . '.php' );
                if (class_exists($name))
                {
                    self::$objects[$name] = new $name;
                }
                else
                {
                    if (self::$config['debug'] == false) { self::error(400, "Bad request."); }
                    
                    self::error(400, "Class '".$name."' does not exist.");
                    return false;
                }
            }
            // debug error
            elseif (strlen($name) > 0)
            {
                if (self::$config['debug'] == false) { self::error(400, "Bad request."); }
                
                self::error(400, "File '".$name."' does not exist.");
                return false;
            }
            // silent error
            else
            {
                self::error(400, "Bad request.");
                return false;
            }
        }
        
        return self::$objects[$name];
    }
    
	/** Model/View/Controller Applications */
	public static function c()
	{
		
		return true;
	}
	public static function m()
	{
		
		return true;
	}
	public static function v()
	{
		
		return true;
	}
	
    /*
    Execute
    */
    public static function exec()
    {
        self::cleanInputs($_GET);
        self::cleanInputs($_POST);
        
        self::on(
            self::$exec['method'],
            self::$exec['path']
        );
        return true;
    }
}

?>