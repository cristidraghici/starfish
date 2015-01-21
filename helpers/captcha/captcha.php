<?php
// Include starfish
require_once("../../starfish.php");
starfish::init();

// Set the default width and height
$width  = (is_numeric(starfish::obj('parameters')->get('width'))) ? starfish::obj('parameters')->get('width') : 70;
$height = (is_numeric(starfish::obj('parameters')->get('height'))) ? starfish::obj('parameters')->get('height') : 35;

// Get the background files
$bgs = array();
$all = starfish::obj('files')->all( @realpath(__DIR__) . DIRECTORY_SEPARATOR );
foreach ($all['files'] as $key=>$value)
{
	if (in_array(starfish::obj('files')->extension($value), array('png')))
	{
		$bgs[] = $value;
	}
}

// Establish the captcha value to show
if (starfish::obj('parameters')->get('mode') == 'new' || starfish::obj('parameters')->session('captcha')  != starfish::obj('captcha')->captcha_number_of_chars)
{
	starfish::obj('captcha')->refreshCaptcha();
}
$string = reg::obj('captcha')->returnCaptcha();

starfish::obj('captcha')->captcha_image($height, $width, $string, $bgs);
?>