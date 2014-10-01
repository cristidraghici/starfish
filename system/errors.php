<?php
if (!class_exists('starfish')) { die(); }

/**
 * Handler for errors
 *
 * @package starfish
 * @subpackage starfish.system.errors
 *
 * @todo This is a basic error handler. Needs to be developed futher soon.
 */
class errors
{

        /**
         * Main message function
         * 
         * This function stores a message in the session if two parameters are specified, or retrieves one and deletes it from the session, when only one parameter is given.
         * 
         * @param string $location Location of the error
         * @param string $message Body of the error
         */
        public static function message()
        {
                $args = func_get_args();
                $message = '';

                switch (count($args))
                {
                        case 1:
                        $message = session('_starfish_errors-' . $args[0]);
                        session('_starfish_errors-' . $args[0], null);
                        break;

                        case 2:
                        $message = $args[1];
                        session('_starfish_errors-' . $args[0], $args[1]);
                        break;
                }

                return $message;
        }

        /**
         * Main error function
         * 
         * @param string $code Code of the error
         * @param string $message Body of the error
         */
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
                //$message = "{$code} {$message}";
                echo $message;

                if (starfish::config('_starfish', 'debug') == true) { static::backtrace(); exit; }
        }

        /**
         * Show the backtrace for the error
         */
        public static function backtrace()
        {
                echo PHP_EOL;
                echo '<pre>';
                print_r(debug_backtrace());
                echo '</pre>';

                return true;
        }
        
        /**
         * Convert an array of errors to a single string
         * 
         * @param mixed $err The error/errors
         * @return string The only error string
         */
        public function toString($err='')
        {
                $string = '';

                if (gettype($err) == 'array')
                {
                        foreach ($err as $key=>$value)
                        {
                                $string .= $key . ': ' . $value . ', ';
                        }
                        $string = substr($string, 0, -2);
                }
                else
                {
                        $string = (string)$err;
                }

                return $string;
        }
}

/**
 * Aliases used by class for easier programming
 */
function err()   { return call_user_func_array(array('errors', 'err'),    func_get_args()); }
?>