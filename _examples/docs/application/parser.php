<?php
if (!class_exists('starfish')) { die(); }

class parser
{
	public function getclass($reflector)
	{
		$info = array(
			'name' => $reflector->name,
			'comments' => $this->cleanComment($reflector->getDocComment()),
			'constants'=> $reflector->getConstants(),
			'properties' => $reflector->getProperties()
		);
		
		return $info;
	}
    public function cleanComment($string) 
    {
        $parts = explode("\n", $string);
        foreach ($parts as $key=>$value) 
        {
            $value = trim($value);
            if (substr($value, 0, 2) === '/*') { $value = substr($value, 2); }
            if (substr($value, 0, 2) === '*/') { $value = substr($value, 2); }
            if (substr($value, 0, 1) === '*') { $value = substr($value, 1); }
            if (strlen($value) > 0)
            {
                $parts[$key] = $value;
            }
            else
            {
                unset($parts[$key]);
            }
        }
        
        return implode("\n", $parts);
    }

	public function methods($reflector)
	{
		$info = array();
		$list = $reflector->getMethods();
		
		foreach ($list as $key=>$value)
		{
			$info[] = array(
				'name' => $value->name,
				'comments' => $this->cleanComment($value->getDocComment()),
				'parameters' => $value->getParameters()
			);
		}
		
		return $info;
	}

	public function aliases($content)
	{
		$info = array();
		
		#preg_match_all('#function err()   { return call_user_func_array(array(\'errors\', \'err\'),    func_get_args()); }#is', $content, $matches);
		
		return $info;
	}
}

?>