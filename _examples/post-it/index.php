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
// With parameter
starfish::obj('routes')->on('get', '/:alpha', function($param) {
        echo 'With param: ' . starfish::obj('scramble')->decode( starfish::obj('scramble')->encode($param) );
});

// The default path
starfish::obj('routes')->on('get', '/:all', function() {
        echo 'Stuff is working well!';
});

// Execute the router
starfish::obj('routes')->run();
?>