<?php
/**
 * Starfish PHP Framework is a minimum Registry microframework primarily desiged to serve JSON content and use objects.
 * 
 * @link        http://www.starfish.ml
 * 
 * @author  	Cristi DRAGHICI
 * @link    	http://blog.draghici.net
 * @version 	0.3a
 * 
 * @see     	Parts from Dispatch PHP micro-framework were used.
 * @link    	https://github.com/noodlehaus/dispatch
 *
 * @see         Simplon Router
 * @link    	https://github.com/fightbulc/simplon_router
 *
 * @see         http://stackoverflow.com/questions/4000483/how-download-big-file-using-php-low-memory-usage
 * 
 * @license 	MIT
 * @link    	http://opensource.org/licenses/MIT
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
	 * $initialized - boolean Needs to be true for Starfish to run
     *
	 * $config    - configuration variables
     * $constants - Starfish "constants": framework specific values for later use
	 * $variables - variable values set throughout the application and accessible from anywhere
	 * 
	 * $objects 	- a list of objects 
	 * $instances 	- a list of objects instantiated
	 */
	private static $initialized = false;

	public static $config = array(
		'_starfish'=>array(
			// Establish the default timezone
			'date_default_timezone' => 'UTC',

			// Set the default debug value
			'debug' => true,

			// Set the path for the application
			'app' => './',

			// Set the name of the application
			'name' => 'starfish',

			// Set the path for the application
			'session' => 'starfish',

			// Set some aliases (just playing around :) )
			'aliases' => array( 's', 'star', 'reg', 'registry')
		)
	);
	public static $constants;
	public static $variables;

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
	private static $instances;

	##################
	# Framework functions
	##################

	/**
	 * Init the framework
	 * 
	 * @param array $objects Objects to preload
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
			@ini_set('display_errors', false);
		}
		else
		{
			error_reporting(E_ALL | E_STRICT);
			@ini_set('display_errors', true);
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

		return null;
	}

	/**
	 * Preload certain objects
	 * This is useful for using the alias functions
	 * 
	 * @param array $objects List of objects to load
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
	 *
	 * @param string $module Module name
	 * @param mixed  $names The names of the configuration options to return or to store
	 * @param mixed  $values The values to store
         * @param boolean $override Defaults to: true. If false, then the new value is not set if one already exists
	 */
	public static function config($module, $names, $values=null, $override=true)
	{
		// Initial values to work with
		$return = null;

		$config = isset(static::$config[$module]) ? static::$config[$module] : array();
		$type   = array(
			'names' => gettype($names),
			'values'=> gettype($values)
		);

		// Return values
		if ($type['values'] == 'NULL')
		{
			// One name
			if ($type['names'] == 'string' && isset($config[ $names ]) )
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
	* Configuration array - Pass the configuration values through an array
	*
	* @param string $module Module name
	* @param mixed  $array The array containing the values
	* @param boolean $override Defaults to: true. If false, then the new value is not set if one already exists
	*/
	public static function configArray($module, $array=array(), $override=true)
	{
		foreach ($array as $key=>$value)
		{
			static::$config[$module][ $key ] = $value;
		}

		return true;
	}

	##################
	# Variables
	##################

	/**
	 * Set a variable
	 *
	 * @param mixed $name The name of the value to store
	 * @param mixed $value The value of the parameter to store
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
	 *
	 * @param string $name The name of the variable to retrive
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
	 *
	 * @param string $name The name of the object to access or create
	 * @param array $configuration The configuration for the created object. Parameters: path, class
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
		else
		{
			// Check if a configuration already exists
			if ( isset(static::$objects[$name]) && is_array(static::$objects[$name]) )
			{
				$configuration = array_merge( static::$objects[$name], $configuration );
			}

			// Name of the class
			$class = isset( static::$objects[$name]['class'] ) ? static::$objects[$name]['class'] : $name;

			// Include the files, if needed
			if (!class_exists($class))
			{
				$path = isset( static::$objects[$name]['path'] ) ? static::$objects[$name]['path'] : null;
				$path = $path == null && isset($configuration['path']) ? $configuration['path'] : null;
				
				if ($path != null)
				{
					require_once( $path );
				}
				else
				{
					$core	 	= static::config('_starfish', 'core_objects') . DIRECTORY_SEPARATOR . $name .'.php';
					$objects 	= static::config('_starfish', 'root_objects') . DIRECTORY_SEPARATOR . $name .'.php';
					$application 	= static::config('_starfish', 'app_objects') . DIRECTORY_SEPARATOR . $name .'.php';

					if (file_exists($core)) { require_once( $core ); }
					else if (file_exists($objects)) { require_once( $objects ); }
					else if (file_exists($application)) { require_once( $application ); }
				}
			}

			// Create the class
			if (class_exists($class))
			{
				// Create the object
				$object = new $class;

				// Run the init method, if it exists
				if (method_exists($object, 'init')) { $object->init(); }

				// Run the routing registration method, if it exists
				if (method_exists($object, 'routes')) { $object->routes(); }

				// Store the object
				static::$instances[$name] = $object;

				// Return the object
				return $object;
			}
		}

		return null;
	}

	/**
	 * Store an exising object inside the registry
	 *
	 * @param string $name The name of the object to access or create
	 * @param array $configuration The configuration for the created object. Parameters: path, class
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
	 * Just create an object, without storing it into the registry
	 *
	 * @param string $name The name of the object to store
	 * @param object $object The object itself
	 * 
	 * @return boolean Returns whether the object has been stored or not
	 */
	public static function access($name, $configuration=array())
	{
		$object = static::obj($name, $configuration);

		unset( static::$instances[ $name ]);

		return $object;
	}

	/**
	 * To a redirect
	 *
	 * @param string $path The path to redirect to
	 * @param string $code Code of the redirect
	 * @param string $condition True condition to make the redirect
	 */
	public static function redirect($path, $code = 302, $condition = true) 
	{
		if (!$condition) { return; }

		if (substr($path, 0, 2) == './') { $path = static::config('_starfish', 'site_url') . substr($path, 2); }

		@header("Location: {$path}", true, $code);
		exit;
	}

	/**
	 * Get the internal memory usage
	 * 
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
	*
	* @return string The execution time in human readable format
	*/
	public static function execution_time()
	{
		$difference = time() - static::$constants['execution_time_start'];
		return static::obj('epoch')->seconds_to_readable($difference);
	}

	/** 
	 * Model/View/Controller support functions
	 * 
	 * @todo Yet to be implemented
	 */
	public static function c($name)
	{
		return true;
	}
	public static function m($name)
	{
		return true;
	}
	public static function v($name, $data=array())
	{
		return true;
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

/**
 * Instantiate the framework. Minimum PHP 5.3 required.
 */
if (PHP_VERSION_ID <= 50300)
{
	die("Starfish PHP Framework minimum requirements: PHP 5.3");
}
?>