<?php

define('JUST_INC', true);

require_once('../common/common.inc.php');
require_once('../classes/Recomendacao.inc.php');

echo "<script>$(document).ready( function(){";

if(isset($_POST['enviar'])) {
	if(Envia_Email("gde-support@googlegroups.com", "GDE - ".$_POST['assunto'], $_POST['mensagem']."\n\n".print_r($_Usuario, true), $_Usuario->getEmail()) !== false)
		echo "$.guaycuru.confirmacao(\"Sua Mensagem foi enviada com sucesso! O GDE agradece!!!\", \"".CONFIG_URL."index\")";
	else
		echo "$.guaycuru.confirmacao(\"N&atilde;o foi poss&iacute;vel enviar a Mensagem...\")";
}

echo "});</script>";

echo $FIM;
