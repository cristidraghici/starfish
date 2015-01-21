<?php
if (!class_exists('starfish')) { die(); }

// Make a configuration
starfish::config('_starfish', 'app_objects', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application/objects' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'template', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application/views' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'storage', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

starfish::config('_starfish', 'site_url', 'http://'.$_SERVER['HTTP_HOST'].'/starfish/_examples/post-it/');

starfish::config('_starfish', 'site_title', 'PostIT Notes');
starfish::config('_starfish', 'site_description', 'Keep track of your important things');

// Add a connection to the database
starfish::config('_starfish', 'databases', array(
	'postit' => array(
		'type' => 'textdb', 
		'parameters' => array(
			'name'      => 'postit',
			'scramble'      => 'post-it',
			'encrypt'       => 'post-it'
		)
	)
));

?>