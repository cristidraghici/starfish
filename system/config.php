<?php
if (!isset($path)) { die(); }

trait config
{
    public static function init()
    {
        // Default required values
        $root       = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $default    = array(
            'root'      => $root,
            'objects'   => $root.'objects' . DIRECTORY_SEPARATOR,
            'tpl'       => $root.'template' . DIRECTORY_SEPARATOR,
            'friendly'  => false,
            'debug'     => false,
            'session'   => 'starfish',
            'aliases'   => array(),
            'site_url'  => 'http://'.$_SERVER['HTTP_HOST'].'/',
            'router'    => ''
        );
        
        foreach ($default as $key=>$value)
        {
            if (!isset(starfish::$config[$key]))
            {
                starfish::$config[$key] = $value;
            }
        }
        
        // Build aliases
        $aliases = starfish::$config['aliases'];
        if (is_array($aliases) && count($aliases) > 0)
        {
            foreach ($aliases as $key=>$value)
            {
                if (!class_exists($value) ) { class_alias('starfish', $value); }
            }
        }
        
        // Display errors
        if (starfish::$config['debug'] == false)
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
    
    public static function config($array=null)
    {
        if (is_array($array))
        {            
            foreach ($array as $key=>$value)
            {
                starfish::$config[$key] = $value;
            }
            //starfish::$config = array_merge(starfish::$config, $array);
            
            config::init();
            
            return starfish::$config;
        }
		elseif (is_string($array))
		{
			return starfish::$config[$array];
		}
        
        
        return false;
    }
}

?>