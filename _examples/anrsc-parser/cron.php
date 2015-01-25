<?php
/**
 * Starfish initial commands
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);


/* Set internal character encoding to UTF-8 */
#mb_internal_encoding("UTF-8");
#mb_http_output( "UTF-8" );
//ob_start("mb_output_handler");

// Require the needed files
require_once('../../starfish.php');

// Initiate Starfish
starfish::init();
$curl = starfish::obj('curl');
$html = starfish::obj('html');

/**
 * The script itself
 */
set_time_limit(0);

/*

download::add(1, 'http://www.anrsc.ro/index.php?option=com_content&view=article&id=176%3Asablon-judet&catid=53&ml=1&Itemid=1', array());

mysql::q("insert into rezultate(`url`, `judet`, `oras`, `siruta`, `cif`, `oras_text`, `salubrizare`, `salubrizare_text`, `iluminat`, `iluminat_text`, `transport`, `transport_text`, `termic`, `termic_text`, `apa`, `apa_text`) values('".$key."', '".$stored[2]."', '".$data['oras']."', '".$data['siruta']."', '".$data['cif']."', '".$data['oras_text']."', '".$data['salubrizare']."', '".$data['salubrizare_text']."', '".$data['iluminat']."', '".$data['iluminat_text']."', '".$data['transport']."', '".$data['transport_text']."', '".$data['termic']."', '".$data['termic_text']."', '".$data['apa']."', '".$data['apa_text']."')");
*/

starfish::obj('scraper')->message('Finished :)');
?>