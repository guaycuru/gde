<?php

namespace GDE;

define('HTML', false);
require_once('../common/common.inc.php');

if($_Usuario->getAdmin() === false)
	die("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina!");

$dir = __DIR__.'/../errors/';
foreach(array_diff(scandir($dir), array('..', '.', '.htaccess')) as $erro) {
	echo '<h2>'.$erro.' - '.date("d/m/Y H:i:s.", filemtime($dir.$erro)).'</h2>';
	echo '<pre>'.file_get_contents($dir.$erro).'</pre>';
	echo '<hr>';
}
