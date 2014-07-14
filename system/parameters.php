<?php
if (!class_exists('starfish')) { die(); }

class parameters
{
    public static function init()
    {
        
    }
}

/**
* Aliases used by class for easier programming
*/
function method()   { return call_user_func_array(array('parameters', 'method'),    func_get_args()); }
function path()     { return call_user_func_array(array('parameters', 'path'),      func_get_args()); }

function get()      { return call_user_func_array(array('parameters', 'get'),       func_get_args()); }
function post()     { return call_user_func_array(array('parameters', 'post'),      func_get_args()); }
function put()      { return call_user_func_array(array('parameters', 'put'),       func_get_args()); }
function delete()   { return call_user_func_array(array('parameters', 'delete'),    func_get_args()); }
function file()     { return call_user_func_array(array('parameters', 'file'),      func_get_args()); }
?>