<?php
if (!class_exists('starfish')) { die(); }

/**
 * Starfish text database
 *
 * @package starfish
 * @subpackage starfish.objects.textdb
 */
class textdb
{	
        /**
	 * Declare used variables
	 * 
	 * $resource - the result of a select perfomed on a file
	 * 
	 * $path - to the database directory
	 * $info - info about the executed query
	 */
        private $resource;
        
        private $path;
        private $info;

        /**
	 * Constructor
	 */
        function __construct()
        {
                // Establish the path to the database containing folder
                if (strlen(starfish::config('_starfish', 'storage')) > 0)
                {
                        $this->path = starfish::config('_starfish', 'storage') . 'textdb';
                }
                else
                {
                        $this->path = starfish::config('_starfish', 'storage') . 'textdb';
                }

                // Create the file if it does not exist
                if (!file_exists($this->path)) { starfish::obj('files')->smkdir($this->path); }
                
                return true;
        }
        
        /**
         * Function to connect to the database
         * 
         * @param string $database Name of the database to use. 
         */
        function connect($database)      
        {
                // Set the path inside this object
                $this->path .= DIRECTORY_SEPARATOR . $database;
                
                // Create the directory, if it does not exist
                if (!file_exists($this->path)) { starfish::obj('files')->smkdir($this->path); }
                
                // Create the safety index.html file
                if (!file_exists($this->path . DIRECTORY_SEPARATOR . 'index.html')) { starfish::obj('files')->w($this->path . DIRECTORY_SEPARATOR . 'index.html', ' '); }
                
                return true; 
        }
        
        /**
         * Function to disconnect from the database 
         */
        function disconnect()   { return true; }
        
        /**
         * Function to sanitize the input
         */
        function sanitize()     { return true; }
        
        /**
         * Function to escape the output
         */
        function escape($string)       
        { 
                return htmlspecialchars($string); 
        }
        
        /**
         * Function to execute a query
         * 
         * @param array $params Parameters of the request:
         *                      0 - the file/table
         *                      1 - the operation to be made
         *                      2 - the record
         *                      3 - the id
         */
        function query($params)        
        { 
                $file = $params[0];
                
                switch ($params[1])
                {
                        case 'c':
                                // Append to the file
                                $record = $this->sendRecord($params[2]);
                        
                                break;
                        case 'r':
                                // Read from the file
                        
                                break;
                        case 'u':
                                // Update exising entry
                                $record = $this->sendRecord($params[2]);
                                $id = $this->sendRecord($params[3]);
                        
                                break;
                        case 'd':
                                // Delete existing entry
                                $record = $params[2];
                                $id = $this->sendRecord($params[3]);
                        
                                break;
                }
                
                return true; 
        }
        
        /**
         * Function to receive info about a query
         */
        function info()         { return true; }
        
        /**
         * Function to fetch the results from a resource
         */
        function fetch()        { return true; }
        
        /**
         * Function to free a resource
         */
        function free()         { return true; }
        
        
        ##################
        # Internal functions
        ##################
        
        /**
         * Function get a record from the text file
         * 
         * @param string $serialized - Serialized retrieved from the text file
         * @return mixed The returned row
         */
        function getRecord($serialized)
        { 
                return @unserialize($serialized); 
        }
        
        /**
         * Function send a record to the text file
         * 
         * @param mixed Parameter to serialized
         * @return string Serialized string
         */
        function sendRecord($param)
        { 
                return @serialize($param); 
        }
}
?>