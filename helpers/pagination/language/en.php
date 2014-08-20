<?php
if (!class_exists('starfish')) { die(); }

/**
 * Use the configuration function to store language variables
 */
starfish::config('_helpers-pagination-en', array('first_page', 'last_page'), array('First page', 'Last page'));

?>