<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for files
 *
 * @package starfish
 * @subpackage starfish.system.files
 */

class files
{	
	/**
	 * Init the object
	 */
    public static function init()
    {
		
    }
	
	/**
	 * Read the content of a file
	 *
	 * @param string $path Path to the file
	 */
	public static function r($path)
	{
		return true;
	}
	
	/**
	 * Write to a file
	 *
	 * @param string $path Path to the file
	 * @param string $content The content of the file
	 * @param string $type The type of writing to execute
	 */
	public static function w($path, $content, $type='w')
	{
		return true;
	}
}
?>