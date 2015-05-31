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

	function translate($word, $from, $to, $repeat = true)
	{
		$word = @urlencode($word);
		$url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=" . $from . "&tl=" . $to . "&dt=t&q=" . ($word) . "&ie=UTF-8&oe=UTF-8";
        
		$html = obj('curl')->single(
			obj('curl')->get($url)
		);
        
        // Check if captcha is needed
        if (stristr($html, 'http://translate.google.com/translate_a'))
        {
            if ($repeat == true)
            {
                $this->translate($word, $from, $to, false);
            }
            else
            {
                die('Captcha is needed: <a href="'.$url.'">enter it here</a>');
            }
        }
        elseif (isset($parts[17]) && trim($parts[17]) === 'https://www.google.com')
        {
            die('Requests blocked by google.');
        }
        
		$parts = explode('"', $html);
        
		return  $parts[1];
	}
}
?>