<?php

namespace GDE;

define('NO_HTML', true);
define('NO_CACHE', true);

require_once('../common/common.inc.php');

echo Usuario::Conta_Online(false);
