<?php
if (!class_exists('starfish')) { die(); }

/**
 * Scramble string
 *
 * @package starfish
 * @subpackage starfish.objects.scramble
 */
class scramble
{	
	/**
	 * Declare used variables
	 *
	 * $letters - Letters to hash
	 * $hash - String to use for building the corresponding encrypted sting
	 */
	private $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789.,!$*+-?@#';
	private $hash    = 'aBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSTtUuVvWwXxYyZz0123456789.,!$*+-?@#As';

	/**
	 * Constructor
	 */
	function __construct()
	{
		if (strlen(starfish::config('_scramble', 'hash')) > 0)
		{
			$this->hash( starfish::config('_scramble', 'hash') );
		}

		return true;
	}

	/**
	 * Set a new hash
	 *
	 * @param string $string The new hash
	 */
	public function hash($string=null)
	{
		if (strlen($string) > 0)
		{
			$string = (string)$string;
			$hash   = $this->letters;

			$string = preg_replace('#\s#', '', $string);
			for ($a=0; $a<strlen($string); $a++)
			{
				$letter  = (string)$string[$a];
				if (stristr($hash, $letter))
				{
					$hash = str_replace($letter, '', $hash) . $letter;
				}
			}

			$this->hash = $hash;
		}

		return true;
	}

	/**
	 * The main scramble function
	 *
	 * @param string $string The string
	 * 
	 * @return string $string The new string
	 */
	public function encode($string)
	{ 
		$string = (string)$string;

		for ($a=0; $a<strlen($string); $a++)
		{
			$letter  = (string)$string[$a];
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

	/**
	 * The main unscramble function
	 *
	 * @param string $string The string
	 * 
	 * @return string $string The new string
	 */
	public function decode($string)
	{
		$string = (string)$string;

		for ($a=0; $a<strlen($string); $a++)
		{
			$letter  = (string)$string[$a];
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