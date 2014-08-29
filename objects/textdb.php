<?php
if (!class_exists('starfish')) { die(); }

/**
 * TextDB connection class
 *
 * @package starfish
 * @subpackage starfish.objects.textdb
 */
class textdb
{
        private $conection;
        
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
         * @param mixed $query
         *              - source: name of the table which contains the data; just one
         *              - operation: select, insert, update, delete
         *              - columns: array containing column names and values
         *              - conditions: function to check the data against 
         *              - order: function to reorder the results
         *              - limits: limits the rows for the displayed data
         * @return boolean Wheter results were found or not
         */
        function query($query)
        {
                // Reset the resource
                $this->resource = array();
                
                // Reset the resource position index
                $this->resource_position = 0;
                
                // Establish the source
                $source = $this->connection['name'] . $query['source'] . '.textdb.php';
                
                // Order the parameters
                if (isset($query['columns'])) { ksort($query['columns']); }
                
                // Order the parameters
                if (!is_callable($query['conditions'])) { $query['conditions'] = function ($results) { return $results; } }
                
                // Execute the requested action                
                switch ($query['type'])
                {
                        case 'select':
                                break;
                        case 'insert':
                                break;
                        case 'update':
                                break;
                        case 'delete':
                                break;
                }
                
                return false;
        }
        
        // encode a string
        function query_encode($string)
        {
                return $this->connection->encrypt->encode( $this->connection->scramble->encode($string) );
        }
        // decode a string
        function query_decode($string)
        {
                return $this->connection->scramble->decode( $this->connection->encrypt->decode($string) );
        }
        // query order the results
        function query_order()
        {
                return $this->resource;
        }
        // query limits
        function query_limits()
        {
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
                $this->resource_position++;
                
                if (isset($this->resource[$this->resource_position]))
                {
                        return $this->resource[$this->resource_position];
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