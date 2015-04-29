<?php
if (!class_exists('starfish')) { die(); }

// Make a configuration
starfish::config('_starfish', 'app_objects', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'storage', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'template', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR);

// Add a connection to the database
starfish::config('_starfish', 'databases', array(
	'db' => array(
		'type' => 'textdb', 
		'parameters' => array(
			'name'      => 'docs',
			'scramble'      => 'Starfish DOCS',
			'encrypt'       => 'Documentation generated for Starfish PHP Microframework'
		)
	)
));
?>