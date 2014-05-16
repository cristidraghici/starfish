<?php
if (!isset($path)) { die(); }

trait mvc
{
	/** Model/View/Controller Applications */
	public static function c($name)
	{
		return starfish::obj('controller-'.$name);
	}
	public static function m($name)
	{
		return starfish::obj('model-'.$name);
	}
	public static function v($name, $data=array())
	{
		return starfish::obj('tpl')->view($name, $data);
	}
}

?>