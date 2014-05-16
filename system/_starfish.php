<?php
/**
 * This file is a part of Starfish PHP micro-framework
 */

class starfish
{
    public static $config = array();
    public static $exec = array();
    
    private static $objects = array();
    private static $variables = array();
    private static $routing = array();
    
    use config;
    use exec;
    use mvc;
    use registry;
    use routing;
    use variables;
    use errors;
    
    /** Init the class */
    public static function init()
    {
        // Exec
        config::init();
        exec::init();
        
        // Build aliases
        $aliases = self::$config['aliases'];
        if (is_array($aliases) && count($aliases) > 0)
        {
            foreach ($aliases as $key=>$value)
            {
                class_alias('starfish', $value);
            }
        }
        
        // Display errors
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
    public static function singleton() { self::init(); }
    
    /** Exec the class */
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