<?php
/**
 * Starfish initial commands
 */
// Require the needed files
require_once('../../starfish.php');
require_once('config.php');

// Initiate Starfish
starfish::init('tpl');

/**
 * The script itself
 */
obj('authentication');
obj('categories');
obj('users');
obj('notes');

// The default path
function defaultPath () {
        if (obj('authentication')->check() == false)
        {
                redirect('./login');
        }
        else
        {
                redirect('./notes');
        }
}
on('get', '/:all', 'defaultPath' );
on('post', '/:all', 'defaultPath' );
on('put', '/:all', 'defaultPath' );
on('delete', '/:all', 'defaultPath' );

// Execute the router
on();
?>