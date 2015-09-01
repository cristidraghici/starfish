<?php
/**
 * Starfish PHP Framework - A minimum Registry microframework primarily desiged to serve JSON content and use objects.
 * 
 * This file is the entry point of Starfish PHP Framework: this acts like a Registry for all the other objects used
 * 
 * @link        http://www.starfish.ml
 * @version 	1.0
 * 
 * @license 	MIT
 * @link    	http://opensource.org/licenses/MIT
 * 
 * @author  	Cristi DRAGHICI
 * @link    	http://blog.draghici.net
 * 
 * 
 * @todo The database system needs improvements
 * 
 * @package starfish
 * @subpackage starfish
 */

class starfish
{
	/*
	 * Boolean Needs to be true for Starfish to run. The value changes upon running the init() method
	 */
	private static $initialized = false;

	/*
	 * Default configuration variables
	 */
	public static $config = array(
		'_starfish'=>array(
			// Establish the default timezone
			'date_default_timezone' => 'UTC',

			// Set the default debug value
			'debug' => true,
			
			// If true, all used system objects are stored in a file in the app root .starfish
			'deployment' => false,

			// Set the path for the application
			'app' => './',

			// Set the name of the application
			'name' => 'starfish',

			// Set the path for the application
			'session' => 'starfish',

			// Set some aliases (just playing around :) )
			'aliases' => array('registry')
		)
	);

	/*
	 * Starfish "constants": framework specific values for later use
	 */
	public static $constants;

	/*
	 * Variable values set throughout the application and accessible from anywhere
	 */
	public static $variables;

	/*
	 * The list of objects
	 */
	private static $objects = array(
		'errors'=>array(
			'path'	=> 'system/errors.php',
			'class'	=> 'errors'
		),

		'parameters'=>array(
			'path'	=> 'system/parameters.php',
			'class'	=> 'parameters'
		),

		'routes'=>array(
			'path'	=> 'system/routes.php',
			'class'	=> 'routes'
		)
	);

	/*
	 * The list of instances
	 */
	private static $instances;


	##################
	# Framework functions
	##################

	/**
	 * Init the framework
	 * @param  array [$objects=array()] List of objects to load
	 * @return null  Returns nothing yet
	 */
	public static function init($objects=array())
	{
		// Initialization
		if (static::$initialized == true) { die('starfish::init() can be run only once!'); }
		else
		{
			static::$initialized = true;
		}

		// Set the debugger
		if (starfish::config('_starfish', 'debug') == false)
		{
			error_reporting(0);
			@ini_set('display_errors', 'off');
		}
		else
		{
			error_reporting(E_ALL);
			@ini_set('display_errors', 'on');
		}

		// Establish the default timezone
		$config = static::config('_starfish', 'date_default_timezone', "UTC", false );
		@date_default_timezone_set( $config );

		// Establish the operating system
		if (strncasecmp(PHP_OS, 'WIN', 3) == 0) 
		{
			static::$constants['operating_system'] = 'Win';
		}
		else
		{
			static::$constants['operating_system'] = 'Non';
		}

		// Store the time Starfish started
		static::$constants['execution_time_start'] = time();

		// Get and store more operating system information
		static::$constants['php_uname'] = php_uname();

		// Get and set CLI status
		static::$constants['cli'] = ( php_sapi_name() == 'cli' ) ? true : false;

		// Check if it is an ajax request
		static::$constants['ajax'] = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;

		// Get and set client IP address
		static::$constants['ip'] = static::get_client_ip();

		// Register aliases
		if (static::config('_starfish', 'aliases') != null && is_array( static::config('_starfish', 'aliases') ))
		{
			foreach (static::config('_starfish', 'aliases') as $key=>$value)
			{
				class_alias('starfish', $value);
			}
		}

		// Set the project name (used in e.g. Session variable names)
		static::config('_starfish', 'project', 'Starfish', false);

		// Set the path for Starfish Framework files
		$path = static::config('_starfish', 'root', @realpath(__DIR__) . DIRECTORY_SEPARATOR);


		// Update the initial object list paths
		foreach (static::$objects as $key=>$value)
		{
			static::$objects[$key]['path'] = $path . $value['path'];
		}
		// Set the path for Starfish Framework objects		
		static::config('_starfish', 'core_objects', $path . 'system'  . DIRECTORY_SEPARATOR);

		static::config('_starfish', 'root_objects', $path . 'objects' . DIRECTORY_SEPARATOR, false );
		static::config('_starfish', 'app_objects',  $path . 'application' . DIRECTORY_SEPARATOR, false );

		// Set the path for Starfish storage		
		static::config('_starfish', 'storage', $path . 'storage'  . DIRECTORY_SEPARATOR, false);

		// Set the path for Starfish storage		
		static::config('_starfish', 'template', $path . 'template'  . DIRECTORY_SEPARATOR, false);

		// Set the path for Starfish root storage		
		static::config('_starfish', 'root_storage', $path . 'storage'  . DIRECTORY_SEPARATOR);

		// Proper initialization
		static::obj('parameters');
		static::obj('files');
		static::obj('routes');
		static::obj('databases');
		static::obj('errors');
		static::obj('logs');
		static::obj('email');

		static::preload($objects);

		// Set the current url
		static::$constants['current_url'] = starfish::config('_starfish', 'site_url') . substr( static::obj('parameters')->path() , 1);

		// Set the current url php based only
		static::$constants['current_url_raw'] = "http".(isset($_SERVER['HTTPS']) ? 's' : '')."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		return null;
	}

	/**
	 * Preload certain objects
	 * This is useful for using the alias functions
	 * @param  array [$objects=array()] List of objects to load
	 * @return null  Noting to return
	 */
	public static function preload($objects=array())
	{
		if (gettype($objects) == 'string') { $objects = array($objects); }

		foreach ($objects as $key=>$value)
		{
			static::obj($value);
		}

		return null;
	}

	/**
     * Configuration function
     * @param  string  $module          Module name
     * @param  mixed   [$names=null]    The names of the configuration options to return or to store
     * @param  mixed   [$values=null]   The values to store
     * @param  boolean [$override=true] If false, then the new value is not set if one already exists
     * @return mixed   A list of all variables set
     */
	public static function config($module, $names=null, $values=null, $override=true)
	{
		// Initial values to work with
		$return = null;

		$config = isset(static::$config[$module]) ? static::$config[$module] : array();
		$type   = array(
			'names' => gettype($names),
			'values'=> gettype($values)
		);

		// Bulk set in modules
		if ($type['names'] == 'array' && $values == true)
		{
			static::$config[$module] = $names;
			$return = $names;
		}
		// Return values
		elseif ($type['values'] == 'NULL')
		{
			// No name
			if ($type['names'] == 'NULL')
			{
				$return = $config;
			}
			// One name
			elseif ($type['names'] == 'string' && isset($config[ $names ]) )
			{
				$return = $config[ $names ];
			}
			// More names
			elseif ($type['names'] == 'array')
			{
				foreach ($names as $key=>$value)
				{
					$return[ $value ] = isset($config[ $value ]) ? $config[ $value ] : null;
				}
			}
		}
		// Set some values
		else
		{
			// One value
			if ($type['values'] == 'string' || $type['names'] == 'string')
			{
				// One value, one name
				if ($type['names'] == 'string')
				{
					if ($override == false && isset(static::$config[$module][ $names ]))
					{
						$return = static::$config[$module][ $names ];
					}
					else
					{
						static::$config[$module][ $names ] = $values;
						$return = $values;
					}
				}
				// One value, more names
				elseif ($type['names'] == 'array')
				{
					foreach ($names as $key=>$value)
					{
						if ($override == false && isset(static::$config[$module][ $value ]))
						{
							$return = static::$config[$module][ $value ];
						}
						else
						{
							static::$config[$module][ $value ] = $values;
							$return[ $value ] = $values;
						}
					}
				}
			}
			// More values
			elseif ($type['values'] == 'array')
			{
				// Ensure that the $names is an array
				if ($type['names'] != 'array') { $names = array($names); }

				// Assign the values
				$names = array_values($names);
				$values = array_values($values);

				for ($a=0; $a<count($names); $a++)
				{
					if ($override == false && isset(static::$config[$module][$names[$a]]))
					{
						$return[ $names[$a] ] = static::$config[$module][$names[$a]];
					}
					else
					{
						static::$config[$module][$names[$a]] = $values[$a];
						$return[ $names[$a] ] = $values[$a];
					}
				}
			}
		}

		// Exception for "base" name
		if ($names == 'base' && $type['values'] == 'array')
		{
			foreach ($values as $key=>$value)
			{
				static::$config[$module][ $key ] = $value;
			}
		}

		return $return;
	}

	/**
	 * Configuration array
	 * Pass the configuration values through an array
	 * @param  string   $module          Module name
	 * @param  mixed    [$array=array()] The array containing the values
	 * @param  [[Type]] $override=true   [[Description]]
	 * @return null     Noting to return
	 */
	public static function configArray($module, $array=array(), $override=true)
	{
		foreach ($array as $key=>$value)
		{
			static::$config[$module][ $key ] = $value;
		}

		return null;
	}

	##################
	# Variables
	##################

	/**
	 * Set a variable
	 * @param  mixed $name  The name of the value to store
	 * @param  mixed $value The value of the parameter to store
	 * @return mixed The value set
	 */
	public static function set($name, $value)
	{
		// Check framework initialization
		if (static::$initialized == false) { die('starfish::init() command must be run within your script!'); }

		// Give a standard form to the variable name
		$type = @gettype($name);
		if ($type == 'array') { @ksort($name); }
		$name = @serialize($name);

		// Store the variable
		static::$variables[$name] = $value;

		return $value;
	}

	/**
	 * Get a variable
	 * @param  string $name The name of the variable to retrive
	 * @return mixed  The value for the requested variable
	 */
	public static function get($name)
	{
		// Check framework initialization
		if (static::$initialized == false) { die('starfish::init() command must be run within your script!'); }

		// Give a standard form to the variable name
		$type = @gettype($name);
		if ($type == 'array') { @ksort($name); }
		$name = @serialize($name);

		// Get the variable
		return isset(static::$variables[$name]) ? static::$variables[$name] : null;
	}

	##################
	# Objects
	##################

	/**
	 * The main object function
	 * @param  string $name                    The name of the object to access or create
	 * @param  array  [$configuration=array()] Configuration for the object
	 * @return object The object iself
	 */
	public static function obj($name, $configuration=array())
	{		
		// Check framework initialization
		if (static::$initialized == false) { die('starfish::init() command must be run within your script!'); }

		// Object exists
		if (isset(static::$instances[ $name ]) && is_object( static::$instances[ $name ] ))
		{
			return static::$instances[ $name ];
		}
		// Class is loaded, mainly used when in one-file-framework mode
		elseif (class_exists($name))
		{
			// Create the object
			$object = new $name;

			// Run the init method, if it exists
			if (method_exists($object, 'init')) { $object->init(); }

			// Run the routing registration method, if it exists
			if (method_exists($object, 'routes')) { $object->routes(); }

			// Store the object
			static::$instances[$name] = $object;
			
			// Store the configuration in the objects list
			static::$objects[$name] = $configuration;
			
			// Return the object
			return $object;
		}
		else
		{
			// Check if a configuration already exists
			if ( isset(static::$objects[$name]) && is_array(static::$objects[$name]) )
			{
				$configuration = array_merge( static::$objects[$name], $configuration );
			}
			
			// Complete the configuration
			if (!isset($configuration['path']))
			{
				$core	 	= static::config('_starfish', 'core_objects')  . DIRECTORY_SEPARATOR . $name .'.php';
				$objects 	= static::config('_starfish', 'root_objects')  . DIRECTORY_SEPARATOR . $name .'.php';
				$application 	= static::config('_starfish', 'app_objects') . DIRECTORY_SEPARATOR . $name .'.php';

				if (file_exists($core)) 
				{ 
					$configuration['path'] = $core;
					static::storeUsedModule($name, $core);
				} 
				else if (file_exists($objects)) 
				{ 
					$configuration['path'] = $objects;
					static::storeUsedModule($name, $objects);
				} 
				else if (file_exists($application)) 
				{ 
					$configuration['path'] = $application;
				}
				else
				{
					$configuration['path'] = null;
				}
				
			}
			elseif (stristr($configuration['path'], starfish::config('_starfish', 'root')))
			{
				static::storeUsedModule($name, $configuration['path']);
			}

			// Name of the class
			$configuration['class'] = isset( $configuration['class'] ) ? $configuration['class'] : $name;

			// Include the files, if needed
			if (!class_exists($configuration['class']))
			{
				
				if ($configuration['path'] != null)
				{
					require_once( $configuration['path'] );
					
					if (class_exists($configuration['class']))
					{
						// Create the object
						$object = new $configuration['class'];

						// Run the init method, if it exists
						if (method_exists($object, 'init')) { $object->init(); }

						// Run the routing registration method, if it exists
						if (method_exists($object, 'routes')) { $object->routes(); }

						// Store the object
						static::$instances[$name] = $object;
						
						// Store the configuration in the objects list
						static::$objects[$name] = $configuration;
						
						// Return the object
						return $object;
					}
				}
			}
		}
		return null;
	}

	/**
	 * Store an exising object inside the registry
	 * @param  string  $name   The name of the object to access or create
	 * @param  object  $object The object to store
	 * @return boolean True if the object exists and was stored, false on contrary
	 */
	public static function store($name, $object)
	{
		if (!isset( static::$instances[ $name ] ) && is_object($object))
		{
			static::$instances[ $name ] = $object;

			return true;
		}

		return false;
	}

	/**
     * Check if an object exists
     * @param  strin   $name Name of the object
     * @return boolean Whether the object exists or not
     */
	public static function exists($name)
	{
		if (isset( static::$instances[ $name ] )) {
			return true;
		}

		return false;
	}

	/**
	 * Just create an object, without storing it into the registry
	 * @param  string $name                    The name of the object to store
	 * @param  array  [$configuration=array()] The configuration for the object
	 * @return object The object itself
	 */
	public static function access($name, $configuration=array())
	{
		$object = static::obj($name, $configuration);

		unset( static::$instances[ $name ]);

		return $object;
	}

	/**
	 * To a redirect
	 * @param string  $path             The path to redirect to
	 * @param integer [$code=302]       Default error code
	 * @param boolean [$condition=true] Condition to interpret before redirect
	 */
	public static function redirect($path, $code=302, $condition=true) 
	{
		if (!$condition) { return; }
		if (substr($path, 0, 2) == './') { $path = static::config('_starfish', 'site_url') . substr($path, 2); }
		@header("Location: {$path}", true, $code);
		exit;
	}

	/**
	 * Get the internal memory usage
	 * @return string String showing readable memory usage
	 */
	public static function memory_usage() 
	{
		$mem_usage = memory_get_usage(true);

		if ($mem_usage < 1024)
		{
			return $mem_usage." bytes";
		}
		elseif ($mem_usage < 1048576)
		{
			return round($mem_usage/1024,2)." KB";
		}

		else
		{
			return round($mem_usage/1048576,2)." MB";
		}

		return "";
	}

	/**
	 * Get the execution time for the script
	 * @return string The execution time in human readable format
	 */
	public static function execution_time()
	{
		$difference = time() - static::$constants['execution_time_start'];
		return static::obj('epoch')->seconds_to_readable($difference);
	}

	/**
	 * Get the IP address of the client
	 * See http://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
	 * @return string The ip addpress of the client
	 */
	public static function get_client_ip() 
	{
		$string = '';
		$verify = array_values(array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'));

		for ($a=0; $a<count($verify) && $string == ''; $a++) 
		{
			if (isset($_SERVER[$verify[$a]]) && $value = trim($_SERVER[$verify[$a]]))
			{
				if (starfish::config('_starfish', 'debug') == false)
				{
					$string = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? $value : '';
				}
				else
				{
					$string = filter_var($value, FILTER_VALIDATE_IP) ? $value : '';
				}
			}
		}

		return $string;
	}

	##################
	# Model/View/Controller support functions
	##################

	/**
	 * Controller
	 * @param  string  $name Name of the controoler to access
	 * @return boolean Temporary
	 */
	public static function c($name)
	{
		return obj($name . '-controller', array(
			'path'=>static::config('_starfish', 'app_objects') . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $name .'.php',
			'class'=>$name
		));
	}
	/**
	 * Model
	 * @param  string  $name Name of the model to access
	 * @return boolean Temporary
	 */
	public static function m($name)
	{
		return obj($name . '-model', array(
			'path'=>static::config('_starfish', 'app_objects') . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $name .'.php',
			'class'=>$name . 'Model'
		));
	}

	/**
	 * View
	 * @param  string  $name           Name of the template
	 * @param  array   [$data=array()] Values to interpret in the template
	 * @return boolean Temporary
	 */
	public static function v($name, $data=array())
	{
		return static::obj('tpl')->view($name, $data);
	}
	
	
	##################
	# Store the used Starfish modules in a list, in order to build a one-file script with the framework
	##################
	
	public static function storeUsedModule($name, $path)
	{
		if (static::config('_starfish', 'deployment') == true && static::config('_starfish', 'root_app') != null)
		{
			$file = static::config('_starfish', 'root_app') . '.starfish';
			$json = '';
			
			// Read the existing contents
			if (file_exists($file) && is_file($path) && is_readable($path))
			{
				$handler = @fopen($file, "r");
				$size = filesize($file);
				if ($size == 0)
				{
					$size = "32";
				}
				$content = @fread($handler, $size);
				@fclose($handler);
				
				$json = @json_decode($content, true);
			}
			
			// Store the object
			$json[$name] = $path;
			
			// Write the file containing the list of modules
			$handler = @fopen($file, 'w');
			@fwrite($handler, @json_encode($json));
			@fclose($handler);
			
			// Create the one file microframework
			$code = '';
			$json = array_reverse($json);
			$json['starfish'] = starfish::config('_starfish', 'root') . 'starfish.php';
			$json = array_reverse($json);
			foreach ($json as $name=>$file)
			{
				$handler = @fopen($file, "r");
				$size = filesize($file);
				if ($size == 0)
				{
					$size = "32";
				}
				$content = @fread($handler, $size);
				@fclose($handler);
				
				$content = str_replace("if (!class_exists('starfish')) { die(); }", '', $content);
				
				$code .= $content;
			}
			$code = str_replace('?'.'><'.'?php', '', $code);
			
			// Write the file containing the microframework
			$handler = @fopen(static::config('_starfish', 'root_app') . 'starfish.php', 'w');
			@fwrite($handler, $code);
			@fclose($handler);
		}
	}
}

/**
* Aliases used by class for easier programming
*/
function init()   { return call_user_func_array(array('starfish', 'init'),    func_get_args()); }
function obj()   { return call_user_func_array(array('starfish', 'obj'),    func_get_args()); }
function config()   { return call_user_func_array(array('starfish', 'config'),    func_get_args()); }
function redirect()   { return call_user_func_array(array('starfish', 'redirect'),    func_get_args()); }

function getVar()   { return call_user_func_array(array('starfish', 'get'),    func_get_args()); }
function setVar()   { return call_user_func_array(array('starfish', 'set'),    func_get_args()); }


function c()   { return call_user_func_array(array('starfish', 'c'),    func_get_args()); }
function m()   { return call_user_func_array(array('starfish', 'm'),    func_get_args()); }
function v()   { return call_user_func_array(array('starfish', 'v'),    func_get_args()); }

/**
 * Instantiate the framework. Minimum PHP 5.3 required.
 */
if (PHP_VERSION_ID <= 50300)
{
	die("Starfish PHP Framework minimum requirements: PHP 5.3");
}
?>