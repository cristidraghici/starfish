<?php
/**
 * Starfish PHP Framework is a minimum Registry microframework primarily desiged to serve JSON content and use objects.
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
	 */
        public static function init()
        {
                // Initialization
                if (self::$initialized == true) { die('starfish::init() can be run only once!'); }
                else
                {
                        self::$initialized = true;
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
                $config = self::config('_starfish', 'date_default_timezone', "UTC", false );
                @date_default_timezone_set( $config );

                // Establish the operating system
                if (strncasecmp(PHP_OS, 'WIN', 3) == 0) 
                {
                        self::$constants['operating_system'] = 'Win';
                }
                else
                {
                        self::$constants['operating_system'] = 'Non';
                }

                // Get and store more operating system information
                self::$constants['php_uname'] = php_uname();

                // Register aliases
                if (self::config('_starfish', 'aliases') != null && is_array( self::config('_starfish', 'aliases') ))
                {
                        foreach (self::config('_starfish', 'aliases') as $key=>$value)
                        {
                                class_alias('starfish', $value);
                        }
                }

                // Set the path for Starfish Framework files
                $path = self::config('_starfish', 'root', @realpath(__DIR__) . DIRECTORY_SEPARATOR);

                // Update the initial object list paths
                foreach (self::$objects as $key=>$value)
                {
                        self::$objects[$key]['path'] = $path . $value['path'];
                }
                // Set the path for Starfish Framework objects		
                self::config('_starfish', 'core_objects', $path . 'system'  . DIRECTORY_SEPARATOR);

                self::config('_starfish', 'root_objects', $path . 'objects' . DIRECTORY_SEPARATOR, false );
                self::config('_starfish', 'app_objects',  $path . 'application' . DIRECTORY_SEPARATOR, false );

                // Set the path for Starfish storage		
                self::config('_starfish', 'storage', $path . 'storage'  . DIRECTORY_SEPARATOR, false);

                // Set the path for Starfish root storage		
                self::config('_starfish', 'root_storage', $path . 'storage'  . DIRECTORY_SEPARATOR);

                // Proper initialization
                self::obj('parameters');

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

                $config = isset(self::$config[$module]) ? self::$config[$module] : array();
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
                        if ($type['values'] == 'string')
                        {
                                // One value, one name
                                if ($type['names'] == 'string')
                                {
                                        if ($override == false && isset(self::$config[$module][ $names ]))
                                        {
                                                $return = self::$config[$module][ $names ];
                                        }
                                        else
                                        {
                                                self::$config[$module][ $names ] = $values;
                                                $return = $values;
                                        }
                                }
                                // One value, more names
                                elseif ($type['names'] == 'array')
                                {
                                        foreach ($names as $key=>$value)
                                        {
                                                if ($override == false && isset(self::$config[$module][ $value ]))
                                                {
                                                        $return = self::$config[$module][ $value ];
                                                }
                                                else
                                                {
                                                        self::$config[$module][ $value ] = $values;
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

                                for ($a=0; $a++; $a<count($names))
                                {
                                        if ($override == false && isset(self::$config[$module][$names[$a]]))
                                        {
                                                $return[ $names[$a] ] = self::$config[$module][$names[$a]];
                                        }
                                        else
                                        {
                                                self::$config[$module][$names[$a]] = $values[$a];
                                                $return[ $names[$a] ] = $values[$a];
                                        }
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
                // Check framework initialization
                if (self::$initialized == false) { die('starfish::init() command must be run within your script!'); }

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
                // Check framework initialization
                if (self::$initialized == false) { die('starfish::init() command must be run within your script!'); }

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
	 * @param array $configuration The configuration for the created object. Parameters: path, class
	 */
        public static function obj($name, $configuration=array())
        {		
                // Check framework initialization
                if (self::$initialized == false) { die('starfish::init() command must be run within your script!'); }

                // Object exists
                if (isset(self::$instances[ $name ]) && is_object( self::$instances[ $name ] ))
                {
                        return self::$instances[ $name ];
                }
                else
                {
                        // Check if a configuration already exists
                        if ( isset(self::$objects[$name]) && is_array(self::$objects[$name]) )
                        {
                                $configuration = array_merge( self::$objects[$name], $configuration );
                        }

                        // Name of the class
                        $class = isset( self::$objects[$name]['class'] ) ? self::$objects[$name]['class'] : $name;

                        // Include the files, if needed
                        if (!class_exists($class))
                        {
                                $path = isset( self::$objects[$name]['path'] ) ? self::$objects[$name]['path'] : null;

                                if ($path != null)
                                {
                                        require_once( $path );
                                }
                                else
                                {
                                        $core	 	= self::config('_starfish', 'core_objects') . DIRECTORY_SEPARATOR . $name .'.php';
                                        $objects 	= self::config('_starfish', 'root_objects') . DIRECTORY_SEPARATOR . $name .'.php';
                                        $application 	= self::config('_starfish', 'app_objects') . DIRECTORY_SEPARATOR . $name .'.php';

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

                                // Store the object
                                self::$instances[$name] = $object;

                                // Return the object
                                return $object;
                        }
                }

                return null;
        }
        
        /**
	 * Store an exising object inside the registry
	 *
	 * @param string $name The name of the object to store
	 * @param object $object The object itself
	 * 
	 * @return boolean Returns whether the object has been stored or not
	 */
        public static function store($name, $object)
        {
                if (!isset( self::$instances[ $name ] ) && is_object($object))
                {
                        self::$instances[ $name ] = $object;
                        
                        return true;
                }
                
                return false;
        }
}

/**
* Aliases used by class for easier programming
*/
function obj()   { return call_user_func_array(array('starfish', 'obj'),    func_get_args()); }
function config()   { return call_user_func_array(array('starfish', 'config'),    func_get_args()); }

/**
 * Instantiate the framework. Minimum PHP 5.3 required.
 */
if (PHP_VERSION_ID <= 50300)
{
        die("Starfish PHP Framework minimum requirements: PHP 5.3");
}
?>