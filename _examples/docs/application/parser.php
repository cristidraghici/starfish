<?php
if (!class_exists('starfish')) { die(); }

class parser
{
	public function getclass($reflector)
	{
		$info = array(
			'name' => $reflector->name,
			'comments' => $reflector->getDocComment(),
			'constants'=> $reflector->getConstants(),
			'properties' => $reflector->getProperties()
		);
		
		return $info;
	}

	public function methods($reflector)
	{
		$info = array();
		$list = $reflector->getMethods();
		
		foreach ($list as $key=>$value)
		{
			$info[] = array(
				'name' => $value->name,
				'comments' => $value->getDocComment(),
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