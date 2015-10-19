<?php
if (!class_exists('starfish')) { die(); }

/**
 * Class for using the helpers
 *
 * @package starfish
 * @subpackage starfish.system.helpers
 */

class helpers
{	
	/**
	 * Get the path to the helpers (used mainly for the situation when starfish is stored in one file)
	 * @param  string $file Name of the file to look for
	 * @return string Path to the desired file
	 */
	public function getPath($file) 
	{
		$path = '';
		
		if (file_exists(starfish::config('_starfish', 'storage') . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $file)) 
		{
			$path = starfish::config('_starfish', 'storage') . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $file;
		} 
		else 
		{
			$path = starfish::config('_starfish', 'root') . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $file;
		}
		
		return $path;
	}
}
?>