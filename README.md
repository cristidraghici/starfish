# Starfish

Starfish is a minimum Registry and Routes microframework primarily desiged to serve JSON content and use objects.

Along the main file and the system files, other objects are available to extend its functionality. These objects can be accesed from separate files or their content can be copied inside the main file.

Minimum **PHP 5.4** is required.

## Specifications

In order to use **Starfish PHP Microframework** at its best, please do take the follwing into consideration:

### General specs

Starfish main files must have the following begining:
```php
<?php
include('starfish.php');
starfish::singleton();
starfish::config(array(
	// Basic configuration
    'site_url' => 'http://'.$_SERVER['HTTP_HOST'].'/',
    'friendly' => false,
	'root'	   => './starfish/',
	'objects'  => './objects/',
	'tpl'	   => './template/',
    'debug'    => false,
	'session'  => 'FS',
	
	/*
	Other configuration variables depeding on the objects you decide to include.
	
	...
	*/
));
starfish::init();
```

### Routing usage

* Starfish main files must end like this:
```php

// Run the script
s::exec();
?>
```

* This is an example of how to define an action based on the route accessed:
```php
<?php
s::on('get', '/del/::id', function($id){
	s::redirect( './', 302,  (s::obj('login')->auth == false) );
	
	s::obj('users')->delete($id);
	
	s::redirect('./', 302);
});
?>
```

### Registry usage
Registry usage refers to directly accessing the objects inside the main file:

```php
<?php
echo s::obj('encrypt')->encode('<string to encode>');
?>
```

### Registry and routing combined

This is an example of how to use **Starfish PHP Microframework** registry and routing based combined:
```php
<?php
s::on('get', '', function(){
	s::redirect( './list', 302,  (s::obj('login')->auth == true) );
	
	echo s::obj('tpl')->view('header');
	echo s::obj('tpl')->view('index');
	echo s::obj('tpl')->view('footer');
});
?>
```

### MVC Usage
To help MVC development **Starfish PHP Microframework** implements starfish::c(), starfish::m() and starfish::v(), which are usefull wrapper functions for MVC development.

## Example applications

* Quick PostIT notes;