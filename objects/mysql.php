<?php
if (!class_exists('starfish')) { die(); }

/**
 * Mysql connection class
 *
 * @package starfish
 * @subpackage starfish.objects.mysql
 */
class mysql
{
        private $connection = null;
        private $resource = null;

        /*
         * Connect to the database
         * 
         * @param array $config The configuration for the connection
         * @return object The object containing the connection to the database
         */
        function connect($config)
        {
                if ($this->connection == null)
                {
                        $this->connection = @mysqli_connect($config['host'], $config['user'], $config['pass'], $config['name']);

                        // Halt on error
                        if (mysqli_connect_errno()) {
                                die( "Connect failed: " . mysqli_connect_error() );
                        }

                        //mysqli_query($this->connection, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
                        mysqli_set_charset($this->connection, 'utf8');
                }

                return $this;
        }
        /*
         * Disconnect from the database
         * Kill the connection to the database
         */
        function disconnect()
        {
                // Kill the connection
                @mysqli_close($this->connection);

                // Reset the data
                $this->connection = null;
                $this->resource = null;

                return true;
        }
        /*
         * Query the database
         * 
         * @param mixed $query
         * @return resource The query resource
         */
        function query($query)
        {
                $this->resource = @mysqli_query($this->connection, $query);
                if (!$this->resource)
                {
                        die(  mysqli_error( $this->connection ) );
                }

                return $this->resource;
        }
        /*
         * Fetch one result from a resource
         * 
         * @param resource $resource The resource to fetch the result from
         * @return array The associated array
         */
        function fetch($resource)
        {
                return @mysqli_fetch_assoc($resource);
        }
        /*
         * Fetch all results from a resource
         * 
         * @param resource $resource The resource to fetch results from
         * @return array An array with all the results
         */
        function fetchAll($resource)
        {
                $result = array();

                while ($row = @mysqli_fetch_assoc($resource))
                {
                        $result[] = $row;
                }

                return $result;
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
                return @mysqli_num_rows($resource);
        }

        /*
         * Free a resource
         * 
         * @param resource $resource The resource to free
         */
        function free($resource)
        {
                // Free the memory
                //$resource->free();
                @mysqli_free_result($resource);

                return true;
        }


        /** 
         * Sanitize string
         * 
         * @param string $name String to alter
         * @return string String returned after processing
         */
        function sanitize($string)
        {
                return @mysqli_real_escape_string($this->connection, $string);
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