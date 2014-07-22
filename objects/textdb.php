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
	 */

        /**
	 * Constructor
	 */
        function __construct()
        {
                // Establish the path to the database containing folder
                if (strlen(starfish::config('_starfish', 'storage')) > 0)
                {
                        $path = starfish::config('_starfish', 'storage') . 'textdb';
                }
                else
                {
                        $path = starfish::config('_starfish', 'storage') . 'textdb';
                }

                // Create the file if it does not exist
                if (!file_exists($path)) { starfish::obj('files')->smkdir($path); }

                return true;
        }

        function connect()      { return true; }
        function disconnect()   { return true; }
        function sanitize()     { return true; }
        function escape()       { return true; }
        function query()        { return true; }
        function info()         { return true; }
        function fetch()        { return true; }
        function free()         { return true; }
?>