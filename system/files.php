<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for files
 *
 * @package starfish
 * @subpackage starfish.system.files
 *
 * @see http://stackoverflow.com/questions/4000483/how-download-big-file-using-php-low-memory-usage
 */

class files
{	
	// Resource used when walking through a file
	private static $walkResource = null;
	// Row number for the current file rows walk 
	private static $walkNumRow = 0;

	/**
	 * Make the tree for the specified path
	 * @param  string $path             Path to the directory
	 * @param  array  [$except=array()] List of paths to except from the tree
	 * @return array  Directory tree
	 */
	public function tree($path, $except=array())
	{
		$list = array();

		if (file_exists($path) && is_dir($path) && is_readable($path) && !$this->is_directory_list_exception($path, $except))
		{
			if ($dir_handler = opendir($path)) 
			{
				while (($file = readdir($dir_handler)) !== false) 
				{
					if ($file != '.' && $file != '..')
					{
						if (filetype($path . $file) == 'file')
						{
							if ( !$this->is_file_list_exception($path . $file, $except) ) 
							{
								$list[] = array('name'=>$file, 'type'=>'file', 'path'=> $path . $file);
							}
						}
						elseif ( !$this->is_directory_list_exception($path . $file, $except) )
						{
							$list[] = array('name'=>$file, 'type'=>'folder', 'path'=> $path . $file, 'content'=> $this->tree($path . $file . '/', $except) );
						}
					}
				}
			}
		}

		return $list;
	}

	/**
	 * Check if a directory path corresponds to the exception list
	 * @param  string  $path   Path to check
	 * @param  array   $except List of exceptions
	 * @return boolean True if it is an exception
	 */
	public function is_directory_list_exception($path, $except)
	{
		$boolean = false;

		if (count($except) > 0)
		{
			foreach ($except as $key=>$value)
			{
				if ($value == $path)
				{
					$boolean = true;
				}
				else
				{
					$value = str_replace('/', '\/', $value);

					if (preg_match('/' . $value . '/ui', $path, $matched))
					{
						$boolean = true;
					}	
				}
			}
		}
		return $boolean;
	}

	/**
	 * Check if a file path corresponds to the exception list
	 * @param  string  $path   Path to check
	 * @param  array   $except List of exceptions
	 * @return boolean True if it is an exception
	 */
	public function is_file_list_exception($path, $except)
	{
		$boolean = false;

		if (count($except) > 0)
		{
			foreach ($except as $key=>$value)
			{
				if ($value == $path)
				{
					$boolean = true;
				}
				else
				{
					$value = str_replace('/', '\/', $value);

					if (preg_match('/' . $value . '/ui', $path, $matched))
					{
						$boolean = true;
					}	
				}
			}
		}
		return $boolean;
	}

	/**
	 * Read the content of a directory
	 * @param  string $path Path to the directory
	 * @return array  List of files
	 */
	public static function all($path)
	{
		$files  = array('folders'=>array(), 'files'=>array());

		if (file_exists($path) && is_dir($path) && is_readable($path))
		{
			if ($dir_handler = opendir($path)) 
			{
				while (($file = readdir($dir_handler)) !== false) 
				{
					if ($file != '.' && $file != '..')
					{
						if (filetype($path . $file) == 'file')
						{
							$files['files'][] = $file;
						}
						else
						{
							$files['folders'][] = $file;
						}
					}
				}
				closedir($dir_handler);
			}
		}

		return $files;
	}

	/**
	 * Recursively remove a directory
	 * @param string $path Path to the directory
	 */
	public static function srmdir($path)
	{
		if (file_exists($path) && !is_file($path) && is_readable($path))
		{
			foreach(glob($path . '/*') as $file)
			{
				if(is_dir($file))
				{
					static::rrmdir($file);
				}
				else
				{
					@unlink($file);
				}
			}
			@rmdir($dir);
		}
	}

	/**
	 * Walk line by line through the content of a file
	 * @param  string [$path=null] Path to the file
	 * @return null   Nothing
	 */
	public static function walk($path=null)
	{
		// Check if the creation of the resource is needed
		if (static::$walkResource == null && $path != null)
		{ 
			if (file_exists($path) && is_file($path) && is_readable($path))
			{
				static::$walkResource = @fopen($path, "r");
				static::$walkNumRow = 0;

				return static::walk();
			}
		}
		else
		{
			// Check if there is still content available
			if (!@feof(static::$walkResource)) 
			{
				static::$walkNumRow++;
				return @fgets(static::$walkResource); 
			}
			else
			{
				@fclose(static::$walkResource);
				static::$walkResource = null;

				return null;
			}
		}

		return null;
	}
	public static function walkNumRow()
	{
		return static::$walkNumRow - 1;
	}

	/**
	 * Read the content of a file
	 * @param  string $path Path to the file
	 * @return mixed  False if not available | String with content, if available
	 */
	public static function r($path)
	{
		if (file_exists($path) && is_file($path) && is_readable($path))
		{
			$file = @fopen($path, "r");
			$size = filesize($path);
			if ($size == 0)
			{
				$size = "32";
			}
			$data = @fread($file, $size);
			@fclose($file);

			return $data;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Write to a file
	 * @param  string  $path       Path to the file
	 * @param  string  $content    The content of the file
	 * @param  string  [$type='w'] The type of writing to execute
	 * @return boolean True if write is successful
	 */
	public static function w($path, $content, $type='w')
	{
		// The default return value
		$return = false;

		// Create the path 
		$directory = @dirname (static::truepath($path)) . DIRECTORY_SEPARATOR;
		if (!file_exists($directory))
		{
			mkdir($directory, 0777, true);
		}

		// Write to file
		if ( (file_exists($path) && is_writable($path)) || (!file_exists($path) && is_writable($directory)) )
		{
			$resource = @fopen($path, $type);
			if (@fwrite($resource, $content) !== FALSE)
			{
				$return = true;
			}

			@fclose($resource);
		}

		return $return;
	}

	/**
	 * This function is to replace PHP's extremely buggy realpath().
	 * - link http://stackoverflow.com/questions/4049856/replace-phps-realpath
	 * @param  string $path Path to analyze
	 * @return string The resolved path, it might not exist.
	 */
	public static function truepath($path){
		// whether $path is unix or not
		$unipath = strlen($path)==0 || $path{0}!='/';
		$unc = substr($path,0,2)=='\\\\'?true:false;
		// attempts to detect if path is relative in which case, add cwd
		if(strpos($path,':') === false && $unipath && !$unc){
			$path=getcwd().DIRECTORY_SEPARATOR.$path;
			if($path{0}=='/'){
				$unipath = false;
			}
		}

		// resolve path parts (single dot, double dot and double delimiters)
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
		$absolutes = array();
		foreach ($parts as $part) {
			if ('.'  == $part){
				continue;
			}
			if ('..' == $part) {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		$path = implode(DIRECTORY_SEPARATOR, $absolutes);
		// resolve any symlinks
		if( function_exists('readlink') && file_exists($path) && linkinfo($path)>0 ){
			$path = readlink($path);
		}
		// put initial separator that could have been lost
		$path = !$unipath ? '/'.$path : $path;
		$path = $unc ? '\\\\'.$path : $path;
		return $path;
	}


	/**
	 * Get the extension of a file
	 * @param  string $file Path to the file or its name
	 * @return string The extension
	 */
	public static function extension($file)
	{
		$extension  = strtolower(substr(strrchr($file, "."), 1));

		return $extension;
	}
	
	/**
	 * Return the name of a file from a path
	 * @param  string $path The path
	 * @return string The filename
	 */
	public static function name($path) 
	{
		$file = basename($path);
		$file = basename($path, '.' . static::extension($path));
		
		return $file;
	}

	/**
	 * Filename validator
	 * @param  string $name The initial name
	 * @return string The cleaned name
	 */
	public static function filename_validator($name)
	{
		return preg_replace("([^\w\s\d\-_~,;\[\]\(\].]|[\.]{2,})", '', $name);
	}

	/**
	 * Get the directory modification date
	 * @param  string $path The path of the directory
	 * @return number The most recent filemtime value for the files inside
	 */
	public static function directorymtime($path)
	{
		$iterator = new DirectoryIterator($path);

		$mtime = -1;
		$file;
		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isFile()) {
				if ($fileinfo->getMTime() > $mtime) {
					$file = $fileinfo->getFilename();
					$mtime = $fileinfo->getMTime();
				}
			}
		}

		return $mtime;
	}

	/**
	 * Copy remote file over HTTP one small chunk at a time.
	 * - see http://stackoverflow.com/questions/4000483/how-download-big-file-using-php-low-memory-usage
	 * @param  string  $infile  The full URL to the remote file
	 * @param  string  $outfile The path where to save the file
	 * @return boolean Whether the downloaded file exists or not locally
	 */
	public static function download($infile, $outfile) 
	{
		$chunksize = 10 * (1024 * 1024); // 10 Megs

		/**
		* parse_url breaks a part a URL into it's parts, i.e. host, path,
		* query string, etc.
		*/
		$parts = parse_url($infile);
		$i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
		$o_handle = fopen($outfile, 'wb');

		if ($i_handle == false || $o_handle == false) {
			return false;
		}

		if (!empty($parts['query'])) {
			$parts['path'] .= '?' . $parts['query'];
		}

		/**
		* Send the request to the server for the file
		*/
		$request = "GET {$parts['path']} HTTP/1.1\r\n";
		$request .= "Host: {$parts['host']}\r\n";
		$request .= "User-Agent: Mozilla/5.0\r\n";
		$request .= "Keep-Alive: 115\r\n";
		$request .= "Connection: keep-alive\r\n\r\n";
		fwrite($i_handle, $request);

		/**
		* Now read the headers from the remote server. We'll need
		* to get the content length.
		*/
		$headers = array();
		while(!feof($i_handle)) {
			$line = fgets($i_handle);
			if ($line == "\r\n") break;
			$headers[] = $line;
		}

		/**
		* Look for the Content-Length header, and get the size
		* of the remote file.
		*/
		$length = 0;
		$exists = true;
		foreach($headers as $header) {
			if (stripos($header, 'Content-Length:') === 0) {
				$length = (int)str_replace('Content-Length: ', '', $header);
			}
			if (stripos($header, ' 404 ')) {
				$exists = false;
			}
		}

		/**
		* Start reading in the remote file, and writing it to the
		* local file one chunk at a time.
		*/
		$cnt = 0;
		while(!feof($i_handle)) {
			$buf = '';
			$buf = fread($i_handle, $chunksize);
			$bytes = fwrite($o_handle, $buf);
			if ($bytes == false) {
				return false;
			}
			$cnt += $bytes;

			/**
			* We're done reading when we've reached the conent length
			*/
			if ($cnt >= $length) break;
		}

		fclose($i_handle);
		fclose($o_handle);
		//return $cnt;

		if ($exists == false)
		{
			unlink($outfile);
		}

		return $exists;
	}


	/**
	 * Simple handler for file upload
	 * -todo http://php.net//manual/ro/session.upload-progress.php
	 * -todo https://github.com/chemicaloliver/PHP-5.4-Upload-Progress-Example
	 * @param  string $name         Name of the file
	 * @param  number [$size=null]  Allowed size in MB
	 * @param  array  [$ext=null]   Allowed exensions
	 * @param  array  [$types=null] Allowed file types
	 * @return array  The information about the file
	 */
	public function upload($name, $size=null, $ext=null, $types=null)
	{
		$file = $_FILES[$name]; 

		// Check upload errors
		if ($file['error'] > 0)
		{
			return false;
		}

		// Check the size
		if ($size != null)
		{
			$size = $size * 1024 * 1024;
			if ($file['size'] > $size)
			{
				return false;
			}
		}

		// Check the extension
		if ($ext != null)
		{
			if (is_array ($ext) && !in_array($this->extension($file['name']), $ext))
			{
				return false;
			}
			elseif (is_string($ext) && $this->extension($file['name']) != $ext)
			{
				return false;
			}
		}

		// Check the type
		if ($types != null)
		{
			if (is_array ($types) && !in_array($file['type'], $types))
			{
				return false;
			}
			elseif (is_string($types) && $file['type'] != $types)
			{
				return false;
			}
		}


		return $file;
	}
}

/**
* Aliases used by class for easier programming
*/
function r()   { return call_user_func_array(array('files', 'r'),    func_get_args()); }
function w()   { return call_user_func_array(array('files', 'w'),    func_get_args()); }
?>