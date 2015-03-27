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

on('post', '/:all', function(){

	$translation = obj('process')->translate();
	
	echo view('header');

	echo view('form', array(
		'translation'=>$translation
	));

	echo view('footer');

} );

// The default path
on('get', '/:all', function(){

	echo view('header');

	echo view('form');

	echo view('footer');

} );

// Execute the router
on();
?>