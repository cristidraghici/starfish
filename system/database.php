<?php
if (!class_exists('starfish')) { die(); }

/**
 * "Abstract" class for interacting with databases
 *
 * @package starfish
 * @subpackage starfish.system.database
 *
 * @todo Connect to the parameters object to help with sanitization
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
	 * 
	 * @todo Maybe add connections that start automatically
	 */
        public static function init()
        {
                $databases = starfish::config('_starfish', 'databases');

                if (is_array($databases))
                {
                        foreach ($databases as $key=>$value)
                        {
                                self::add($key, $value['type'], $value['parameters']);
                        }
                }

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
        public static function add($name, $type='textdb', $parameters=array())
        {
                self::$connections[$name] = array(
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
                        if (isset(self::$resources[$name]))
                        {
                                return self::$resources[$name];
                        }
                        elseif (isset(self::$connections[$name]))
                        {
                                // Get the information about the connection
                                $info = self::$connections[$name];

                                // Create the new resource
                                $conn = null;

                                switch ($info['type'])
                                {
                                        case 'pgsql':
                                        break;
                                        case 'mysql':
                                        $conn = starfish::obj('mysql')->connect( $info['parameters'] );
                                        if ($conn != false)
                                        {
                                                self::$resources[$name] = $conn;
                                        }
                                        break;
                                        case 'textdb':
                                        break;

                                        // Break execution if database type is not valid
                                        default: 
                                        return null;
                                }

                                return $conn;
                        }
                }
                // Only one connection, no name specified
                elseif (count(self::$connections) == 1)
                {
                        // Get the name of the connection
                        $connections = array_keys(self::$connections);

                        // Call this function again
                        return self::get( $connections[0] );
                }

                return null;
        }

        /** 
         * Convert the connection string inside this object's methods into a connection resource
         * 
         * @param mixed $conn Name of the connections
         * @return resource The connection requested
         */
        private static function conn($conn)
        {
                switch (strtolower(gettype($conn)))
                {
                        case 'string':
                        case 'null':
                        return self::get($conn);
                        break;

                        case 'resource':
                        return $conn;
                        break;
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
                return self::conn($connection)->query($query);
        }

        /** 
         * Fetch a result from a returned query resource
         * 
         * @param resource $resource The resource to be interpreted
         * @param string $connection Name of the connection
         * 
         * @return array An array containing the fetched result
         */
        public static function fetch($resource, $connection=null)
        {
                return self::conn($connection)->fetch($resource);
        }

        /** 
         * Fetch all results from a returned query resource
         * 
         * @param resource $resource The resource to be interpreted
         * @param string $connection Name of the connection
         * 
         * @return array An array containing the fetched result
         */
        public static function fetchAll($resource, $connection=null)
        {
                return self::conn($connection)->fetchAll($resource);
        }

        /** 
         * Free a resource
         * 
         * @param resource $resource The resource to be interpreted
         * @param string $connection Name of the connection
         */
        public static function free($resource, $connection=null)
        {
                return self::conn($connection)->free($resource);
        }

        /** 
         * Disconnect a connection
         * 
         * @param string $name Name of the connections
         */
        public static function disconnect($connection=null)
        {
                // Get the object
                $obj = self::conn($connection);

                // Disconnect from the database
                $obj->disconnect();

                // Delete from the resources list
                foreach (self::$resources as $key=>$value)
                {
                        if ($value == $obj) { unset(self::$resources[$key]); break; }
                }

                // Destroy the object
                unset($obj);

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
                return self::conn($connection)->sanitize($string);
        }

        /** 
         * Escape string
         * 
         * @param string $name String to alter
         * @return string String returned after processing
         */
        function escape($string)
        {
                return self::conn($connection)->escape($string);
        }
}
?>