<?php
if (!class_exists('starfish')) { die(); }

// Make a configuration
starfish::config('_starfish', 'app_objects', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application/objects' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'template', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'application/views' . DIRECTORY_SEPARATOR);
starfish::config('_starfish', 'storage', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

starfish::config('_starfish', 'site_url', 'http://'.$_SERVER['HTTP_HOST'].'/starfish/_examples/map-of-romania/');

starfish::config('_starfish', 'site_title', 'Map of Romania');
starfish::config('_starfish', 'site_description', 'This is a very simple map example using Leaflet.js');

?>