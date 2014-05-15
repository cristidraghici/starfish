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
    
    // Traits
    require_once( $path . 'config.php'); 
    require_once( $path . 'variables.php'); 
    require_once( $path . 'registry.php'); 
    require_once( $path . 'routing.php'); 
    require_once( $path . 'mvc.php'); 
    require_once( $path . 'exec.php');
    
    // The main file
    require_once( $path . 'starfish.php');
    starfish::init();
}
?>