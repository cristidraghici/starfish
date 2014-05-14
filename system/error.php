<?php
if (!class_exists('starfish')) { die(); }

class error
{
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

?>