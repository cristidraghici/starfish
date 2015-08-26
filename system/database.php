<?php
if (!class_exists('starfish')) { die(); }

/**
 * "Abstract" class for interacting with databases
 *
 * @package starfish
 * @subpackage starfish.system.database
 *
 * @todo Connect to the parameters object to help with sanitization
 * @see https://github.com/Wikunia/Medoo/blob/master/medoo.php
 */
class database
{
	/**
	 * Declare used variables
	 *
	 * $connections - The list of information regarding connections
	 * $resources - The list of resources stored as connections
	 */
	private static $connections = array();
	private static $resources = array();

	/**
	 * Init the object. Connect to any database in the configuration, if needed
	 * 
	 * -todo Maybe add connections that start automatically
	 * @return boolean Nothing
	 */
	public static function init()
	{
		$databases = starfish::config('_starfish', 'databases');

		if (is_array($databases))
		{
			foreach ($databases as $key=>$value)
			{
				static::add($key, $value['type'], $value['parameters']);
			}
		}

		return null;
	}

	/**
	 * Add a new connection
	 * @param  string  $name                 Name of the connection
	 * @param  string  [$type='textdb']      Type of connection: pqsql, mysql, textdb
	 * @param  mixed   [$parameters=array()] Parameters for the connection
	 *                                       - host
	 *                                       - port
	 *                                       - user
	 *                                       - password
	 *                                       - database
	 * @return boolean Nothing
	 */
	public static function add($name, $type='textdb', $parameters=array())
	{
		static::$connections[$name] = array(
			'type' => $type, 
			'parameters' => $parameters
		);

		return null;
	}

	/**
	 * Retrieve/create a connection
	 * @param  string   [$name=null] Name of the connections
	 * @return resource The connection requested
	 */
	public static function get($name=null)
	{
		// If a name is specified
		if ($name != null)
		{	
			// Get the stored resource
			if (isset(static::$resources[$name]))
			{
				return static::$resources[$name];
			}
			elseif (isset(static::$connections[$name]))
			{
				// Get the information about the connection
				$info = static::$connections[$name];

				// Create the new resource
				$conn = null;

				switch ($info['type'])
				{
					case 'pgsql':
					case 'postgres':
					$conn = starfish::access('postgres', array('path'=>starfish::config('_starfish', 'root') . 'helpers/database/postgres.php'))->connect( $info['parameters'] );
					if ($conn != false)
					{
						static::$resources[$name] = $conn;
					}
					break;
					case 'mysql':
					$conn = starfish::access('mysql', array('path'=>starfish::config('_starfish', 'root') . 'helpers/database/mysql.php'))->connect( $info['parameters'] );
					if ($conn != false)
					{
						static::$resources[$name] = $conn;
					}
					break;
					case 'textdb':
					$conn = starfish::access('textdb', array('path'=>starfish::config('_starfish', 'root') . 'helpers/database/textdb.php'))->connect( $info['parameters'] );
					if ($conn != false)
					{
						static::$resources[$name] = $conn;
					}
					break;

					// Break execution if database type is not valid
					default: 
					return null;
				}

				return $conn;
			}
		}
		// Only one connection, no name specified
		elseif (count(static::$connections) >= 1)
		{
			// Get the name of the connection
			$connections = array_keys(static::$connections);

			// Call this function again
			return static::get( $connections[0] );
		}

		return null;
	}

	/**
	 * Convert the connection string inside this object's methods into a connection resource
	 * @param  mixed    $conn Name of the connections
	 * @return resource The connection requested
	 */
	private static function conn($conn)
	{
		switch (strtolower(gettype($conn)))
		{
			case 'string':
			case 'null':
			return static::get($conn);
			break;

			case 'resource':
			return $conn;
			break;
		}

		return null;
	}

	/**
	 * Return the connection information for the given connection
	 * @param  string $name Name of the connection to return info about
	 * @return array  Information about the connection
	 */
	private static function connectionInfo($name)
	{
		if (isset(static::$connections[$name]))
		{
			return static::$connections[$name];
		}

		return null;
	}

	/**
	 * Send a query to the connection
	 * @param  mixed    $query                Name of the connections
	 * @param  string   [$connection=null]    Name of the connection
	 * @param  array    [$parameters=array()] Parameters to replace in the query, after sanitization
	 * @return resource The resource containing the result
	 */
	public static function query($query, $connection=null, $parameters=array() )
	{
		if (count($parameters) > 0)
		{
			foreach ($parameters as $key=>$value)
			{
				$query = str_replace('{'. $key . '}', static::sanitize( $parameters[$key], $connection ), $query );
			}
		}

		return static::conn($connection)->query($query);
	}

	/**
	 * Retrieve the id of the last inserted value
	 * @param  mixed [$connection=null] Selected connection
	 * @return int   Numeric value of the last id
	 */
	public static function insert_id($connection=null)
	{
		return static::conn($connection)->insert_id();
	}

	/**
	 * Verify a query
	 * @param mixed  $query                Name of the connections
	 * @param string [$connection=null]    Name of the connection
	 * @param array  [$parameters=array()] Parameters to sanitize and insert in the given command
	 */
	public static function eecho($query, $connection=null, $parameters=array() )
	{
		if (count($parameters) > 0)
		{
			foreach ($parameters as $key=>$value)
			{
				$query = str_replace('{'. $key . '}', static::sanitize( $parameters[$key], $connection ), $query );
			}
		}

		echo $query;
		exit;
	}

	/**
	 * Fetch a result from a returned query resource
	 * @param  resource $resource             The resource to be interpreted
	 * @param  string   [$connection=null]    Name of the connection
	 * @param  array    [$parameters=array()] Parameters to escape as they will be sent to the browser
	 * @return array    An array containing the fetched result
	 */
	public static function fetch($resource, $connection=null, $parameters=array())
	{
		$row = static::conn($connection)->fetch($resource);

		if (count($parameters) > 0)
		{
			foreach ($parameters as $key=>$value)
			{
				if (isset($row[$value])) { $row[$value] = static::escape( $row[$value] ); }
			}
		}

		return $row;
	}

	/**
	 * Fetch all results from a returned query resource
	 * @param  resource $resource             The resource to be interpreted
	 * @param  string   [$connection=null]    Name of the connection
	 * @param  array    [$parameters=array()] Parameters to escape as they will be sent to the browser
	 * @return array    An array containing the fetched result
	 */
	public static function fetchAll($resource, $connection=null, $parameters=array())
	{
		$rows = static::conn($connection)->fetchAll($resource);

		if (count($parameters) > 0)
		{
			foreach ($rows as $k=>$row)
			{
				foreach ($parameters as $key=>$value)
				{
					if (isset($rows[$k][$value])) { $rows[$k][$value] = static::escape( $rows[$k][$value] ); }
				}
			}
		}

		return $rows;
	}


	/**
	 * Fetch the json output from a database
	 * @param  resource $resource             The resource to be interpreted
	 * @param  string   [$connection=null]    Name of the connection
	 * @param  array    [$parameters=array()] Parameters to escape as they will be sent to the browser
	 * @return string   The JSON string returned
	 */
	public static function json($resource, $connection=null, $parameters=array())
	{
		$row = static::conn($connection)->fetch($resource);
		static::conn($connection)->free($resource, $connection);
		$row = @array_values($row);

		if ($row == null)
		{
			return @json_encode(array('success'=>false, 'message'=>'The data could not be retrieved.'));
		}
		return $row[0];
	}

	/**
	 * Count the results matching the given conditions
	 * @param  resource $resource          The resource to be interpreted
	 * @param  string   [$connection=null] Name of the connection
	 * @return number   The number of results
	 */
	public static function numRows($resource, $connection=null)
	{
		return static::conn($connection)->numRows($resource);
	}

	/**
	 * Free a resource
	 * @param  resource $resource          The resource to be interpreted
	 * @param  string   [$connection=null] Name of the connection
	 * @return boolean  Result of the free command
	 */
	public static function free($resource, $connection=null)
	{
		return static::conn($connection)->free($resource);
	}

	/**
	 * Disconnect a connection
	 * @param  mixed   [$connection=null] Selected connection
	 * @return boolean Nothing
	 */
	public static function disconnect($connection=null)
	{
		// Get the object
		$obj = static::conn($connection);

		// Disconnect from the database
		$obj->disconnect();

		// Delete from the resources list
		foreach (static::$resources as $key=>$value)
		{
			if ($value == $obj) { unset(static::$resources[$key]); break; }
		}

		// Destroy the object
		unset($obj);

		return null;
	}

	/**
	 * Sanitize string
	 * @param  string $string            String to sanitize
	 * @param  mixed  [$connection=null] Connection to use
	 * @return string String returned after processing
	 */
	public static function sanitize($string, $connection=null)
	{
		return static::conn($connection)->sanitize($string);
	}

	/**
	 * Escape string
	 * @param  string $string            String to escape
	 * @param  mixed  [$connection=null] Connection to use
	 * @return string String returned after processing
	 */
	public static function escape($string, $connection=null)
	{
		return static::conn($connection)->escape($string);
	}
	
	/**
	 * Escape all the rows resulted from a query
	 * @param  array $rows              The target rows
	 * @param  mixed [$connection=null] Connection to use
	 * @return array The escaped target rows
	 */
	public static function escapeAll($rows, $connection=null) 
	{
		$output = array();
		
		foreach ($rows as $key=>$value) 
		{
			$row = array();
			foreach ($value as $k=>$v) 
			{
				$row[$k] = static::escape($v, $connection);
			}
			
			$output[] = $row;
		}
		
		return $output;
	}
}

/**
* Aliases used by class for easier programming
*/
function query() { return call_user_func_array(array('database', 'query'),    func_get_args()); }
function fetch() { return call_user_func_array(array('database', 'query'),    func_get_args()); }
function fetchAll() { return call_user_func_array(array('database', 'fetchAll'),    func_get_args()); }
function dbFree() { return call_user_func_array(array('database', 'free'),    func_get_args()); }
function numRows() { return call_user_func_array(array('database', 'numRows'),    func_get_args()); }

?>