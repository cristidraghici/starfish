<?php
/**
 * @author  Cristi DRAGHICI
 * @link    http://blog.draghici.net
 * @version 0.2a
 * 
 * @see     Parts from Dispatch PHP micro-framework were used.
 * @link    https://github.com/noodlehaus/dispatch
 * @license MIT
 * @link    http://opensource.org/licenses/MIT
 */

/** Entry point: file aggregator */

if (!class_exists('starfish'))
{
    if (PHP_VERSION_ID >= 50400)
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR;
        
        // Traits
        require_once( $path . 'config.php'); 
        require_once( $path . 'variables.php'); 
        require_once( $path . 'registry.php'); 
        require_once( $path . 'routing.php'); 
        require_once( $path . 'mvc.php'); 
        require_once( $path . 'exec.php');
        require_once( $path . 'errors.php');
        
        
        // The main file
        require_once( $path . '_starfish.php');
        starfish::init();
        
        // Include the aliases
        require_once( $path . '_aliases.php');
    }
    else
    {
        die('Minimum PHP 5.4 is required.');
    }
}
?>