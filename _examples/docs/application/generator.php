<?php
if (!class_exists('starfish')) { die(); }

class generator
{
	public function generate()
	{
		$list = obj('files')->tree('../../', array(
			'../../.git*',
			'../../_examples/*',
			'../../_tests/*',
			'../../helpers/*'
		));

		$this->update_db($list);

		return true;
	}

	public function update_db($list)
	{
		//print_r($list);
		$this->analyze_obj('curl.php','../../objects/curl.php');
	}

	public function is_obj($class, $file)
	{
		$class = substr($class, 0, -4);
		$content = r($file);
		if (preg_match('#class '.$class.'#i', $content, $match))
		{
			return true;
		}

		return false;
	}

	public function analyze_obj($class, $file)
	{
		$class = substr($class, 0, -4);
		$content = r($file);
		
		//Instantiate the reflection object
		$reflector = new ReflectionClass( obj($class) );

		$class = obj('parser')->getclass($reflector);
		$methods = obj('parser')->methods($reflector);
		$aliases = obj('parser')->aliases($content);
	}
}

?>