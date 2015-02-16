<?php
if (!class_exists('starfish')) { die(); }

/**
 * Get google maps coordinates
 *
 * @package starfish
 * @subpackage starfish.objects.googlemaps
 * 
 * @see https://rupeshpatel.wordpress.com/2012/06/23/usage-of-google-translator-api-for-free/
 */
class googletranslate
{	
	private $timeout = 10;
	public $debugInfo = '';

	public function init()
	{
		/*
		 * https://translate.google.ro/translate_a/single?client=t&sl=en&tl=ro&oe=UTF-8&otf=2&srcrom=1&ssel=0&tsel=3&q=hello
		 */
		if (!extension_loaded('curl')) { starfish::obj('errors')->error(400, "PHP required extension - curl - not loaded."); }
		return true;
	}

	function translate($word, $from, $to)
	{
		$word = @urlencode($word);
		$url = "http://translate.google.com/translate_a/t?client=t&text=$word&hl=".$to."&sl=".$from."&tl=".$to."&ie=UTF-8&oe=UTF-8&multires=1&otf=1&pc=1&trs=1&ssel=3&tsel=6&sc=1";

		$html = obj('curl')->single(
			obj('curl')->get($url)
		);

		$parts = explode('"', $html);
		return  $parts[1];
	}
}
?>