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
obj('authentication');
obj('categories');
obj('notes');
obj('users');


// The default path
on('get', '/:all', function() {
        if (obj('authentication')->check() == false)
        {
                redirect('./login');
        }
        else
        {
                redirect('./notes');
        }
});

// Execute the router
on();
?>