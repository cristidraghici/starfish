<?php
if (!class_exists('starfish')) { die(); }

class ftp
{
	public $connections = array();
	public $errors = array();
	
	function connect($host, $user, $pass=null, $path='/', $port='21')
	{
		$name = $host . '-'. $user . '-' . $port;
		
		if (!isset($this->connections[$name]))
		{
			$this->connections[$name] = @ftp_connect($host, 21) or starfish::error(401, "Cannot connect to " . $host . ':' . $port);
			@ftp_login($this->connections[$name], $user, $pass) or starfish::error(401, "Cannot login to " . $host . ':' . $port . ' with ' . $user );
		}
		
		if (!isset($this->connections[$name]) && $pass == null)
		{
			starfish::error(401, "Cannot use " . $host . ':' . $port . '. Please specify login information!');
		}
		else
		{
			return $name;
		}
		
		return false;
	}
	function disconnect($conn)
	{
		if (isset($this->connections[$conn]))
		{
			@ftp_close($this->connections[$conn]);
			unset($this->connections[$conn]);
		}
		
		return false;
	}
	
	function upload($conn, $source, $target)
	{
		if (isset($this->connections[$conn]))
		{
			$upload = @ftp_put($this->connections[$conn], $target, $source, FTP_ASCII);
            if (!$upload ) 
			{
				$this->errors[$conn][$source] = 'Error uploading.';
			}
		}
		
		return false;
	}
	
	function all($conn, $path='./', $type=false)
	{
		$all = array();
		
		if (isset($this->connections[$conn]))
		{
			$all = @ftp_nlist($this->connections[$conn], $path);
			if ($all != false && $type == true)
			{
				$files  = array('folders'=>array(), 'files'=>array());
				foreach ($all as $key=>$value)
				{
					if ($this->ftp_is_dir($conn, $value))
					{
						$files['folders'][] = $value; 
					}
					else
					{
						$files['files'][] = $value;
					}
				}
				
				$all = $files;
			}
		}
		
		return $all;
	}
	
	function ftp_is_dir($conn, $dir) 
	{
		if (isset($this->connections[$conn]))
		{
			if (@ftp_chdir($this->connections[$conn], $dir)) 
			{
				ftp_chdir($this->connections[$conn], '..');
				return true;
			}
			else
			{
				return false;
			}
		}
		
		return false;
	}
}

?>