<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for logging
 *
 * @package starfish
 * @subpackage starfish.system.logs
 *
 * @todo Maybe an interface to view the content of the logs.
 */
class logs
{
	// The default path to the cache files
	public static $path = null;

	/**
	 * Init
	 * - todo Check the size of the log file, clean it if too big
	 * - todo Establish the default date format
	 * @return boolean Nothing
	 */
	public function init()
	{
		// Set the path to the storage files
		static::$path = starfish::config('_starfish', 'storage') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
		if (!file_exists(static::$path)) { starfish::obj('files')->w(static::$path . 'index.html', 'Silence is golden.'); }

		return null;
	}

	/**
	 * Save information to the log file
	 * @param  string  $file The name of the file which will store the logs
	 * @param  mixed   $data The data to log
	 * @return boolean Nothing
	 */
	public static function saveLog($file, $data)
	{
		$data = @json_encode($data) . PHP_EOL;
		static::add($file, $data . PHP_EOL, 'a');

		return null;
	}

	/**
	 * Reset the log file
	 * @param  string $file The name of the file which will store the logs
	 * @return null   Nothing
	 */
	public static function resetLog($file)
	{
		starfish::obj('files')->w($file, "", 'w');

		return null;
	}

	/**
	 * Write into a log file
	 * @param  string  $file The file where to write the content
	 * @param  mixed   $text The content to store
	 * @return boolean Nothing
	 */
	public static function add($file, $text)
	{
		$file = static::$path . $file;

		// Make sure we are dealign with a string
		if (gettype($text) != 'string') { $text = @serialize($text); }
		
		// write the data
		starfish::obj('files')->w($file, $text, 'a');


		return null;
	}
}

/**
* Aliases used by class for easier programming
*/
function llog()   { return call_user_func_array(array('logs', 'add'),    func_get_args()); }
?>