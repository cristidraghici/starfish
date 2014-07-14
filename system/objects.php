<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for loading objects outside the Starfish core
 *
 * @package starfish
 * @subpackage starfish.system.objects
 */
class objects
{
	/**
	 * Init the object
	 */
    public static function init()
    {
        // Set the path for Starfish Framework objects
		self::config('_starfish', 'root_objects', self::config('_starfish', 'root') . DIRECTORY_SEPARATOR . 'objects' );
		self::config('_starfish', 'app_objects',  self::config('_starfish', 'app') . DIRECTORY_SEPARATOR . 'application' );
    }
	
	/**
	 * The main function
	 *
	 * @param string $name The name of the object to access or create
	 * @param array $config The configuration for the created object
	 *
	 * @todo Include MVC support
	 */
	public static function obj($name, $config=array())
	{
		// Ensure that the object type exists
		$type = (isset($config['type']) && in_array($config['type'], array('app', 'objects')) ) ? $config['type'] : 'app';
		
		// Set the path to the file
		if (!isset($config['path']) || (isset($config['path']) && file_exists($config['path'])))
		{
			$core = self::config('_starfish', 'root_objects') . DIRECTORY_SEPARATOR . $name .'.php';
			$app  = self::config('_starfish', 'app_objects')  . DIRECTORY_SEPARATOR . $name .'.php';
			
			// is core object file
			if (file_exists($core))
			{
				$path = $core;
				$type = 'objects';
			}
			
			// is application object file
			elseif (file_exists($app))
			{
				$path = $app;
				$type = 'app';
			}
			
			// stop execution, path does not exist
			else
			{
				return false;
			}
		}
		else
		{
			$path = $config['path'];
		}
		
		// Set the class name
		$class = isset($config['class']) ? $config['class'] : $name;
		
		
		// Object does not exist
		if (!is_object( self::$objects[ $type ][$name]['instance'] ))
		{
			return starfish::obj($name, array(
				'path' => $path
				'class'=> $name,
				'type' => $type
			));
		}
		// Object exists
		else if (is_object( self::$objects[ $type ][$name]['instance'] ))
		{
			return self::$objects[ $type ][$name]['instance'];
		}
		
		return false;
	}
}

/**
* Aliases used by class for easier programming
*/
function obj()   { return call_user_func_array(array('objects', 'obj'),    func_get_args()); }
?>