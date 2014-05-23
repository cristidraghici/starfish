<?php
if (!isset($path)) { die(); }

trait exec
{
    public static function init()
    {
        starfish::$exec['method']   = self::method();
        starfish::$exec['path']     = self::path();
        starfish::$exec['params']   = self::params();
        starfish::$exec['ip']       = self::ip();
        starfish::$exec['requestHeaders']   = self::request_headers();
        starfish::$exec['requestBody']      = self::request_body(true);
        starfish::$exec['pathParts']        = self::pathParts( starfish::$exec['path'] );
        
		if (isset(starfish::$config['date_default_timezone']))
		{
			date_default_timezone_set(starfish::$config['date_default_timezone']);
		}
		
        return true;
    }
    
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
            $method = starfish::params('_method') ? starfish::params('_method') : $method;
        }
        
        return strtolower($method);
    }
    
    public static function path()
    {
        // get the request_uri basename
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // remove dir path if we live in a subdir
        if ($base = starfish::$config['site_url'])
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
        if ($stub = starfish::$config['site_url'])
        {
            $stub = starfish::$config['router'];
            $path = preg_replace('@^/?'.preg_quote(trim($stub, '/')).'@i', '', $path);
        }
        
        // Set the path on non-friendly servers
        if (starfish::$config['friendly'] == false)
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
		
		if (substr($path, 0, 2) == './') { $path = starfish::$config['site_url'] . substr($path, 2); }
		
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
        starfish::nocache();
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
}

?>