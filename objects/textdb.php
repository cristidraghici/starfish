<?php
if (!class_exists('starfish')) { die(); }

/**
 * TextDB connection class
 * 
 * This is a very simple file storage system which is compatible with simple SQL queries
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
         *              - type: select, insert, update, delete
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
         */
        function query_sql($sql)
        {
                $query = array();
                
                // Identify the command
                $parts = explode(" ", $sql);
                switch (strtolower($parts[0]))
                {
                        case 'select':
                        $query = $this->query_sql_select($sql);
                        break;
                        
                        case 'insert':
                        $query = $this->query_sql_select($sql);
                        break;
                        
                        case 'update':
                        $query = $this->query_sql_select($sql);
                        break;
                        
                        case 'delete':
                        $query = $this->query_sql_select($sql);
                        break;
                }
                
                return $query;
        }
        
        // Query helper function for select
        function query_sql_select($sql)
        {
                $query = array();
                
                return $query;
        }
        // Query helper function for insert
        function query_sql_insert($sql)
        {
                $query = array();
                
                return $query;
        }
        // Query helper function for update
        function query_sql_update($sql)
        {
                $query = array();
                
                return $query;
        }
        // Query helper function for delete
        function query_sql_delete($sql)
        {
                $query = array();
                
                return $query;
        }
              
        
        // filter the results 
        function query_conditions($sql)
        {
                return $this->resource;
        }
        
        // query order the results
        function query_order($sql)
        {
                return $this->resource;
        }
        // query limits
        function query_limits($sql)
        {
                return $this->resource;
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