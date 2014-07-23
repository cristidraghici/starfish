# Starfish

**Starfish PHP Framework** is a minimum Registry microframework primarily desiged to serve JSON content and use objects.

## Router examples

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

## Other software used

### As mentioned inside the starfish.php document

Parts of code or inspiration was obtained from the following software.

* [Dispatch PHP micro-framework](https://github.com/noodlehaus/dispatch)
* [Simplon Router](https://github.com/fightbulc/simplon_router)
* [Stackoverflow Answers](http://stackoverflow.com/questions/4000483/how-download-big-file-using-php-low-memory-usage)

### Bootstrap and jQuery in examples

* Bootstrap
```html
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
```

* jQuery
```html
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
```