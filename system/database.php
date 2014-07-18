<?php
if (!class_exists('starfish')) { die(); }

/**
 * "Abstract" class for interacting with databases
 *
 * @package starfish
 * @subpackage starfish.system.database
 */
class database
{
	/**
	 * Declare used variables
	 *
	 * $connections - The list of information regarding connections
	 * $resources - The list of resources stored as connections
	 */
	private static $connections = array();
	private static $resources = array();
	
	/**
	 * Init the object. Connect to any database in the configuration, if needed
	 */
	public static function init()
	{
	}
	
	/**
	 * Check whether a connection is active. The main usage of this function is for input sanitization
	 *
	 * @return boolean Returns true if a connection has been established
	 */
	public static function isConnected()
	{
		if (count(self::$connections) == 0)
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Create a connection
	 *
	 * @param string $type The type of connection: mysql | postgres
	 * @param string $name The name of the connection to be accessed throughout the code
	 *
	 * @return boolean Return whether the connection was successful or not
	 */
	public static function connect($type, $name, $parameters)
	{
		
	}
	
	/**
	 * Return a connection
	 *
	 * @param string $name The name of the connection to be accessed
	 *
	 * @return resource Return the connection
	 */
	public static function connection($name)
	{
        $connection = null;
        
		if (isset(self::$connections[$name]))
		{
			$connection = self::$connections[$name];
		}
		
		// If only one connection, return it
		if (count(self::$connections) == 0)
		{
			$connection = array_values(self::$connections)[0];
		}
        
        // Init the object, if the connection is needed
        if (isset($connection) && !isset(self::$resources[$connection]))
        {
            self::$resources[$connection] = $connection->init();
        }
		
		return false;
	}
	
	/**
	 * Disconnect
	 *
	 * @param string $name The name of the connection to disconnect from
	 *
	 * @return boolean Return whether the connection was successful or not
	 */
	public static function disconnect($name)
	{
		// Disconnect, if connection exists
		if (isset(self::$connections[$name]))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Sanitize input
	 *
	 * @param string $string The string to sanitize
	 * @param string $connection Name of the connection to execute the command on
	 * 
	 * @return string The sanitized returned string
	 */
	public static function sanitize($string, $connection=null)
	{
		if ($connection == null && self::isConnected()) { $connection = self::connection(); }
		
		return $string;
	}
	
	/**
	 * Execute a query
	 *
	 * @param string $query The query to execute
	 * @param string $connection Name of the connection to execute the command on
	 *
	 * @return resource The output of the query
	 */
	public static function query($query, $connection=null)
	{
		if ($connection == null && self::isConnected()) { $connection = self::connection(); }
		
	}
	
	/**
	 * Fetch the query info
	 *
	 * @param resource $resource The query to execute
	 * @param string $connection Name of the connection to execute the command on
	 *
	 * @return array The fetched results
	 */
	public static function info($query, $connection=null)
	{
		if ($connection == null && self::isConnected()) { $connection = self::connection(); }
		
	}
	
	/**
	 * Fetch the results
	 *
	 * @param resource $resource The query to execute
	 * @param string $connection Name of the connection to execute the command on
	 *
	 * @return array The fetched results
	 */
	public static function fetch($resource, $connection=null)
	{
		if ($connection == null && self::isConnected()) { $connection = self::connection(); }
		
	}
	
	/**
	 * Free results
	 *
	 * @param resource $resource The query to execute
	 * @param string $connection Name of the connection to execute the command on
	 */
	public static function free($resource, $connection=null)
	{
		if ($connection == null && self::isConnected()) { $connection = self::connection(); }
		
	}
}

/**
* Aliases used by class for easier programming
*/
function isConnected()	{ return call_user_func_array(array('database', 'isConnected'),    	func_get_args()); }

function connect()	{ return call_user_func_array(array('database', 'connect'),    		func_get_args()); }
function connection()   { return call_user_func_array(array('database', 'connection'),    	func_get_args()); }
function disconnect()   { return call_user_func_array(array('database', 'disconnect'),    	func_get_args()); }

function query()	{ return call_user_func_array(array('database', 'query'),    		func_get_args()); }
function info()		{ return call_user_func_array(array('database', 'info'),    		func_get_args()); }
function fetch()	{ return call_user_func_array(array('database', 'fetch'),    		func_get_args()); }
function free()		{ return call_user_func_array(array('database', 'free'),    		func_get_args()); }
?>