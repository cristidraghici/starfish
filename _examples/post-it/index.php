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

// Execute the router
on();
?>