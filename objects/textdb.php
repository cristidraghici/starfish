<?php
if (!class_exists('starfish')) { die(); }

/**
 * Mysql connection class
 *
 * @package starfish
 * @subpackage starfish.objects.mysql
 */
class textdb
{	
        /**
	 * Init the object
	 */
        public static function init()
        {
        }

        function connect($config)      
        {
                return true; 
        }
        function disconnect()   { return true; }
        function sanitize()     { return true; }
        function escape()       { return true; }
        function query()        { return true; }
        function info()         { return true; }
        function fetch()        { return true; }
        function free()         { return true; }
		
		
public function current()
{
return $this->result[$this->index];
}

public function next()
{
$this->index ++;
}

public function key()
{
return $this->index;
}

public function valid()
{
return isset($this->result[$this->key()]);
}

public function rewind()
{
$this->index = 0;
}

public function reverse()
{
$this->result = array_reverse($this->result);
$this->rewind();
}
}
?>