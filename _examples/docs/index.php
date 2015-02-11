<?php
/**
 * Starfish initial commands
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Require the needed files
require_once('../../starfish.php');
require_once('config.php');

require_once('./libraries/markdown/markdown.php');

// Initiate Starfish
starfish::init();

/**
 * The script itself
 */

// Preload the objects
$files = starfish::obj('files')->all('application/');
foreach ($files['files'] as $key=>$value)
{
    $parts = explode('.', $value);
    starfish::obj($parts[0]);
}


on('get', '/generate', function(){
	obj('generator')->generate();
});

// Other paths
on('get', '/:all', function($all){
	echo 'Page does not exist!';
});


// Execute the router
starfish::obj('routes')->run();
?>