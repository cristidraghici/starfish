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
	 * Establish a route / Run the routing function
	 *
	 * @param string $method Method used to access the resource
	 * @param string $path Resource path
	 * @param mixed $callback What to do when the route has been accessed
	 * 
	 * or
	 * 
	 * no parametes at all
	 */
        public static function on()
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
	 * Run the routing
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