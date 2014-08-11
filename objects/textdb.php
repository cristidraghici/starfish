<?php
if (!class_exists('starfish')) { die(); }

/**
 * TextDB connection class
 *
 * @package starfish
 * @subpackage starfish.objects.textdb
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
                        $this->connection = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
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
                $this->result = null;
                $this->index = 0;

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
                $this->resource = $this->connection->query( $query );

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
                return $resource->fetch_assoc();
        }
        /*
         * Fetch all results from a resource
         * 
         * @param resource $resource The resource to fetch results from
         * @return array An array with all the results
         */
        function fetchAll($resource)
        {
                while ($row = $resource->fetch_assoc())
                {
                        $this->result[] = $row;
                }
                return $this->result;
        }
        /*
         * Free a resource
         * 
         * @param resource $resource The resource to free
         */
        function free($resource)
        {
                // Free the memory
                $resource->free();

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
                return mysqli_real_escape_string ($this->connection, $string);
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