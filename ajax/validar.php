<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

// ToDo: Testar
$_Usuario->Enviar_Email_Validar();
$_SESSION['validaEmail'] = true;
