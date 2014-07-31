<?php
if (!class_exists('starfish')) { die(); }

/**
 * "Abstract" class for interacting with databases
 *
 * @package starfish
 * @subpackage starfish.system.database
 */
class database
{
        /**
	 * Declare used variables
	 *
	 * $connections - The list of information regarding connections
	 * $resources - The list of resources stored as connections
	 */
        private static $connections = array();
        private static $resources = array();

        /**
	 * Init the object. Connect to any database in the configuration, if needed
	 */
        public static function init()
        {
                return true;
        }
        
        /**
         * Add a new connection
         * 
         * @param string $name Name of the connection
         * @param string $type Type of connection: pqsql, mysql, textdb
         * @param mixed $parameters Parameters for the connection
         *                              - host
         *                              - port
         *                              - user
         *                              - password
         *                              - database
         */
        public static function add($name, $type, $parameters)
        {
                $this->connections[$name] = array(
                        'type' => $type, 
                        'parameters' => $parameters
                );
                
                return true;
        }
        
        /** 
         * Retrieve/create a connection
         * 
         * @param string $name Name of the connections
         * @return resource The connection requested
         */
        public static function get($name=null)
        {
                // If a name is specified
                if ($name != null)
                {
                        // Get the stored resource
                        if (isset($this->resources[$name]))
                        {
                                return $this->resources[$name];
                        }
                        elseif (isset($this->connections[$name]))
                        {
                                // Get the information about the connection
                                $info = $this->connections[$name];
                                
                                // Create the new resource
                                switch ($info['type'])
                                {
                                        case 'pgsql':
                                                break;
                                        case 'mysql':
                                                break;
                                        case 'textdb':
                                                break;
                                        
                                        // Break execution if database type is not valid
                                        default: 
                                                return null;
                                }
                        }
                }
                // Only one connection, no name specified
                elseif (count($this->connections) == 1)
                {
                        // Get the name of the connection
                        $connections = array_keys($this->connections);
                        
                        // Call this function again
                        return self::get( $connections[0] );
                }
                
                return null;
        }
        
        /** 
         * Send a query to the connection
         * 
         * @param mixed $query Name of the connections
         * @param string $connection Name of the connection
         * 
         * @return resource The resource containing the result
         */
        public static function query($query, $connection=null)
        {
                
        }
        
        /** 
         * Fetch results from a returned query resource
         * 
         * @param resource $resource The resource to be interpreted
         * @param string $connection Name of the connection
         * 
         * @return array An array containing the fetched result
         */
        public static function fetch($resource, $connection=null)
        {
                
        }
        
        /** 
         * Free a resource
         * 
         * @param resource $resource The resource to be interpreted
         * @param string $connection Name of the connection
         */
        public static function free($resource, $connection=null)
        {
                
        }
        
        
        /** 
         * Disconnect a connection
         * 
         * @param string $name Name of the connections
         */
        public static function disconnect($name=null)
        {
                return true;
        }
}
?>