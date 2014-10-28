<?php
if (!class_exists('starfish')) { die(); }

class authentication
{
	public function init()
	{
		return true;
	}
	
	public function routes()
	{
                // With parameter
                on('get', '/:alpha', function($param) {
                        echo 'With param: ' . starfish::obj('scramble')->decode( starfish::obj('scramble')->encode($param) );
                });

                // The default path
                on('get', '/:all', function() {
                        echo 'Stuff is working well!';
                });
		return true;
	}
}
?>