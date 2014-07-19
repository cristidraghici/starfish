<?php
/**
 * Starfish initial commands
 */
// Require the needed files
require_once('../../starfish.php');

// Make a configuration

// Initiate Starfish
starfish::init();

/**
 * The script itself
 */
// The default path
starfish::obj('routes')->on('get', '/', function() {
	echo 'Stuff is working well!';
});

// Execute the router
starfish::obj('routes')->run();
?>