<?php
if (!class_exists('starfish')) { die(); }

/**
 * Postgres connection class
 *
 * @package starfish
 * @subpackage starfish.objects.postgres
 *
 * @todo SET bytea_output = "escape"; after connection
 */
class postgres
{	
	/**
	 * Init the object
	 */
	public static function init()
	{
	}
    
    function connect()      { return true; }
    function disconnect()   { return true; }
    function sanitize()     { return true; }
    function escape()       { return true; }
    function query()        { return true; }
    function info()         { return true; }
    function fetch()        { return true; }
    function free()         { return true; }
}
?>