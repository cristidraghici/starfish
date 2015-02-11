<?php
if (!class_exists('starfish')) { die(); }

class generator
{
	public function generate()
	{
		$list = obj('files')->tree('../../', array('../../.git*'));
		print_r($list);
	}
}

?>