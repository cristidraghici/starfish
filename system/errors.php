<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for errors
 *
 * @package starfish
 * @subpackage starfish.system.objects
 *
 * @todo This is a basic error handler. Needs to be developed futher soon.
 */
class errors
{
    public static function err($code, $message='Page error')
    {
		// Ensure the code is a string
        $code = (string) $code;
        
        // set the response code
        header(
            "{$_SERVER['SERVER_PROTOCOL']} {$code} {$message}",
            true,
            (int) $code
        );
		
		// set the response message
        $message = "{$code} {$message}";
        echo $message;
        
        if (starfish::config('_starfish', 'debug') == true) { starfish::backtrace(); exit; }
    }
	
    public static function backtrace()
    {
        echo PHP_EOL;
        echo '<pre>';
        print_r(debug_backtrace());
        echo '</pre>';
        
        return true;
    }
}
/**
* Aliases used by class for easier programming
*/
function err()   { return call_user_func_array(array('errors', 'err'),    func_get_args()); }
?>