<?php
if (!class_exists('starfish')) { die(); }

class home
{
	public function init()
	{
		on('get', '/', 		array($this, 'startPage') );
	}

	public function startPage()
	{
		echo starfish::obj('tpl')->view('header');
		echo starfish::obj('tpl')->view('home');
		echo starfish::obj('tpl')->view('footer');     
	}
}

?>