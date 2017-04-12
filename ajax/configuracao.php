<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$Usuario_Config = $_Usuario->getConfig(true);
$Usuario_Config->setAvisos_Aniversario($_POST['tipoA']);
echo ($Usuario_Config->Save(true) !== false) ? '1' : '0';
