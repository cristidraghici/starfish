<?php
if (!class_exists('starfish')) { die(); }

/**
 * Postgres connection class
 *
 * @package starfish
 * @subpackage starfish.objects.postgres
 *
 * @todo SET bytea_output = "escape"; after connection
 */
class postgres
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
                        $this->connection = @pg_connect("host=".$config['host']." port=".$config['port']." dbname=".$config['name']." user=".$config['user']." password=".$config['pass']);
                        
                        // Correct output
                        @pg_query($this->connection, 'SET bytea_output = "escape";');
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
                @pg_close($this->connection);

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
                $this->resource = @pg_query($this->connection, $query );

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
                return @pg_fetch_assoc($this->resource);
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
                
                while ($row = @pg_fetch_assoc($this->resource))
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
                return @pg_num_rows($resource);
        }
        
        /*
         * Free a resource
         * 
         * @param resource $resource The resource to free
         */
        function free($resource)
        {
                // Free the memory
                pg_free_result($resource);

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
                return @pg_escape_string ($this->connection, $string);
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