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

/**
 * Entry point of Starfish PHP Framework: this acts like a Registry for all the other objects used
 * 
 * @package starfish
 * @subpackage starfish
 */
class starfish
{
	/**
	 * Declare used variables
	 *
	 * $config 		- configuration variables
	 * $variables 	- variable values set throughout the application and accessible from anywhere
	 * $objects 	- a list of objects 
	 */
	public static $config;
	public static $variables;
	private static $objects;
	
	##################
	# Framework functions
	##################
	
	/**
	 * Init the framework
	 */
	public static function init()
	{
		// Set the path for Starfish
		$path = self::config('_starfish', array(
			'root' => @realpath(__DIR__) . DIRECTORY_SEPARATOR
		));
		
		// Create the initial objects list
		self::$objects = array(
			'parameters'=>array(
				'instance'		=> null,
				'configuration'	=> array(
					'path'	=> $path . 'system/parameters.php',
					'class'	=> 'parameters'
				)
			)
		);
		
		return self;
	}
	
	/**
	 * Configuration function
	 *
	 * @param string $name The name of the module for which the configuration is stored
	 * @param array $parameters The array with configuration values to be stored
	 */
	public static function config($name, $parameters=array())
	{
		if (!is_array( self::$config[$name] ))
		{
			self::$config[$name] = $parameters;
		}
		else
		{
			self::$config[$name] = array_merge(self::$config[$name], $parameters);
		}
		
		return true;
	}
	
	##################
	# Variables
	##################
	
	/**
	 * Set a variable
	 *
	 * @param string $name The name of the value to store
	 * @param mixed $value The value of the parameter to store
	 */
	public static function set($name, $value)
	{
		self::$variables[$name] = $value;
		
		return $value;
	}
	
	/**
	 * Get a variable
	 *
	 * @param string $name The name of the variable to retrive
	 */
	public static function get($name)
	{
		return self::$variables[$name];
	}
	
	##################
	# Objects
	##################
	
	/**
	 * The main object function
	 *
	 * @param string $name The name of the object to access or create
	 * @param array $config The configuration for the created object
	 */
	public static function obj($name, $config=array())
	{
		// Object does not exist
		if (!is_object( self::$objects[$name]['instance'] ))
		{
			// Set the configuration
			if (count($config) > 0)
			{
				if (!is_array( self::$objects[$name]['configuration'] ))
				{
					self::$objects[$name]['configuration'] = $config;
				}
				else
				{
					self::$objects[$name]['configuration'] = array_merge(self::$objects[$name]['configuration'], $config);
				}
			}
			
			// Instantiate the object, if possible
			$class = self::$objects[$name]['configuration']['class'];
			$path  = self::$objects[$name]['configuration']['path'];
			
			// include the path file, if class does not exist
			if (!class_exists($class) && file_exists($path))
			{
				require_once( $path ); 
			}
			
			// create the object, if needed
			if (class_exists($class))
			{
				self::$objects[$name]['instance'] = new $class;
				
				return self::$objects[$name]['instance'];
			}			
		}
		// Object exists
		else if (is_object( self::$objects[$name]['instance'] ))
		{
			return self::$objects[$name]['instance'];
		}
		
		return false;
	}
}

/**
 * Instantiate the framework. Minimum PHP 5.3 required.
 */
if (PHP_VERSION_ID >= 50300)
{
	die("Starfish PHP Framework minimum requirements: PHP 5.3");
}
// Create an instance
$starfish &= new starfish();
// Include the aliases file
require_once ( starfish::config() );
?>