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
        /**
	 * Read the content of a directory
	 *
	 * @param string $path Path to the directory
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

                return $list;
        }

        /**
	 * Recursively remove a directory
	 *
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
                                        self::rrmdir($file);
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
	 * Read the content of a file
	 *
	 * @param string $path Path to the file
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


                        return $data;
                }
                else
                {
                        return false;
                }
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
                // The default return value
                $return = false;

                // Create the path 
                $directory = @realpath($path) . DIRECTORY_SEPARATOR;
                if (!file_exists($directory))
                {
                        @mkdir($directory, 0777, true);
                }

                // Write to file
                if ( (file_exists($path) && is_writable($path)) || (!file_exists($path) && is_writable($directory)) )
                {
                        $resource = @fopen($path, $type);
                        if (@fwrite($resource, $data) !== FALSE)
                        {
                                $return = true;
                        }

                        @fclose($resource);
                }

                return $return;
        }

        /**
        * Get the extension of a file
        *
        * @param string $file Path to the file or its name
        * 
        * @return string The extension
        */
        public static function extension($file)
        {
                $extension  = strtolower(substr(strrchr($file, "."), 1));

                return $extension;
        }


        /**
        * Copy remote file over HTTP one small chunk at a time.
        *
        * @see http://stackoverflow.com/questions/4000483/how-download-big-file-using-php-low-memory-usage
        *
        * @param string $infile The full URL to the remote file
        * @param string $outfile The path where to save the file
        *
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
        *
        * @param string $name Name of the file
        * @param number $size Allowed size in MB
        * @param array $ext Allowed exensions
        * @param array $types Allowed file types
        *
        * @return array The information about the file
        * 
        * @todo http://php.net//manual/ro/session.upload-progress.php
        * @todo https://github.com/chemicaloliver/PHP-5.4-Upload-Progress-Example
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