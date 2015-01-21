<?php
if (!class_exists('starfish')) { die(); }

class team
{
	function getMembers()
	{
		$url = 'https://api.github.com/repos/cristidraghici/starfish/contributors';

		if (starfish::obj('cache')->exists($url, 3600))
		{
			$content = starfish::obj('cache')->get($url);
		}
		else
		{
			$content = starfish::obj('curl')->quickGet($url);
			starfish::obj('cache')->add($url, $content);
		}

		$content = @json_decode($content, true);

		return $content;
	}
}

?>