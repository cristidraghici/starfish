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
        private $result = null;
        private $index = 0;

        function connect($config)
        {
                if ($this->connection == null)
                {
                        $this->connection = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
                }

                return $this;
        }
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
        function query($query)
        {
                $this->resource = $this->connection->query( $query );

                return $this->resource;
        }
        function fetch($resource)
        {
                return $resource->fetch_assoc();
        }
        function fetchAll($resource)
        {
                while ($row = $resource->fetch_assoc())
                {
                        $this->result[] = $row;
                }
                return $this->result;
        }
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