<?php
if (!class_exists('starfish')) { die(); }

class process
{
	function readContent()
	{
		$upload = obj('files')->upload('file');
		$content = r($upload['tmp_name']);
		
		return $content;
	}
	
	function preparePattern($pattern)
	{
		// Clean the pattern
		$array = array(
			'$'=>'\$',
			"'"=>"\\'",
			'['=>'\[',
			']'=>'\]'
		);
		$pattern = str_replace(array_keys($array), array_values($array), $pattern);
		
		// Create the pattern
		$pattern = '#'.str_replace(array('{T}', '{A}'), '(.*)', $pattern) . '#i';
		
		return $pattern;
	}
	
	function parseContent($text, $pattern)
	{
		$pattern = $this->preparePattern($pattern);
		
		preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
		
		return $matches;
	}
	
	//--> update: translation should match position
	function translateContent($lines, $pattern) 
	{
		$positions[0] = false;
		preg_match_all('#\{(A|T){1}\}#i', $pattern, $matches, PREG_SET_ORDER);
		foreach ($matches as $key=>$value)
		{
			$positions[($key+1)] = ($value[1] == 'T') ? true : false;
		}
		
		$list = array();
		
		foreach ($lines as $key=>$value)
		{
			$translate = array();
			for ($a=0; isset($value[$a]); $a++) 
			{
				if ($positions[$a] == true) { $translate[ $lines[$key][$a] ] = $this->translateString( $lines[$key][$a] ) ; }
			}
			
			$list[$value[0]] = str_replace(array_keys($translate), array_values($translate), $value[0]);
		}
		
		return $list;
	}
	
	function translateString($string)
	{
		return ucfirst(obj('googletranslate')->translate($string, 'en', 'ro'));
	}
	
	function updateContent($text, $values)
	{
		foreach ($values as $key=>$value)
		{
			$text = str_replace($key, $value, $text);
		}
		
		return $text;
	}
	
	function translate()
	{
		$text = $this->readContent();
		$pattern = $_POST['pattern'];
		
		$lines = $this->parseContent($text, $pattern);
		$values = $this->translateContent($lines, $pattern);
		
		return @htmlentities( $this->updateContent($text, $values) );
	}
}
?>