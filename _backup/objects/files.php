<?php
if (!class_exists('starfish')) { die(); }

class files
{
	# read folder contents
	function all($root = './')
	{
		$files  = array('folders'=>array(), 'files'=>array());
        
		if (is_dir($root))
        {
			if ($dir_handler = opendir($root)) 
			{
				while (($file = readdir($dir_handler)) !== false) 
				{
					if ($file != '.' && $file != '..')
					{
                        echo $root . $file . "<br>\n";
						if (filetype($root . $file) == 'file')
						{
							$files['files'][] = $root . $file;
						}
						else
						{
							$files['folders'][] = $root . $file;
						}
					}
				}
				closedir($dir_handler);
			}
		}
		return $files;
	} 
    
    function dirToArray($dir)
    {
    
        $result = array();
        
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value)
        {
            if (!in_array($value,array(".","..")))
            {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
                {
                    $result[$value] = starfish::obj('files')->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                }
                else
                {
                    $result[] = $value;
                }
            }
        }
        
        return $result;
    }
    
    function filesFromDir($dir)
    {
        $result = array();
        
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value)
        {
            if (!in_array($value,array(".","..")))
            {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
                {
                    $list = starfish::obj('files')->filesFromDir($dir . DIRECTORY_SEPARATOR . $value);
                    foreach ($list as $k=>$v)
                    {
                        $result[$k] = $dir . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR . $v;
                    }
                }
                else
                {
                    $result[$value] = $value;
                }
            }
        }
        
        return array_values($result);
    }
    
    # recursively remove a directory
    function rrmdir($dir)
    {
        foreach(glob($dir . '/*') as $file)
        {
            if(is_dir($file))
            {
                rrmdir($file);
            }
            else
            {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }

    # read the content of a file
    public function r ($x)
    {
        if (file_exists($x))
        {
            $file = @fopen($x, "r");
            $size = filesize($x);
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
    
    # write to a file
    public function w ($x, $data, $type='w')
    {
        // Create the path 
        #@mkdir(dirname($x), 0777, true);
        
        // Write to file
        $numefisier=@fopen($x,$type);
        $return = true;
        if (@fwrite($numefisier, $data) === FALSE)
        {
            $return = false;
        }
        @fclose($numefisier);
        
        return $return; 
    }
    
    public function filename($file)
    {
        $len = 0;
        $len = strlen($this->extension($file));
        if ($len > 0) {
            $len = $len + 1;
            $file = substr($file, 0, -$len);
        }
        
        return $file;
    }
    public function extension($file)
    {
        $extension  = strtolower(substr(strrchr($file, "."), 1));
        return $extension;
    }
	
	
	function download($file_source, $file_target) 
	{
		$rh = fopen($file_source, 'rb');
		$wh = fopen($file_target, 'wb');
		if (!$rh || !$wh) {
			return false;
		}

		while (!feof($rh)) {
			if (fwrite($wh, fread($rh, 1024)) === FALSE) {
				return false;
			}
		}

		fclose($rh);
		fclose($wh);

		return true;
	}
}

?>