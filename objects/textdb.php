<?php
if (!class_exists('starfish')) { die(); }

/**
 * Mysql connection class
 *
 * @package starfish
 * @subpackage starfish.objects.mysql
 */
class textdb
{	
        /**
	 * Init the object
	 */
        public static function init()
        {
        }

        function connect($config)      
        {
                return true; 
        }
        function disconnect()   { return true; }
        function sanitize()     { return true; }
        function escape()       { return true; }
        function query()        { return true; }
        function info()         { return true; }
        function fetch()        { return true; }
        function free()         { return true; }
}
?>