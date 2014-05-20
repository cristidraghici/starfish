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
}

?>