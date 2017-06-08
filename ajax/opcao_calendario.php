<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

$_SESSION['calendario'][$_POST['c']] = ($_POST['v'] == 1);

echo Base::To_JSON(array(
	'ok' => true
));
