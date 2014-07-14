<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for routes
 *
 * @uses https://github.com/fightbulc/simplon_router
 * 
 * @package starfish
 * @subpackage starfish.system.routes
 */

class routes
{
	/**
	 * Declare used variables
	 *
	 * $routes - Routes stored
	 * $wildcards - Formats that resource the parametes can take
	 */
    private static $routes;
    private static $wildcards = array(
		'num' 		=> '([0-9]+)',
		'alpha' 	=> '([0-9A-Za-z_\-]+)',
		'hex' 		=> '([0-9A-Fa-f]+)',
		'base64' 	=> '([0-9A-Za-z+/=.\-_]+)',
		'query' 	=> '\?(.*?)',
		'all' 		=> '*(.*?)'
	);
	
	/**
	 * Init the object
	 */
    public static function init()
    {
    }
	
	/**
	 * Establish a route
	 *
	 * @param string $method Method used to access the resource
	 * @param string $path Resource path
	 * @param mixed $callback What to do when the route has been accessed
	 */
	public static function on($method, $path, $callback)
	{
		$method = strtoupper($method);
		$path = self::compile($path);
		
		// Store the route, by making sure there are no conflicts
		self::$routes[ $method . $path ] = array(
			'method' 	=> $method,
			'path'		=> $path,
			'callback' 	=> $callback
		);
	}
	
	/**
	 * Run the routing
	 */
	public static function run()
	{
		foreach (self::$routes as $key=>$value)
		{
			$method = $value['method'];
			$regex = $value['path'];
			$callback = $value['callback'];
			
			// called path() from ./system/parameters.php
			if (preg_match('/' . $regex . '/ui', path(), $matched))
			{
				// leave out the match
				array_shift($matched);
				
				// if :all wildcard, split result by "/"
				if(strpos($regex, $_wildcardTypes['all']) !== FALSE)
				{
				  $matched = explode('/', $matched[0]);
				}
				
				// callback home with found params
				call_user_func_array($callback, $matched);
				
				// and we stop
				return true;
			}
		}
		
		return true;
	}
	
	/**
	 * Compile the route
	 *
	 * @param string $path The path to compile into the routers memory
	 */
	private static function compile($path)
	{
		// Replace the regex-es
		foreach (self::$wildcards as $key=>$value)
		{
			$path = str_replace( ':' . $key, $value, $path );
		}
		
		// leading backslash
		$path = '/' . ltrim($path, '/');
		
		// final slash
		$path .= '/*';
		
		// escape fwd slash
		$path = str_replace('/', '\/', $path);
		
		return "^$path$";
	}
	
}
?>