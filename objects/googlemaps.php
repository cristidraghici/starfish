<?php
if (!class_exists('starfish')) { die(); }

/**
 * Get google maps coordinates
 *
 * @package starfish
 * @subpackage starfish.objects.googlemaps
 */
class googlemaps
{	
	public static function location($string)
	{
		$json = starfish::obj('curl')->get('http://maps.googleapis.com/maps/api/geocode/json?address='. $string .'&sensor=true&language=ro');
		$data = @json_decode($json, true);
                
		if (is_array($data))
		{
			return $data;
		}
		
		return array();
	}
}
?>