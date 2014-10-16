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

on('post', '/contact', function(){ 
        starfish::obj('contact')->send();
});

on('get', '/', function(){
        echo starfish::obj('tpl')->view('header');
        echo starfish::obj('tpl')->view('about');
        echo starfish::obj('tpl')->view('examples', array(
                'examples' => obj('examples')->getExamples()
        ));
        echo starfish::obj('tpl')->view('history');
        echo starfish::obj('tpl')->view('team', array(
                'contributors' => obj('team')->getMembers()
        ));
        echo starfish::obj('tpl')->view('contact');
        echo starfish::obj('tpl')->view('footer');     
});

on('get', '/:all', function($all){
        echo 'Page does not exist!';
});


// Execute the router
starfish::obj('routes')->run();
?>