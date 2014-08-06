<?php
if (!class_exists('starfish')) { die(); }

/**
 * Mysql connection class
 *
 * @package starfish
 * @subpackage starfish.objects.mysql
 */
class mysql implements Iterator
{
    private $connection = null;
    private $resource = null;
    private $result = null;
    private $index = 0;
    
    function connect($parameters) { return true; }
    function disconnect()   { return true; }
    function sanitize()     { return true; }
    function escape()       { return true; }
    function query()        { return true; }
    function fetch()        { return true; }
    function free()         { return true; }
}
?>