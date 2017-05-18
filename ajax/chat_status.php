<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if(isset($_POST['set_chat_status'])) {
	$_Usuario->setChat_Status($_POST['set_chat_status'][0]);
	$_Usuario->Save_Json(true);
}
