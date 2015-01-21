<?php
if (!class_exists('starfish')) { die(); }

/**
 * Time parsing and converting
 *
 * @package epoch
 * @subpackage starfish.objects.epoch
 */
class epoch
{	
	/**
	 * Convert seconds to readable
	 * 
	 * @param number $secs Seconds to converting
	 * @return string The readable format for the duration
	 */
	function seconds_to_readable($secs)
	{
		$units = array(
			"week"   => 7*24*3600,
			"day"    =>   24*3600,
			"hour"   =>      3600,
			"minute" =>        60,
			"second" =>         1,
		);

		// specifically handle zero
		if ( $secs == 0 ) return "0 seconds";

		$s = "";

		foreach ( $units as $name => $divisor ) {
			if ( $quot = intval($secs / $divisor) ) {
				$s .= "$quot $name";
				$s .= (abs($quot) > 1 ? "s" : "") . ", ";
				$secs -= $quot * $divisor;
			}
		}

		return substr($s, 0, -2);
	}

	/**
	 * Method to return miliseconds
	 */
	public function millitime()
	{
		$microtime = microtime();
		$comps = explode(' ', $microtime);

		return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
	}
}
?>