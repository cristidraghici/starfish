<?php
/**
 * Starfish initial commands
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require the needed files
require_once('../../starfish.php');

/**
 * Configuration
 */
// Storage path
starfish::config('_starfish', 'storage', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'storage');

// Add a connection to the database
starfish::config('_starfish', 'databases', array(
        'test' => array(
                'type' => 'textdb', 
                'parameters' => array(
                        'name'      => 'test',
                        'scramble'      => 'test',
                        'encrypt'       => 'test'
                )
        )
));

// Initiate Starfish
starfish::init();

/**
 * The script itself
 */

starfish::obj('database')->query("select * from people");
?>