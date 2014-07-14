<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for the input parameters
 * 
 * @package starfish
 * @subpackage starfish.system.parameters
 */
class parameters
{
	/**
	 * Declare used variables
	 *
	 * $cache 		- Cache information for usage without running the function
	 */ 
    private static $cache;
    
    
	/**
	 * Init the object
	 */
    public static function init()
    {
        self::method();
    }
    
	/**
	 * Request method
	 */
    public static function method()
    {
        // If string exists in cache, return it
        if (isset(self::$cache['method'])) { return self::$cache['method']; }
        
        // Create the string in cache and return it
        self::$cache['method'] = '';
        
        
        return self::$cache['method'];
    }
    
	/**
	 * Request path
	 */
    public static function path()
    {
        // If string exists in cache, return it
        if (isset(self::$cache['path'])) { return self::$cache['path']; }
        
        // Create the string in cache and return it
        self::$cache['path'] = '';
        
        
        return self::$cache['path'];
    }
    
	/**
	 * Get parameters
	 *
	 * @param string $name The name of the variable to retrieve
	 */
    public static function get($name)
    {
        // If string exists in cache, return it
        if (isset(self::$cache['get'][$name])) { return self::$cache['get'][$name]; }
        
        // Create the string in cache and return it
        self::$cache['get'][$name] = '';
        
        
        return self::$cache['get'][$name];
    }
    
	/**
	 * Post parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
    public static function post($name)
    {
        // If string exists in cache, return it
        if (isset(self::$cache['post'][$name])) { return self::$cache['post'][$name]; }
        
        // Create the string in cache and return it
        self::$cache['post'][$name] = '';
        
        
        return self::$cache['post'][$name];
    }
    
	/**
	 * Put parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
    public static function put($name)
    {
        // If string exists in cache, return it
        if (isset(self::$cache['put'][$name])) { return self::$cache['put'][$name]; }
        
        // Create the string in cache and return it
        self::$cache['put'][$name] = '';
        
        
        return self::$cache['put'][$name];
    }
    
	/**
	 * Delete parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
    public static function delete($name)
    {
        // If string exists in cache, return it
        if (isset(self::$cache['delete'][$name])) { return self::$cache['delete'][$name]; }
        
        // Create the string in cache and return it
        self::$cache['delete'][$name] = '';
        
        
        return self::$cache['delete'][$name];
    }
    
	/**
	 * File parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
    public static function file($name)
    {
        // If string exists in cache, return it
        if (isset(self::$cache['file'][$name])) { return self::$cache['file'][$name]; }
        
        // Create the string in cache and return it
        self::$cache['file'][$name] = '';
        
        
        return self::$cache['file'][$name];
    }
}

/**
* Aliases used by class for easier programming
*/
function method()   { return call_user_func_array(array('parameters', 'method'),    func_get_args()); }
function path()     { return call_user_func_array(array('parameters', 'path'),      func_get_args()); }

function get()      { return call_user_func_array(array('parameters', 'get'),       func_get_args()); }
function post()     { return call_user_func_array(array('parameters', 'post'),      func_get_args()); }
function put()      { return call_user_func_array(array('parameters', 'put'),       func_get_args()); }
function delete()   { return call_user_func_array(array('parameters', 'delete'),    func_get_args()); }
function file()     { return call_user_func_array(array('parameters', 'file'),      func_get_args()); }
?>