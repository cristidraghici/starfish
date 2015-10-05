<?php
if (!class_exists('starfish')) { die(); }

/**
 * Common functions that are not part of a complex enough structure to build a module
 *
 * @package starfish
 * @subpackage starfish.objects.common
 */
class common
{	
	/**
	 * Extract the domain from a url
	 * See: http://stackoverflow.com/questions/16027102/get-domain-name-from-full-url
	 * 
	 * @param  string  $url The whole url to parse
	 * @return string The domain, if available
	 */
	function get_domain($url)
	{
		$pieces = parse_url($url);
		$domain = isset($pieces['host']) ? $pieces['host'] : '';
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
			return $regs['domain'];
		}
		
		return '';
	}

	/**
	 * Creates a compressed zip file
	 * See: http://davidwalsh.name/create-zip-php
	 * 
	 * @param  array   [$files            = array()] The list of files to add
	 * @param  string  $destination       = ''  The destination
	 * @param  boolean $overwrite         = false Overwrite the destination, if it already exists
	 * @return boolean True if the archive has been created
	 */
	function create_zip($files = array(),$destination = '',$overwrite = false) {
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($destination) && !$overwrite) { return false; }
		//vars
		$valid_files = array();
		//if files were passed in...
		if(is_array($files)) {
			//cycle through each file
			foreach($files as $file) {
				//make sure the file exists
				if(file_exists($file)) {
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if(count($valid_files)) {
			//create the archive
			$zip = new ZipArchive();
			if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				return false;
			}
			//add the files
			foreach($valid_files as $file) {
				$zip->addFile($file,$file);
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

			//close the zip -- done!
			$zip->close();

			//check to make sure the file exists
			return file_exists($destination);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Create a tree from a given array
	 * - see http://stackoverflow.com/questions/4196157/create-array-tree-from-array-list
	 * @param  array  $source                    Source of data
	 * @param  string $columnIdentifier          Id of the column
	 * @param  string $columnParent              Name of the column where the id of the father is saved
	 * @param  string [$childrenName='children'] Name of the column where the children will move
	 * @param  string [$parentValue=null]        Value for which to search for results
	 * @return array  Generated tree file
	 */
	function treeze ($source, $columnIdentifier, $columnParent, $childrenName='children', $parentValue=null)
	{
		$list = array();

		foreach ($source as $key=>$item)
		{
			if ($item[$columnParent] == $parentValue) 
			{
				$children = $this->treeze($source, $columnIdentifier, $columnParent, $childrenName, $item[$columnIdentifier]);
				if (count($children) > 0)
				{
					$item[$childrenName] = $children;
				}

				$list[] = $item;
			}
		}

		return $list;
	}

	/**
     * Transform an array into an ini string
     * @param  array  $a       The source array
     * @param  array  [$parent = array()] The parent array
     * @return string The output string
     */
	function arr2ini($a, $parent = array())
	{
		$out = '';
		foreach ($a as $k => $v)
		{
			if (is_array($v))
			{
				//subsection case
				//merge all the sections into one array...
				$sec = array_merge((array) $parent, (array) $k);
				//add section information to the output
				$out .= '[' . join('.', $sec) . ']' . PHP_EOL;
				//recursively traverse deeper
				$out .= arr2ini($v, $sec);
			}
			else
			{
				//plain key->value case
				$out .= $k .'="' . (string)$v . '"' . PHP_EOL;
			}
		}
		return $out;
	}

	/**
	 * Convert a text retrieved from a sql file to a list of commands
	 * @param  string $text       Sql file content
	 * @param  string [$eol="\n"] EOL character
	 * @return array  The list of commands returned
	 */
	function parse_mysql_string($text, $eol="\n") 
	{
		$list = array();
		$cmd = '';

		$lines = explode($eol, $text);
		foreach ($lines as $key=>$line)
		{
			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '')
			{
				continue;
			}
			
			// Add the line to the current command
			$cmd .= $line;

			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';')
			{
				// Store
				$list[] = $cmd;

				// Reset temp variable to empty
				$cmd = '';
			}
		}

		return $list;
	}
}
?>