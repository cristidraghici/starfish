<?php
if (!isset($path)) { die(); }

trait errors
{
    public static function error($code, $callback=null)
    {
        $code = (string) $code;
        
        // this is a hook setup, save and return
        if (is_callable($callback))
        {
            call_user_func($callback, $code);
            return true;
        }
        
        // see if passed callback is a message (string)
        $message = (is_string($callback) ? $callback : 'Page Error');
        
        // set the response code
        header(
            "{$_SERVER['SERVER_PROTOCOL']} {$code} {$message}",
            true,
            (int) $code
        );
        
        #$message = "{$code} {$message}";
        
        exit ($message);
    }
}

?>