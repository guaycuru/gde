<?php

namespace GDE;

define('NO_HTML', true);
define('TITULO', false);


require_once('../common/common.inc.php');


$ra = (isset($_GET['ra'])) ? intval($_GET['ra']) : -1;
$p = (isset($_GET['p'])) ? intval($_GET['p']) : null;
$n = (isset($_GET['n'])) ? $_GET['n'][0] : 'G';


?>
<html>
<head>
</head>
<body style="padding: 20px;">
</body>
<html>
