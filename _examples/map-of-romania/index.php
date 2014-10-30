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

// The default path
on('get', '/:all', function(){
        
        echo view('header');
        
        echo view('map');
        
        echo view('footer');
        
} );

// Execute the router
on();
?>