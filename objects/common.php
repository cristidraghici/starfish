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
	 * Get an empty row from the given table
	 *
	 * @param  string table The table name
	 * @param  object [$override=null]     Values used to override the default null value for each of the columns
	 * @param  object [$conn=null]     The connection to use
	 * @return array An array containing the table structure
	 */
	public function get_mysql_default_row($table, $override=array(), $conn=null)
	{
		$output = array();
		
		$resource = starfish::obj('database')->query('select * from `'.$table.'`', $conn);
		$info = $resource->fetch_fields();
		
		foreach ($info as $key=>$value) 
		{
			$output[$value->name] = null;
		}
		
		return $output;
	}

	/**
	 * Backup the data in a mysql database
	 * https://dl.dropboxusercontent.com/u/18434517/mysql_backup.php
	 * 
	 * @param  string [$filename=null] The filename where to save the data (reccommended). If not specified, the backup will be returned as a string.
	 * @param  object [$conn=null]     The connection to use
	 * @return string The output text
	 */
	public function backup_mysql_data($filename=null, $conn=null)
	{
		$output = '';
		obj('files')->w($filename, '');
		
		// Select the tables
		$tablesResource = starfish::obj('database')->query('show tables', $conn);
		while ($table = starfish::obj('database')->fetch($tablesResource))
		{
			$table = $table['Tables_in_registry'];
			
			// Select the rows
			$rowsResource = starfish::obj('database')->query('select * from `'.$table.'`', $conn);
			while ($row = starfish::obj('database')->fetch($rowsResource)) {
				$nor = count($row);
				$datas = array();
				foreach($row as $r){
					$datas[] = $r;
				}
				
				$lines = '';
				$lines .= "INSERT INTO `".$table."` VALUES (";
				for($i=0; $i<$nor; $i++)
				{
					if($datas[$i]===NULL)
					{
						$lines .= "NULL";
					}
					else if((string)$datas[$i] == "0")
					{
						$lines .= "0";
					}
					else if(filter_var($datas[$i], FILTER_VALIDATE_INT) || filter_var($datas[$i], FILTER_VALIDATE_FLOAT))
					{
						$lines .= $datas[$i];
					}
					else
					{
						$lines .= "'" . addcslashes($datas[$i], '\\'."'") . "'";
					}
					
					if($i==$nor-1)
					{
						$lines .= ");\n";
					}
					else
					{
						$lines .= ",";
					}
				}
				
				if ($filename === null)
				{
					$output .= $lines;
				}
				else
				{
					obj('files')->w($filename, $lines, 'a');
				}
			}
		}
		
		return $output;
	}
	
	/**
	 * Extract the domain from a url
	 * See: http://stackoverflow.com/questions/16027102/get-domain-name-from-full-url
	 * 
	 * @param  string  $url The whole url to parse
	 * @return string The domain, if available
	 */
	public function get_domain($url)
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
	 * @param  array    [$files = array()] The list of files to add
	 * @param  string   $path=''           The path to remove from the filenames
	 * @param  [[Type]] $destination = ''  The destination
	 * @param  [[Type]] $overwrite = false Overwrite the destination, if it already exists
	 * @return boolean  True if the archive has been created
	 */
	public function create_zip($files = array(), $path='', $destination = '', $overwrite = false) {
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
			if($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
				return false;
			}
			//add the files
			foreach($valid_files as $file) {
				$filename = substr($file, strlen($path));
				$filename = str_replace('\\', '/', $filename);
				
				$zip->addFromString ($filename, starfish::obj('files')->r($file));
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
			//exit;
			
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
	public function treeze ($source, $columnIdentifier, $columnParent, $childrenName='children', $parentValue=null)
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
	public function arr2ini($a, $parent = array())
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
	public function parse_mysql_string($text, $eol="\n") 
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