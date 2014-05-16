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
            'objects'   => $root.'objects/',
            'tpl'       => $root.'template/',
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
        
        return true;
    }
    
    public static function config($array=null)
    {
        if (is_array($array))
        {
            starfish::$config = array_merge(starfish::$config, $array);
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