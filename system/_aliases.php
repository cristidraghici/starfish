<?php
if (!class_exists('starfish')) { die(); }

/*
 Error messages
*/
function err($target, $value, $class="") { starfish::obj('error')->err($target, $value, $class); }

?>