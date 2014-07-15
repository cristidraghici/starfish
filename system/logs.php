<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for logging
 *
 * @package starfish
 * @subpackage starfish.system.logs
 *
 * @todo Maybe an interface to view the content of the logs.
 */
class logs
{
    /**
     * Init
     *
     * @todo Check the size of the log file, clean it if too big
     * @todo Establish the default date format
     */
    public static function init()
    {
    }
    
    
    /**
     * Write into a log file
     *
     * @param string $file The file where to write the content
     * @param mixed $text The content to store
     */
    public static function add($file, $text)
    {
        // Make sure we are dealign with a string
        if (gettype($text) != 'string') { $text = @serialize($text); }
        
        // write the data
        w($file, $text, 'a');
        
        
        return true;
    }
}

/**
* Aliases used by class for easier programming
*/
function log()   { return call_user_func_array(array('logs', 'add'),    func_get_args()); }
?>