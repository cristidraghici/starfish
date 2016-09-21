<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for routes
 *
 * @uses https://github.com/fightbulc/simplon_router
 * 
 * @see https://github.com/noodlehaus/dispatch
 * @see https://github.com/fightbulc/simplon_router
 * 
 * @package starfish
 * @subpackage starfish.system.routes
 *
 * @todo When running obj(<>)->routes(), skip init()
 *
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
	 * Establish a route / Run the routing function
	 * @param string [$method=null]   Method used to access the resource
	 * @param string [$path=null]     Resource path
	 * @param mixed  [$callback=null] What to do when the route has been accessed
	 */
	public static function on($method=null, $path=null, $callback=null)
	{
		$args = func_get_args();

		if (count($args) == 3)
		{
			$method = strtoupper($args[0]);
			$path = static::compile($args[1]);

			// Store the route, by making sure there are no conflicts
			static::$routes[ $method ] [ $path ] = $args[2];
		}
		else
		{
			static::run();
		}
	}
	
	/**
	 * Clean a certain category of requests - useful for building demos
	 * @param string   $method          The target method
	 * @param mixed    $matching        Function - if returns true, cleanup is performed, has one parameter path; if null, all matches
	 * @param [[Type]] [$callback=null] [[Description]]
	 */
	public static function clean($method, $matching=null, $callback=null) 
	{
		$method = strtoupper($method);
		
		if (isset(static::$routes[$method])) 
		{
			foreach (static::$routes[$method] as $path => $function) 
			{
				if ($matching === null || (gettype($matching) === 'object' && call_user_func_array($matching, array('path'=>$path)) === true)) 
				{
					// Change the callback
					if ($callback === null) 
					{
						unset(static::$routes[$method][$path]);
					}
					else
					{
						static::$routes[$method][$path] = $callback;
					}
				}
			}
		}
	}
	/**
	 * Run the routing
	 * @return null Nothing to return
	 */
	public static function run()
	{
		// called method() from ./system/parameters
		$method = method();

		if (isset(static::$routes[$method]) && is_array(static::$routes[$method]))
		{
			foreach (static::$routes[$method] as $key=>$value)
			{
				$regex = $key;
				$callback = $value;
				
				// called path() from ./system/parameters.php
				if (preg_match('/' . $regex . '/ui', path(), $matched))
				{
					// leave out the match
					array_shift($matched);

					// if :all wildcard, split result by "/"
					if(strpos($regex, static::$wildcards['all']) !== FALSE)
					{
						$matched = explode('/', $matched[0]);
					}

					// callback home with found params
					call_user_func_array($callback, $matched);
					
					// and we stop
					return true;
				}
			}
		}

		return null;
	}

	/**
	 * Compile the route
	 * @param  string $path The path to compile into the routers memory
	 * @return string Compiled path
	 */
	private static function compile($path)
	{
		// Replace the regex-es
		foreach (static::$wildcards as $key=>$value)
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

/**
* Aliases used by class for easier programming
*/
function on() { return call_user_func_array(array('routes', 'on'),    func_get_args()); }
?>