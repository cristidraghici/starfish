<?php
if (!class_exists('starfish')) { die(); }

/**
 * TextDB connection class
 * 
 * This is a very simple file storage system which is compatible with simple SQL queries
 *
 * @package starfish
 * @subpackage starfish.objects.textdb
 * @todo This object can massively be updated, upon request
 * @todo Naming the column convention:
 *              * - unique column       
 *              + - autoincrement
 */
class textdb
{
	private $connection;

	private $resource = array();
	private $resource_position = 0;

	/*
	 * Connect to the database
	 * 
	 * @param array $config The configuration for the connection
	 *                              - name - the folder where the database is located
	 *                              - scramble - key to scramble/unscramble content
	 *                              - encrypt  - key to encrypt/decrypt content
	 * @return object The object containing the connection to the database
	 */
	function connect($config)
	{
		if ($this->connection == null)
		{
			$scramble = starfish::obj('scramble');
			$encrypt  = starfish::obj('encrypt');

			$scramble->hash($config['scramble']);
			$encrypt->hash($config['encrypt']);

			// Store the connection
			$this->connection = array(
				'name' => $config['name'],
				'scramble' => $scramble,
				'encrypt' => $encrypt
			);
		}

		return $this;
	}
	/*
	 * Disconnect from the database
	 * Kill the connection to the database
	 */
	function disconnect()
	{
		// Reset the data
		$this->connection = null;

		return true;
	}
	/*
	 * Query the database
	 * 
	 * @param string $query SQL query to execute
	 */
	function query($query)
	{
		// Reset the resource
		$this->resource = array();

		// Reset the resource position index
		$this->resource_position = 0;

		// Execute the query
		$this->query_sql($query);

		return $this->resource;
	}

	/**
	 * Parse an SQL string
	 * 
	 * e.g. 
	 * 
	 * select * from table where name='Starfish' order by title limit 0, 10
	 * insert into table(col1, col2, col3) values(1, 2, 3);
	 * update table set col1='a' where col3=3
	 * delete from table where col3=1
	 * 
	 * @param string $sql The sql formatted query
	 * @return array An array containing information on how to interrogate the TXT file
	 * 
	 * @todo Update and delete are very resource consuming
	 * @todo Add count(*) as select fields
	 * @todo Add as <name> for fields in select
	 * @todo Mind the string "and" inside the sql
	 */
	function query_sql($sql)
	{
		$query = array();

		// Identify the command
		$parts = explode(" ", $sql);
		$command = strtolower($parts[0]);

		// Identify the table
		$table = $this->query_get_the_table_name($command, $sql);
		$source = starfish::config('_starfish', 'storage') . $this->connection['name'] . DIRECTORY_SEPARATOR . $table . '.textdb.php';

		// Identify the columns
		$fields = $this->query_get_the_fields($command, $sql);

		// Identify the values
		$values = $this->query_get_the_values($command, $sql);

		// Identify the conditions      
		$conditions = $this->query_get_the_condition($command, $sql);

		// Identify the limits
		$limits = $this->query_get_the_limit($command, $sql);

		// Execute the command
		/*if (isset($query['columns'])) { ksort($query['columns']); }*/
		switch ($command)
		{
			case 'select':
			// create the comparison function
			$comparison = null;
			if ($conditions)
			{
				$comparison = $this->query_comparison_function($conditions);
			}

			// go through the rows
			starfish::obj('files')->walk($source);
			while ($row = starfish::obj('files')->walk($source) )
			{
				$result = @unserialize($row);
				if ($result)
				{
					foreach ($result as $key=>$value)
					{
						$result[$key] = $this->query_decode($value);
					}

					if (!is_callable($comparison) || (is_callable($comparison) && $comparison($result)))
					{
						$this->resource[] = $result;
					}
				}
			}
			break;

			case 'insert':
			if (count($fields) != count($values) || count($fields) == 0)
			{
				starfish::obj('errors')->err(400, 'Incorrect fields and values number!');
			}

			$result = array();
			for ($a = 0; $a < count($fields); $a++)
			{
				$result[ $fields[$a] ] = $this->query_encode( $values[$a] );
			}

			$result['_id'] = $this->query_encode( $this->_id($table) );

			ksort($result);

			$string = @serialize($result) . PHP_EOL;     

			// Add the security row
			if (!file_exists($source))
			{
				starfish::obj('files')->w( $source, '<'.'?php /* Starfish TextDB storage protection */ die(); ?'.'>' . PHP_EOL );
			}
			// Add the row
			starfish::obj('files')->w( $source, $string, 'a' );
			break;

			case 'update':
			// The new lines to store
			$lines = array();

			// create the comparison function
			$comparison = null;
			if ($conditions)
			{
				$comparison = $this->query_comparison_function($conditions);
			}

			// go through the rows
			starfish::obj('files')->walk($source);
			while ($row = starfish::obj('files')->walk($source) )
			{
				$result = @unserialize($row);
				if ($result)
				{
					if (is_array($result))
					{
						foreach ($result as $key=>$value)
						{
							$result[$key] = $this->query_decode($value);
						}
					}

					if (!is_callable($comparison) || (is_callable($comparison) && $comparison($result)))
					{
						for ($a = 0; $a < count($fields); $a++)
						{
							$result[ $fields[$a] ] = $values[$a];
						}

						foreach ($result as $key=>$value)
						{
							$result[$key] = $this->query_encode( $value );
						}

						ksort($result);

						$lines[] = @serialize($result);
					}
				}
			}

			// write the new lines
			starfish::obj('files')->w( $source, '<'.'?php /* Starfish TextDB storage protection */ die(); ?'.'>' . PHP_EOL );
			starfish::obj('files')->w( $source, @implode(PHP_EOL, $lines) . PHP_EOL, 'a' );
			break;

			case 'delete':
			// The new lines to store
			$lines = array();

			// create the comparison function
			$comparison = null;
			if ($conditions)
			{
				$comparison = $this->query_comparison_function($conditions);
			}

			// go through the rows
			starfish::obj('files')->walk($source);
			while ($row = starfish::obj('files')->walk($source) )
			{
				$result = @unserialize($row);
				if ($result)
				{
					foreach ($result as $key=>$value)
					{
						$result[$key] = $this->query_decode($value);
					}

					if (!is_callable($comparison) || (is_callable($comparison) && $comparison($result)))
					{
						// ignorig the line will lead to deletion
					}
					else
					{
						foreach ($result as $key=>$value)
						{
							$result[$key] = $this->query_encode( $value );
						}

						ksort($result);

						$lines[] = @serialize($result);
					}
				}
			}

			// write the new lines
			starfish::obj('files')->w( $source, '<'.'?php /* Starfish TextDB storage protection */ die(); ?'.'>' . PHP_EOL );
			starfish::obj('files')->w( $source, @implode(PHP_EOL, $lines) . PHP_EOL, 'a' );
			break;
		}

		// Create the result resource

		return $query;
	}

	// Calculate the _id for the row
	function _id($table)
	{
		$max = 0;
		$resource = $this->query('select * from '.$table);

		while ($row = $this->fetch($resource))
		{
			if ((int)$row['_id'] > $max)
			{
				$max = (int)$row['_id'];
			}
		}

		return ++$max;
	}

	// create the comparison function
	function query_comparison_function($where)
	{
		$conditions = array();
		$parts = explode(" and ", $where);
		foreach ($parts as $key=>$value)
		{
			preg_match('#(.*)(<|>|=|==|<=|>=)(.*)#ims', trim($value), $match);
			if (isset($match[1]) && isset($match[2]) && isset($match[3]))
			{
				if ($match[2] == '=') { $match[2] = '==='; }
                $f = $match[1];
                $v = substr($match[3], 1, -1);

				$conditions[] = 'if (!(md5((string)trim($row["'.$f.'"])) '.$match[2].' "'.md5((string)trim($v)).'")) { $return = false; }';
			}
		}

		$function = function ($row) use ($conditions) {
			$return = true;
            
            foreach ($conditions as $value)
			{
				eval($value);
			}

			return $return;
		};

		return $function;
	}

	// extract the table name
	function query_get_the_table_name($command, $query)
	{
		$string = array();

		switch ($command)
		{
			case 'select':
			preg_match('#from ([^\s]*)#is', $query, $match);
			$string = trim($match[1]);
			break;

			case 'insert':
			preg_match('#insert into ([^\()]*)\(([^\))]*)\)#is', $query, $match);
			$string = trim($match[1]);
			break;

			case 'update':
			preg_match('#update ([^\s]*) set#is', $query, $match);
			$string = trim($match[1]);
			break;

			case 'delete':
			preg_match('#delete from ([^\s]*)#is', $query, $match);
			$string = trim($match[1]);
			break;
		}

		return $string;                
	}

	// extract the field names from the query
	function query_get_the_fields($command, $query)
	{
		$fields = array();

		switch ($command)
		{
			case 'select':
			preg_match('#select (.*) from#is', $query, $match);
			$string = trim($match[1]);

			if ($string == '*')
			{
				$fields = true;
			}
			else
			{
				$parts = explode(",", $string);
				foreach ($parts as $key=>$value)
				{
					$fields[] = trim($value);
				}
			}
			break;

			case 'insert':
			preg_match('#insert into ([^\()]*)\(([^\))]*)\)#is', $query, $match);
			$string = trim($match[2]);
			$parts = explode(",", $string);
			foreach ($parts as $key=>$value)
			{
				$fields[] = trim($value);
			}
			break;

			case 'update':
			preg_match('#update (.*) set (.*)#is', $query, $match);
			$string = trim($match[2]);
			$parts = explode("where", $string);
			$string = trim($parts[0]);
			$parts = explode("limit", $string);
			$string = trim($parts[0]);

			$string = str_replace(',',' ',$string);
			$string = trim($string);
			preg_match_all("#([^=]*)='([^']*)'#is", $string, $matches);
			foreach ($matches[1] as $key=>$value)
			{
				$fields[] = trim($value);
			}
			break;

			case 'delete':
			$fields = true;
			break;
		}

		return $fields;
	}

	// extract the values from the query
	function query_get_the_values($command, $query)
	{
		$values = array();

		switch ($command)
		{
			case 'select':
			$values = true;
			break;

			case 'insert':
			preg_match('#<query> insert into ([^\()]*)\(([^\))]*)\) values\((.*?)\) </query>#is', '<query> '.$query.' </query>', $match);
			$string = trim($match[3]);


			preg_match_all("#'([^']*)'#is", $string, $matches);
			foreach ($matches[1] as $key=>$value)
			{
				$values[] = trim($value);
			}
			break;

			case 'update':
			preg_match('#update (.*) set (.*)#is', $query, $match);
			$string = @trim($match[2]);
			$parts = explode("where", $string);
			$string = @trim($parts[0]);
			$parts = explode("limit", $string);
			$string = @trim($parts[0]);

			$string = trim($string);
			preg_match_all("#([^=]*)='([^']*)'#is", $string, $matches);
			foreach ($matches[2] as $key=>$value)
			{
				$values[] = trim($value);
			}
			break;

			case 'delete':
			$values = true;
			break;
		}

		return $values;
	}

	// identify the condition
	function query_get_the_condition($command, $query)
	{
		$string = array();

		switch ($command)
		{
			case 'select':
			preg_match('#where (.*)#is', $query, $match);
			$string = @trim($match[1]);
			$parts = explode("limit", $string);
			$string = @trim($parts[0]);
			break;

			case 'insert':
			$string = true;
			break;

			case 'update':
			preg_match('#where (.*)#is', $query, $match);
			$string = @trim($match[1]);
			$parts = @explode("limit", $string);
			$string = trim($parts[0]);
			break;

			case 'delete':
			preg_match('#where (.*)#is', $query, $match);
			$string = @trim($match[1]);
			break;
		}

		return $string;
	}

	// identify the limit
	function query_get_the_limit($command, $query)
	{
		$string = array();

		switch ($command)
		{
			case 'select':
			preg_match('#limit (.*)#is', $query, $match);
			$string = @trim(@$match[2]);
			break;

			case 'insert':
			$string = true;
			break;

			case 'update':
			$string = true;
			break;

			case 'delete':
			preg_match('#limit (.*)#is', $query, $match);
			$string = @trim($match[2]);
			break;
		}

		return $string;
	}

	// encode a string
	function query_encode($string)
	{
		return $this->connection['encrypt']->encode( $this->connection['scramble']->encode($string) );
		//return $this->connection['scramble']->encode($string);
		//return $string;
	}
	// decode a string
	function query_decode($string)
	{
		return $this->connection['scramble']->decode( $this->connection['encrypt']->decode($string) );
		//return $this->connection['scramble']->decode( $string );
		//return $string;
	}

	/*
	 * Fetch one result from a resource
	 * 
	 * @param resource $resource The resource to fetch the result from
	 * @return array The associated array
	 */
	function fetch($resource)
	{
		if (isset($this->resource[$this->resource_position]))
		{
			return $this->resource[$this->resource_position++];
		}
		else
		{
			$this->resource_position = 0;
			return false;
		}

		return '';
	}
	/*
	 * Fetch all results from a resource
	 * 
	 * @param resource $resource The resource to fetch results from
	 * @return array An array with all the results
	 */
	function fetchAll($resource)
	{
		return $this->resource;
	}
	/** 
	 * Count the results matching the given conditions
	 * 
	 * @param resource $resource The resource to be interpreted
	 * @param string $connection Name of the connection
	 * 
	 * @return number The number of results
	 */
	public static function numRows($resource)
	{
		return @count($this->resource);
	}

	/*
	 * Free a resource
	 * 
	 * @param resource $resource The resource to free
	 */
	function free($resource)
	{
		$this->resource = array();
		return true;
	}


	/** 
	 * Sanitize string
	 * 
	 * @param string $name String to alter
	 * @return string String returned after processing
	 */
	function sanitize($string, $connection=null)
	{
		return htmlentities($string);
	}

	/** 
	 * Escape string
	 * 
	 * @param string $name String to alter
	 * @return string String returned after processing
	 */
	function escape($string)
	{
		return htmlspecialchars($string);
	}
}
?>