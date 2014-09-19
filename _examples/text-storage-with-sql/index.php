<?php
/**
 * Starfish initial commands
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require the needed files
require_once('../../starfish.php');

/**
 * Configuration
 */
// Storage path
starfish::config('_starfish', 'storage', @realpath(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

// Add a connection to the database
starfish::config('_starfish', 'databases', array(
        'test' => array(
                'type' => 'textdb', 
                'parameters' => array(
                        'name'      => 'test',
                        'scramble'      => 'test',
                        'encrypt'       => 'test'
                )
        )
));

// Initiate Starfish
starfish::init();

/**
 * The script itself
 */

starfish::obj('database')->query("update people set nume='Cristi'");

starfish::obj('database')->query("insert into people(nume, prenume, gen) values('Ionescu".rand(1, 10)."', 'Vasile".rand(1, 10)."', 'Masculin')");

$resource = starfish::obj('database')->query("select * from people where nume='Ionescu2' and prenume='Vasile1'");
while ($row = starfish::obj('database')->fetch($resource))
{
        print_r($row);
}

$resource = starfish::obj('database')->query("select * from people");
while ($row = starfish::obj('database')->fetch($resource))
{
        print_r($row);
}

$resource = starfish::obj('database')->query("delete from people");
?>