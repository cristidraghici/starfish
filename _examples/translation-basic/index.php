<?php
/**
 * Starfish initial commands
 */
// Require the needed files
require_once('../../starfish.php');
require_once('config.php');

// Initiate Starfish
starfish::init();

/**
 * The script itself
 */

// The default path
starfish::obj('routes')->on('get', '/:all', function($from='en', $to='ro') {
	$string = strlen(get('t')) > 0 ? get('t') : 'Hello world!';
	
	echo obj('googletranslate')->translate($string, $from, $to);
});

// Execute the router
starfish::obj('routes')->run();
?>