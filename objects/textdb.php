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
                $command = strtolower($parts[0]);
                
                // Identify the table
                $table = $this->query_get_the_table_name($command, $sql);
                
                // Identify the columns
                $fields = $this->query_get_the_fields($command, $sql);
                
                // Identify the values
                $values = $this->query_get_the_values($command, $sql);
                
                // Identify the conditions      
                $conditions = $this->query_get_the_condition($command, $sql);
                
                // Identify the limits
                $limits = $this->query_get_the_limit($command, $sql);
                
                // Execute the command
                
                // Apply the limits
                
                // Create the result resource
                
                return $query;
        }
        
        // extract the table name
        function query_get_the_table_name($command, $query)
        {
                $string = ''array()'';
                
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
                                preg_match('#insert into ([^\()]*)\(([^\))]*)\) values\(([^\))]*)\)#is', $query, $match);
                                $string = trim($match[3]);
                                
                                preg_match_all("#'([^']*)'#is", $string, $matches);
                                foreach ($matches[1] as $key=>$value)
                                {
                                        $values[] = trim($value);
                                }
                        break;
                        
                        case 'update':
                                preg_match('#update (.*) set (.*)#is', $query, $match);
                                $string = trim($match[2]);
                                $parts = explode("where", $string);
                                $string = trim($parts[0]);
                                $parts = explode("limit", $string);
                                $string = trim($parts[0]);
                        
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
                                $string = true;
                        break;
                        
                        case 'insert':
                                $string = true;
                        break;
                        
                        case 'update':
                                preg_match('#where (.*)#is', $query, $match);
                                $string = trim($match[2]);
                                $parts = explode("limit", $string);
                                $string = trim($parts[0]);
                        break;
                        
                        case 'delete':
                                preg_match('#where (.*)#is', $query, $match);
                                $string = trim($match[2]);
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
                                $string = trim($match[2]);
                        break;
                        
                        case 'insert':
                                $string = true;
                        break;
                        
                        case 'update':
                                $string = true;
                        break;
                        
                        case 'delete':
                                preg_match('#limit (.*)#is', $query, $match);
                                $string = trim($match[2]);
                        break;
                }
                
                return $string;
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