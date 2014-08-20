<?php
if (!isset($path)) { die(); }

trait variables
{
	public static function set($target, $value)
	{
		return starfish::regVar($target, $value);
	}
	public static function get($target)
	{
		return starfish::regVar($target);
	}
	public static function aget($array, $target)
	{
		return starfish::regArr($array, $target);
	}

    public static function regVar($target, $value=null)
    {
        if ($value != null)
        {
            starfish::$variables[$target] = $value;
            return $value;
        }
        elseif (isset(starfish::$variables[$target]))
        {
            return starfish::$variables[$target];
        }
		
		return null;
    }
    public static function regArr($target, $values=null, $value=null)
    {
        if ($value != null && gettype($values) == 'string')
        {
            starfish::$variables[$target][$values] = $value;
            return $value;
        }
        elseif ($value == null && $values != null && gettype($values) == 'array')
        {
            starfish::$variables[$target] = $values;
            return $values;
        }
        elseif ($value == null && $values != null && gettype($values) == 'string')
        {
            return starfish::$variables[$target][$values];
        }
        else
        {
            return starfish::$variables[$target];
        }
    }
    
    
    public static function session($name = null, $value = null)
    {
    
        static $session_active = false;
        
        // stackoverflow.com: 3788369
        if ($session_active === false)
        {
            if (($current = ini_get('session.use_trans_sid')) === false)
            {
                trigger_error(
                    'Call to session() requires that sessions be enabled in PHP',
                    E_USER_ERROR
                );
            }
            
            $test = "mix{$current}{$current}";
            
            $prev = @ini_set('session.use_trans_sid', $test);
            $peek = @ini_set('session.use_trans_sid', $current);
            
            if ($peek !== $current && $peek !== false)
            {
                session_start();
            }
            
            $session_active = true;
        }
        
        $args = func_num_args();
        if ($args === 1)
        {
            return (isset($_SESSION[$name]) ? $_SESSION[$name] : null);
        }
        elseif ($args === 0)
        {
            return (is_array($_SESSION) ? $_SESSION : null);
        }
        
        $_SESSION[$name] = $value;
    }
    
    public static function cookie($name, $value = null, $expire = 31536000, $path = '/')
    {
        static $quoted = -1;
        
        if ($quoted < 0)
        {
            $quoted = get_magic_quotes_gpc();
        }
        
        if (func_num_args() === 1)
        {
            return (isset($_COOKIE[$name]) ? ( $quoted ? stripslashes($_COOKIE[$name]) : $_COOKIE[$name] ) : null );
        }
        
        setcookie($name, $value, time() + $expire, $path);
    }
}

?>