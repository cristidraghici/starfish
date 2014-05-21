<?php
if (!isset($path)) { die(); }

trait errors
{
	public static function outputErrorClean($string)
    {
        // Clean the output
        $string = str_replace(
            array("<br />", "\n", "<b>", "</b>"),
            " ",
            $string
        );
        $string = str_replace('&quot;', '"', $string);
        
        // Remove double spaces
        $string = preg_replace('/\n+|\t+|\s+/', ' ', $string);
        $string = trim($string);
        
        return $string;
    }
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
        
        echo $message;
        
        if (starfish::$config['debug'] == true) { starfish::backtrace(); }
        
        exit;
    }
    
    public static function backtrace()
    {
        echo PHP_EOL;
        print_r(debug_backtrace());
        
        return true;
    }
}

?>