<?php
/**
 * Starfish initial commands
 */
// Require the needed files
require_once('../../starfish.php');

// The url where this example is run
$site_url = 'http://<please enter a new value>/starfish/_examples/curl-requests/';

// Initiate Starfish
starfish::init();

if ($site_url == 'http://<please enter a new value>/starfish/_examples/curl-requests/')
{
        starfish::obj('errors')->err(401, 'Please edit the main file of this example at line #9!');
}

/**
 * The script itself
 */
on('get', '/:all', function($all) use ($site_url) {
        echo starfish::obj('curl')->single(
                starfish::obj('curl')->post($site_url, array(), 
                                            array(
                                                    'var1' => 'value1',
                                                    'var2' => 'value2'
                                            ))
        );
});

on('post', '/', function(){
        print_r( post() );
});

// Execute the router
starfish::obj('routes')->run();
?>