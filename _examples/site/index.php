<?php
/**
 * Starfish initial commands
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Require the needed files
require_once('../../starfish.php');
require_once('config.php');

// Initiate Starfish
starfish::init();

/**
 * The script itself
 */

on('post', '/contact', function(){ 
        starfish::obj('contact')->send();
});

on('get', '/', function()
{
        echo starfish::obj('tpl')->view('header');
        echo starfish::obj('tpl')->view('about');
        echo starfish::obj('tpl')->view('examples');
        echo starfish::obj('tpl')->view('history');
        echo starfish::obj('tpl')->view('team');
        echo starfish::obj('tpl')->view('contact');
        echo starfish::obj('tpl')->view('footer');     
});

on('get', '/:all', function($all)
{
        echo 'Page does not exist!';
});


// Execute the router
starfish::obj('routes')->run();
?>