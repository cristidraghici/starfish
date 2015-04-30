<?php
if (!class_exists('starfish')) { die(); }

/**
 * Helper class for interacting with databases
 * 
 * This class uses starfish.system.database, but it creates shorcuts in coding as it returns itself for every command given
 * 
 * @package starfish
 * @subpackage starfish.objects.db
 * 
 * @todo Not yet functional
 */
class db
{
	/**
	 * Declare used variables
	 *
	 * $connection - The connection to execute the query on
	 * $resource - The resource returned
	 */
	private $connection = null;
	private $resource = null;

	/**
	 * Init the object
	 */
	public function init()
	{
		starfish::obj('database');
		return true;
	}

	/**
	 * Add a new connection
	 * Helper method
	 */
	public function add($name, $type='textdb', $parameters=array())
	{
		return starfish::obj('database')->add($name, $type, $parameters);
	}

	/** 
	 * Retrieve/create a connection
	 * Helper method
	 */
	public function get($name=null)
	{
		$this->connection = $name;
		return $this;
	}

	/** 
	 * Convert the connection string inside this object's methods into a connection resource
	 * 
	 * @param mixed $conn Name of the connections
	 * @return resource This object
	 */
	private function conn($conn)
	{
		$this->connection = starfish::obj('database')->conn($conn);
		return $this;
	}

	/** 
	 * Return the connection information for the given connection
	 * 
	 * @param string $name Name of the connection to return info about
	 * @return array Information about the connection
	 */
	private function connectionInfo($name)
	{
		return starfish::obj('database')->connectionInfo($name);
	}

	/** 
	 * Send a query to the connection
	 * 
	 * @param mixed $query Name of the connections
	 * @param string $connection Name of the connection
	 * @param array $parameters Parameters to replace in the query, after sanitization
	 * 
	 * @return resource The resource containing the result
	 */
	public function query($query, $connection=null, $parameters=array() )
	{
		$this->resource = starfish::obj('database')->query($query, $connection, $parameters);
		return $this;
	}
	/*
	 * Return the last insert id
	 */
	public function insert_id($connection = null)
	{
		return starfish::obj('database')->insert_id($connection);
	}

	/** 
	 * Verify a query
	 * 
	 * @param mixed $query Name of the connections
	 * @param string $connection Name of the connection
	 * 
	 * @return resource The resource containing the result
	 */
	public function eecho($query, $connection=null, $parameters=array() )
	{
		if (count($parameters) > 0)
		{
			foreach ($parameters as $key=>$value)
			{
				$query = str_replace('{'. $key . '}', $this->sanitize( $parameters[$key], $connection ), $query );
			}
		}

		echo $query;
		exit;
	}

	/** 
	 * Fetch a result from a returned query resource
	 * 
	 * @param resource $resource The resource to be interpreted
	 * @param string $connection Name of the connection
	 * 
	 * @return array An array containing the fetched result
	 */
	public function fetch($resource=null, $connection=null)
	{
		if ($resource == null) { $resource = $this->resource; }
		if ($connection == null) { $resource = $this->connection; }

		return starfish::obj('database')->fetch($resource, $connection);
	}

	/** 
	 * Fetch all results from a returned query resource
	 * 
	 * @param resource $resource The resource to be interpreted
	 * @param string $connection Name of the connection
	 * 
	 * @return array An array containing the fetched result
	 */
	public function fetchAll($resource=null, $connection=null)
	{
		if ($resource == null) { $resource = $this->resource; }
		if ($connection == null) { $resource = $this->connection; }

		return starfish::obj('database')->fetchAll($resource, $connection);
	}

	/** 
	 * Count the results matching the given conditions
	 * 
	 * @param resource $resource The resource to be interpreted
	 * @param string $connection Name of the connection
	 * 
	 * @return number The number of results
	 */
	public function numRows($resource, $connection=null)
	{
		if ($resource == null) { $resource = $this->resource; }
		if ($connection == null) { $resource = $this->connection; }

		return starfish::obj('database')->numRows($resource, $connection);
	}

	/** 
	 * Free a resource
	 * 
	 * @param resource $resource The resource to be interpreted
	 * @param string $connection Name of the connection
	 */
	public function free($resource, $connection=null)
	{
		if ($resource == null) { $resource = $this->resource; }
		if ($connection == null) { $resource = $this->connection; }

		return starfish::obj('database')->free($resource, $connection);
	}

	/** 
	 * Disconnect a connection
	 * 
	 * @param string $name Name of the connections
	 */
	public function disconnect($connection=null)
	{
		return starfish::obj('database')->disconnect($connection);
	}

	/** 
	 * Sanitize string
	 * 
	 * @param string $name String to alter
	 * @return string String returned after processing
	 */
	public function sanitize($string, $connection=null)
	{
		return starfish::obj('database')->sanitize($string, $connection);
	}

	/** 
	 * Escape string
	 * 
	 * @param string $name String to alter
	 * @return string String returned after processing
	 */
	public function escape($string)
	{
		return starfish::obj('database')->escape($string, $connection);
	}
}
?>