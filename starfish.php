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
        // Establish the default timezone
		$config = self::config('_starfish', 'date_default_timezone', (self::config('_starfish', 'date_default_timezone') != null) ? self::config('_starfish', 'date_default_timezone') : "UTC" );
        @date_default_timezone_set( $config );
        
		// Set the path for the application
		$path = self::config('_starfish', 'app', './');
		
		// Set the path for Starfish Framework files
		$path = self::config('_starfish', 'root', @realpath(__DIR__) . DIRECTORY_SEPARATOR);
		
		// Create the initial objects list
		self::$objects = array(
			'errors'=>array(
				'instance'		=> null,
				'configuration'	=> array(
					'path'	=> $path . 'system/errors.php',
					'class'	=> 'errors',
					'type'	=> 'core'
				)
			),
            
			'objects'=>array(
				'instance'		=> null,
				'configuration'	=> array(
					'path'	=> $path . 'system/objects.php',
					'class'	=> 'objects',
					'type'	=> 'core'
				)
			),
            
			'parameters'=>array(
				'instance'		=> null,
				'configuration'	=> array(
					'path'	=> $path . 'system/parameters.php',
					'class'	=> 'parameters',
					'type'	=> 'core'
				)
			),
            
			'routes'=>array(
				'instance'		=> null,
				'configuration'	=> array(
					'path'	=> $path . 'system/routes.php',
					'class'	=> 'routes',
					'type'	=> 'core'
				)
			)
		);
		
		// Proper initialization
		self::obj('parameters')->init();
		self::obj('objects')->init();
		
		return self;
	}
	
	/**
	 * Configuration function
	 *
	 * @param string $module Module name
	 * @param mixed  $names The names of the configuration options to return or to store
	 * @param mixed  $values The values to store
	 */
	public static function config($module, $names, $values=null)
    {
        // Initial values to work with
        $return = null;
        $config &= isset(self::$config[$module]) ? self::$config[$module] : array();
        $type   = array(
            'names' => gettype($names),
            'values'=> gettype($values)
        );
        
        // Return values
        if ($type['values'] == null)
        {
            // One name
            if ($type['names'] == 'string')
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
            if ($type['values'] == 'string')
            {
                // One value, one name
                if ($type['names'] == 'string')
                {
                    $config[ $names ] = $values;
                    $return[ $names ] = $values;
                }
                // One value, more names
                elseif ($type['names'] == 'array')
                {
                    foreach ($names as $key=>$value)
                    {
                        $config[ $value ] = $values;
                        $return[ $value ] = $values;
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
                
                for ($a=0; $a++; $a<count($names))
                {
                    $config[$names[$a]] = $values[$a];
                    $return[ $names[$a] ] = $values[$a];
                }
            }
        }
        
        return $return;
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
        // Give a standard form to the variable name
        $type = @gettype($name);
        if ($type == 'array') { @ksort($name); }
        $name = @serialize($name);
        
        // Store the variable
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
        // Give a standard form to the variable name
        $type = @gettype($name);
        if ($type == 'array') { @ksort($name); }
        $name = @serialize($name);
        
        // Get the variable
		return self::$variables[$name];
	}
	
	##################
	# Objects
	##################
	
	/**
	 * The main object function
	 *
	 * @param string $name The name of the object to access or create
	 * @param array $config The configuration for the created object. Parameters: path, class, type(enum: core|app)
	 */
	public static function obj($name, $config=array())
	{
		// Ensure that the object type exists
		// core - in ./system folder
		// objects - in ./objects folder
		// app - in the application folder
		$config['type'] = (isset($config['type']) && in_array($config['type'], array('core', 'objects', 'app')) ) ? $config['type'] : 'app';
		
		// Object does not exist
		if (!is_object( self::$objects[ $config['type'] ][$name]['instance'] ))
		{
			// Set the configuration
			if (count($config) > 0)
			{
				if (!is_array( self::$objects[ $config['type'] ][$name]['configuration'] ))
				{
					self::$objects[ $config['type'] ][$name]['configuration'] = $config;
				}
				else
				{
					self::$objects[ $config['type'] ][$name]['configuration'] = array_merge(self::$objects[ $config['type'] ][$name]['configuration'], $config);
				}
			}
			
			// Instantiate the object, if possible
			$class = self::$objects[ $config['type'] ][$name]['configuration']['class'];
			$path  = self::$objects[ $config['type'] ][$name]['configuration']['path'];
			
			// include the path file, if class does not exist
			if (!class_exists($class) && file_exists($path))
			{
				require_once( $path ); 
			}
			
			// create the object, if needed
			if (class_exists($class))
			{
				self::$objects[ $config['type'] ][$name]['instance'] = new $class;
				
				return self::$objects[ $config['type'] ][$name]['instance'];
			}			
		}
		// Object exists
		else if (is_object( self::$objects[ $config['type'] ][$name]['instance'] ))
		{
			return self::$objects[ $config['type'] ][$name]['instance'];
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
?>