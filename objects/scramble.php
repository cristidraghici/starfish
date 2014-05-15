<?php
if (!class_exists('starfish')) { die(); }

class scramble 
{
	private $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789.,!$*+-?@#';
	private $hash    = 'aBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSTtUuVvWwXxYyZz0123456789.,!$*+-?@#As';
	
	function scramble()
	{
		if (strlen(starfish::$config['hash']) > 0)
		{
			$this->hash( starfish::$config['hash'] );
		}
		
		return true;
	}
	
	function hash($string=null)
	{
		if (strlen($string) > 0)
		{
			$hash   = $this->letters;
			
			$string = preg_replace('#\s#', '', $string);
			for ($a=0; $a<strlen($string); $a++)
			{
				$letter  = $string[$a];
				$hash = $letter . str_replace($letter, '', $hash);
			}
			
			$this->hash = $hash;
		}
		
		return true;
	}
	
	function encode($string)
	{
		for ($a=0; $a<strlen($string); $a++)
		{
			$letter  = $string[$a];
			$pos     = strpos($this->letters, $letter);
			if (is_numeric($pos))
			{
				$replace = $this->hash[$pos];
				$string[$a] = $replace;
			}
			
			unset($replace, $pos, $letter);
		}
		
		return $string;
	}
	
	function decode($string)
	{
		for ($a=0; $a<strlen($string); $a++)
		{
			$letter  = $string[$a];
			$pos     = strpos($this->hash, $letter);
			if (is_numeric($pos))
			{
				$replace = $this->letters[$pos];
				$string[$a] = $replace;
			}
			
			unset($replace, $pos, $letter);
		}
		
		return $string;
	}
}

?>