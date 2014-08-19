<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for the input parameters
 *
 * @package starfish
 * @subpackage starfish.system.parameters
 */
class parameters
{
        /**
	 * Declare used variables
	 *
	 * $cache 		- Cache information for usage without running the function
	 */
        private static $cache;


        /**
	 * Init the object
	 */
        public static function init()
        {
                self::request_content_type();
                self::request_body();

                self::session();
        }

        /**
	 * Sanitize string
	 *
	 * @param mixed $string The name of the variable to retrieve
	 *
	 * @todo Change the filtering function content if any database sanitization is available
	 * @todo Maybe override this function project defined
	 */
        public static function sanitize($mixed)
        {
                $type = gettype($mixed);

                // If a string is passed
                if ($type == 'string')
                {
                        $mixed = filter_var( $mixed, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                }
                // If an array is passed
                elseif ($type == 'array')
                {
                        foreach ($mixed as $key=>$value)
                        {
                                $mixed[$key] = self::sanitize($value);
                        }
                }

                return $mixed;
        }

        /**
	 * Reset already sanitized strings, for a new sanitization
	 */
        public static function reset_sanitized($mixed)
        {
                // Unset stored variables
                unset(self::$cache['get']);
                unset(self::$cache['post']);
                unset(self::$cache['put']);
                unset(self::$cache['delete']);
                unset(self::$cache['head']);
                unset(self::$cache['options']);

                return true;
        }


        /**
	 * Request method
	 */
        public static function method()
        {
                // If string exists in cache, return it
                if (isset(self::$cache['method'])) { return self::$cache['method']; }

                // Create the string in cache and return it
                $method = strtoupper($_SERVER['REQUEST_METHOD']);
                if ($method == 'POST')
                {
                        if (isset($_SERVER['HTTP_X_HTTP_METHOD']))
                        {
                                $method = $_SERVER['HTTP_X_HTTP_METHOD'];
                        }

                        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
                        {
                                $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
                        }
                }
                else
                {
                        $method = (self::post('_method') != null) ? self::post('_method') : $method;
                        $method = (self::get('_method') != null) ? self::get('_method') : $method;
                }

                // Ensure method is within allowed methods
                if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS')))
                {
                        $method = 'GET';
                }

                self::$cache['method'] = $method;
                return $method;
        }

        /**
	 * Request method
	 */
        public static function request_protocol()
        {
                // If string exists in cache, return it
                if (isset(self::$cache['request_protocol'])) { return self::$cache['request_protocol']; }

                // Create the string in cache and return it
                $request_protocol = $_SERVER['SERVER_PROTOCOL'];

                self::$cache['request_protocol'] = $request_protocol;
                return $request_protocol;
        }

        /**
	 * Get the request content type
	 */
        private static function request_content_type()
        {
                // If string exists in cache, return it
                if (isset(self::$cache['request_content_type'])) { return self::$cache['request_content_type']; }

                // Create the string in cache and return it
                $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : false;

                switch($content_type)
                {
                        case 'application/xml':
                        $request_content_type = 'xml';
                        break;

                        case 'application/json':
                        $request_content_type = 'json';
                        break;

                        case "application/x-www-form-urlencoded":
                        $request_content_type = 'html';
                        break;


                        case 'text/html':
                        default:
                        $request_content_type = 'html';
                        break;
                }

                self::$cache['request_content_type'] = $request_content_type;
                return $request_content_type;
        }

        /**
	 * Get the request body
	 *
	 * @todo Add parsing for XML requests
	 */
        private static function request_body()
        {
                // If string exists in cache, return it
                if (isset(self::$cache['request_body'])) { return self::$cache['request_body']; }

                // Create the string in cache and return it
                $body = @file_get_contents("php://input");
                $parameters = array();

                // Get the query vars
                if (isset($_SERVER['QUERY_STRING'])) { parse_str($_SERVER['QUERY_STRING'], $parameters); }

                // Establish the parameters by the content type headers
                switch (self::request_content_type())
                {
                        case 'json':
                        $body_params = @json_decode($body, true);
                        if($body_params)
                        {
                                foreach($body_params as $name=>$value)
                                {
                                        $parameters[$name] = $value;
                                }
                        }
                        break;
                        case 'xml':
                        // No parsing to be made, yet
                        break;
                        case 'html':
                        // No parsing to be made
                        break;
                }

                self::$cache['request_body'] = $parameters;
                return $parameters;
        }

        /**
	 * Request path
	 */
        public static function path()
        {
                // If string exists in cache, return it
                if (isset(self::$cache['path'])) { return self::$cache['path']; }

                // Create the string in cache and return it
                // get the request_uri basename
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                // remove dir path if we live in a subdir
                if ($base = starfish::config('_starfish', 'site_url'))
                {
                        $base = rtrim(parse_url($base, PHP_URL_PATH), '/');
                        $path = preg_replace('@^'.preg_quote($base).'@', '', $path);
                }
                else
                {
                        // improved base directory detection if no config specified
                        $base = rtrim(strtr(dirname($_SERVER['SCRIPT_NAME']), '\\', '/'), '/');
                        $path = preg_replace('@^'.preg_quote($base).'@', '', $path);
                }

                // remove router file from URI
                if ($stub = starfish::config('_starfish', 'site_url') )
                {
                        $stub = starfish::config('_starfish', 'site_router') ;
                        $path = preg_replace('@^/?'.preg_quote(trim($stub, '/')).'@i', '', $path);
                }
                
                // add begining backslash
                if (substr($path, 0, 1) != '/') { $path = '/'.$path; }
                
                self::$cache['path'] = $path;
                return self::$cache['path'];
        }

        /**
	 * Get parameters
	 *
	 * @param string $name The name of the variable to retrieve
	 */
        public static function get($name)
        {
                // If string exists in cache, return it
                if (isset(self::$cache['get'][$name])) { return self::$cache['get'][$name]; }

                // Create the string in cache and return it
                self::$cache['get'][$name] = isset($_GET[$name]) ? self::sanitize($_GET[$name]) : null;

                return self::$cache['get'][$name];
        }

        /**
	 * Post parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
        public static function post($name)
        {
                // If string exists in cache, return it
                if (isset(self::$cache['post'][$name])) { return self::$cache['post'][$name]; }

                // Create the string in cache and return it
                switch (self::request_content_type())
                {
                        case 'json':
                        self::$cache['post'][$name] = isset(self::$cache['request_body'][$name]) ? self::sanitize(self::$cache['request_body'][$name]) : null;
                        break;

                        default:
                        self::$cache['post'][$name] = isset($_POST[$name]) ? self::sanitize($_POST[$name]) : null;
                }


                return self::$cache['post'][$name];
        }

        /**
	 * Put parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
        public static function put($name)
        {
                // If string exists in cache, return it
                if (isset(self::$cache['put'][$name])) { return self::$cache['put'][$name]; }

                // Create the string in cache and return it
                self::$cache['put'][$name] = isset(self::$cache['request_body'][$name]) ? self::sanitize(self::$cache['request_body'][$name]) : null;;

                return self::$cache['put'][$name];
        }

        /**
	 * Delete parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
        public static function delete($name)
        {
                // If string exists in cache, return it
                if (isset(self::$cache['delete'][$name])) { return self::$cache['delete'][$name]; }

                // Create the string in cache and return it
                self::$cache['delete'][$name] = isset(self::$cache['request_body'][$name]) ? self::sanitize(self::$cache['request_body'][$name]) : null;;


                return self::$cache['delete'][$name];
        }

        /**
	 * Head parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
        public static function head($name)
        {
                // If string exists in cache, return it
                if (isset(self::$cache['head'][$name])) { return self::$cache['head'][$name]; }

                // Create the string in cache and return it
                self::$cache['head'][$name] = isset(self::$cache['request_body'][$name]) ? self::sanitize(self::$cache['request_body'][$name]) : null;;

                return self::$cache['head'][$name];
        }

        /**
	 * Options parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
        public static function options($name)
        {
                // If string exists in cache, return it
                if (isset(self::$cache['options'][$name])) { return self::$cache['options'][$name]; }

                // Create the string in cache and return it
                self::$cache['options'][$name] = isset(self::$cache['request_body'][$name]) ? self::sanitize(self::$cache['request_body'][$name]) : null;;

                return self::$cache['options'][$name];
        }

        /**
	 * Session parameters. session_write_close() used for speeding up asynchronous JSON content delivery
	 *
	 * 0 args - store the content of the session into the session variable cache
	 * 1 args - return the content of the specified name
	 * 2 args - set a value for the specified name
	 *
	 * @param string $name Name of the variable to retrieve or set
	 * @param mixed $value Value of the variable to set
	 *
	 * @return mixed Value of the specified variable inside the session
	 */
        public static function session()
        {
                $args = func_get_args();

                switch (count($args))
                {
                        case 0:
                                session_start();
                                self::$cache['session'] = $_SESSION;
                                session_write_close();

                                return true;

                                break; // for structured coding
                        case 1:
                                return isset( self::$cache['session'][ $args[0] ] ) ? self::$cache['session'][ $args[0] ] : null;

                                break; // for structure coding

                        case 2:
                                self::$cache['session'][ $args[0] ] = $args[1];

                                session_start();
                                $_SESSION[ $args[0] ] = $args[1];
                                session_write_close();

                                return $args[1];

                                break; // for structure coding
                }
        }

        /**
	 * File parameters
	 *
	 * @param mixed $name The name of the variable to retrieve
	 */
        public static function file($name)
        {
                // If string exists in cache, return it
                if (isset(self::$cache['file'][$name])) { return self::$cache['file'][$name]; }

                // Create the string in cache and return it
                self::$cache['file'][$name] = $_FILES[$name];

                return self::$cache['file'][$name];
        }
}

/**
* Aliases used by class for easier programming
*/
function method()   	{ return call_user_func_array(array('parameters', 'method'),    func_get_args()); }
function path()     	{ return call_user_func_array(array('parameters', 'path'),      func_get_args()); }

function get()      	{ return call_user_func_array(array('parameters', 'get'),       func_get_args()); }
function post()     	{ return call_user_func_array(array('parameters', 'post'),      func_get_args()); }
function put()      	{ return call_user_func_array(array('parameters', 'put'),       func_get_args()); }
function delete()	{ return call_user_func_array(array('parameters', 'delete'),    func_get_args()); }
function head() 	{ return call_user_func_array(array('parameters', 'head'),    	func_get_args()); }
function options()  	{ return call_user_func_array(array('parameters', 'options'),   func_get_args()); }
function session()  	{ return call_user_func_array(array('parameters', 'session'),   func_get_args()); }
?>