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
        private $resource;
        
        private $index = 0;
        private $count = 0;
        
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
         *              - fields: fields used in the select
         *              - values: values used in the select
         *              - conditions: conditions to meet for data to be valid
         *              - order: how to order the rows (only in association with $this->fetchAll() method)
         *              - limits: limits the rows for the displayed data
         * @return function Function to check every line in the file against the arguments given
         */
        function query($query)
        {
                // Establish the source
                $source = $this->connection['name'] . $query['source'] . '.textdb.php';
                
                // Execute the requested action                
                switch ($query['type'])
                {
                        case 'select':
                                return $this->query_select($source, $query['fields'], $query['conditions'], $query['order'], $query['limits']);
                                break;
                        case 'insert':
                                return $this->query_insert($source, $query['fields'], $query['values']);
                                break;
                        case 'update':
                                return $this->query_update($source, $query['fields'], $query['values'], $query['conditions']);
                                break;
                        case 'delete':
                                return $this->query_delete($source, $query['conditions']);
                                break;
                }
                
                return false;
        }
        /** Helper functions for executing a query */
        // select
        function query_select($source, $fields, $conditions, $order, $limits)
        {
                // Jump the first line, which is a safeguard
                
                // Read some results
                
                return true;
        }
        // insert
        function query_insert($source, $fields, $values)
        {
                return true;
        }
        // update
        function query_update($source, $fields, $values, $conditions)
        {
                return true;
        }
        // delete
        function query_delete($source, $conditions)
        {
                return true;
        }
        
        function query_conditions($conditions)
        {
                return '';
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
                
                return '';
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
                return 0;
        }
        
        /*
         * Free a resource
         * 
         * @param resource $resource The resource to free
         */
        function free($resource)
        {
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