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

// Connect to the database
starfish::obj('database')->connect('textdb', 'post-it-textdb', 'post-it');

// 
?>