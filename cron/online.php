<?php

namespace GDE;

define('NO_HTML', true);
define('NO_LOGIN_CHECK', true);

require_once('../common/config.inc.php');

file_put_contents(__DIR__.'/../cache/online.txt', Usuario::Conta_Online(true));
