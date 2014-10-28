<?php
if (!class_exists('starfish')) { die(); }

// Make a configuration
starfish::config('_starfish', 'app_objects', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application/objects' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'template', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application/views' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'storage', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'storage');

// Add a connection to the database
starfish::config('_starfish', 'databases', array(
        'postit' => array(
                'type' => 'textdb', 
                'parameters' => array(
                        'name'      => 'postit',
                        'scramble'      => 'post-it',
                        'encrypt'       => 'post-it'
                )
        )
));

?>