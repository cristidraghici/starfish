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
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR;
    $files = array(
        // Traits
        'config.php',
        'variables.php',
        'registry.php',
        'routing.php',
        'mvc.php',
        'exec.php',
        'errors.php'
    );
    
    if (PHP_VERSION_ID >= 50400)
    {
        /*
        * The minimum PHP 5.4 requirement is met.
        */
        
        // Include the system files
        foreach ($files as $value)
        {
            require_once( $path . $value);
        }
        
        // Include the main file
        require_once( $path . '_starfish.php');
    }
    else
    {
        die('Your PHP version is outdated. Please install 5.4 minimum.');
    }
    
    // Init the framework
    starfish::init();
    
    // Include the aliases
    require_once( $path . '_aliases.php');
}
?>