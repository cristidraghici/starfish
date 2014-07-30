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
        }
        
}
?>