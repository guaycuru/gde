<?php

namespace GDE;

define('NO_HTML', true);
define('NO_CACHE', true);
define('NO_LOGIN_CHECK', true);

require_once('../common/common.inc.php');

try {
	Base::_EM()->getConnection()->executeQuery('SELECT 1');
} catch (\Throwable $e) {
	http_response_code(503);
	exit;
}

echo 'OK';
