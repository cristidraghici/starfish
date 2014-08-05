<?php
if (!class_exists('starfish')) { die(); }

// Make a configuration
starfish::config('_starfish', 'app_objects', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application');
starfish::config('_starfish', 'storage', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'storage');

// Add a connection to the database
starfish::obj('database')->add('postit', 'textdb', array(
        'name'=>'postit',
        'hash'=>'postit'
));

?>