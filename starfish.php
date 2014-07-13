<?php
/**
 * Starfish PHP Framework is a minimum Registry microframework primarily desiged to serve JSON content and use objects.
 *
 * @author  Cristi DRAGHICI
 * @link    http://blog.draghici.net
 * @version 0.3a
 * 
 * @see     Parts from Dispatch PHP micro-framework were used.
 * @link    https://github.com/noodlehaus/dispatch
 * @license MIT
 * @link    http://opensource.org/licenses/MIT
 */

/*
 * Entry point of Starfish PHP Framework: this acts like a Registry for all the other objects used
 * 
 * @package starfish
 * @subpackage starfish
 */
class starfish
{
	/*
	 * Declare used variables
	 *
	 * $config 		- configuration variables
	 * $variables 	- variable values set throughout the application and accessible from anywhere
	 * $objects 	- a list of objects 
	 */
	public $config;
	public $variables;
	private $objects;
	
	##################
	# Framework functions
	##################
	
	/**
	 * Init the framework
	 */
	public function init()
	{
		
		return self;
	}
	
	/**
	 * Configuration function
	 *
	 * @param string $name The name of the module for which the configuration is stored
	 * @param array $parameters The array with configuration values to be stored
	 */
	public function config($name, $parameters)
	{
		if (!is_array( self::$config[$name] ))
		{
			self::$config[$name] = $parameters;
		}
		else
		{
			self::$config[$name] = array_merge(self::$config[$name], $parameters);
		}
	}
	
	##################
	# Variables
	##################
	
	/**
	 * Set a variable
	 *
	 * @param string $name The name of the value to store
	 * @param any $value The value of the parameter to store
	 */
	public function set($name, $value)
	{
		self::$variables[$name] = $value;
		
		return $value;
	}
	
	/**
	 * Get a variable
	 *
	 * @param string $name The name of the variable to retrive
	 */
	public function get($name)
	{
		return self::$variables[$name];
	}
	
}

// Init the framework
$starfish &= new starfish();
?>