<?php
if (!isset($path)) { die(); }

trait registry
{
	// Main function called
    public static function obj($name)
    {
        if (isset(starfish::$config['obj-alias'][$name]))
        {
            $name = starfish::$config['obj-alias'][$name];
        }
        
        return starfish::object_create($name);
    }
	
	// Create an object and store it within the framework
	public static function object_create($name)
	{
		if (!isset(starfish::$objects[$name]))
        {
			starfish::$objects[$name] = starfish::object_init($name);
		}
		
		return starfish::$objects[$name];
	}
	
	// Create an object and just return it (e.g. useful when creating two instances of a mysql connection, using the same object)
	public static function object_init($name)
	{
		// if class exists within the current file
		if (class_exists($name))
		{
			return new $name;
		}
		// if class exists within the system file
		else if (file_exists( starfish::$config['root'] . 'objects/' . $name . '.php' ))
		{
			include( starfish::$config['root'] . 'objects/' . $name . '.php' );
			if (class_exists($name))
			{
				return new $name;
			}
			else
			{
				if (starfish::$config['debug'] == false) { starfish::error(400, "Bad request."); }
				
				starfish::error(400, "Class '".$name."' does not exist.");
				return false;
			}
		}
		// if class exists in the custom required objects list
		elseif (file_exists( starfish::$config['objects'] . $name . '.php' ))
		{
			include( starfish::$config['objects'] . $name . '.php' );
			if (class_exists($name))
			{
				return new $name;
			}
			else
			{
				if (starfish::$config['debug'] == false) { starfish::error(400, "Bad request."); }
				
				starfish::error(400, "Class '".$name."' does not exist.");
				return false;
			}
		}
		// debug error
		elseif (strlen($name) > 0)
		{
			if (starfish::$config['debug'] == false) { starfish::error(400, "Bad request."); }
			
			starfish::error(400, "File '".$name."' does not exist.");
			return false;
		}
		// silent error
		else
		{
			starfish::error(400, "Bad request.");
			return false;
		}
	}
	
	public static function object_exists($file, $path='./', $class=null)
	{
		if ($class == null) { $class = $file; }
		
		// If in the registry
		if (isset(starfish::$objects[$class])) { return starfish::$objects[$class]; }
		
		// If not an object yet
		if (class_exists($class)) { return starfish::object_store($class, new $class); }
		
		// If it's in a different file
		if (file_exists( $path . DIRECTORY_SEPARATOR . $file . '.php' ))
		{
			include( $path . DIRECTORY_SEPARATOR . $file . '.php' );
			if (class_exists($class))
			{
				return starfish::object_store($class, new $class);
			}
			else
			{
				return false;
			}
		}
		
		return false;
	}
	
	// Create an alias for an object
	public static function object_alias($name, $alias=null)
	{
		if ($alias != null)
		{
			starfish::$config['obj-alias'][$alias] = $name;
		}
		
		return true;
	}
	
	// Store an external object into the Registry, for later use
	public static function object_store($name, $object)
	{
		if (is_object($object))
		{
			starfish::$objects[$name] = $object;
		}
		elseif (class_exists($object))
		{
			starfish::$objects[$name] = new $object;
		}
		else
		{
			return false;
		}
		
		return $object;
	}
}

?>