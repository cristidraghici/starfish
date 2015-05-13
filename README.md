# Starfish ![Starfish PHP Framework](/storage/starfish-small.png "Starfish PHP Framework")

**Starfish PHP Framework** is a minimum Registry microframework primarily desiged to serve JSON content and use objects.

## Router examples

* .htaccess file content example:

```apache
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /starfish/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?=$1 [QSA,L]

ErrorDocument 404 " "
</IfModule>
```

* This is an example of how to define an action based on the route accessed:

```php
<?php
/**
* Starfish initial commands
*/
// Require the needed files
require_once('starfish.php');

// Initiate Starfish
starfish::init();

/**
* The script itself
*/
// With parameter
starfish::obj('routes')->on('get', '/:alpha', function($param) {
echo 'With param: ' . $param;
});

// The default path
starfish::obj('routes')->on('get', '/:all', function() {
echo 'Stuff is working well!';
});

// Execute the router
starfish::obj('routes')->run();
?>
```

* Here is a smaller version, using helper functions:

```php
<?php
/** Starfish initial commands */

// Require the needed files
require_once('starfish.php');
// Initiate Starfish
init();

/** The script itself */

// With parameter
on('get', '/:alpha', function($param) {
echo 'With param: ' .$param;
});

// The default path
on('get', '/:all', function() {
echo 'Stuff is working well!';
});

// Execute the router
on();
?>
```

## Recommendations

* In development, enable php debugging before including starfish:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```


## Databases

* Mysql connection configuration:

```php
// Add a connection to the database
starfish::config('_starfish', 'databases', array(
'name' => array(
'type' => 'mysql', 
'parameters' => array(
'host' => 'localhost',
'user' => 'root',
'pass' => '',
'name' => 'name',
)
)
));
```

* One example query:
```php
<?php
$resource = starfish::obj('database')->query('select * from `table`');
while ( $row = starfish::obj('database')->fetch($resource) )
{
print_r($row);
}

starfish::obj('database')->free( $resource );
?>
```
