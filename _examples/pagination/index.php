<?php
/**
 * Starfish initial commands
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require the needed files
require_once('../../starfish.php');

// Initiate Starfish
starfish::init();

/**
 * The script itself
 */
$page = get('page');
$total = 101;
$rows = 10;

echo starfish::obj('pagination')->nav($total, $rows, $page, 'index.php?page={page}', 2);

?>