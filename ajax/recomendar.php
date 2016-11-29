<?php

define('JUST_INC', true);

require_once('../common/common.inc.php');

echo "<script>$(document).ready( function(){";

if(isset($_POST['enviar'])) {
	$Rec = new Recomendacao();
	$Rec->setChave();
	$Rec->setLogin($_Usuario->getLogin(true));
	$Rec->setEmail($_POST['email']);
	$Rec->setRA(intval($_POST['ra']));
	$verificar = $Rec->Verificar();
	if($verificar !== true) {
		echo "$.guaycuru.confirmacao(\"Os seguintes erros foram encontrados, favor corrig&iacute;-los:<br />";
		foreach($verificar as $erro) {
			echo $erro."<br />";
		}
		echo "\");";
	} else {
		if($Rec->Recomendar($_Usuario, $_POST['mensagem']) === true)
			echo "$.guaycuru.confirmacao(\"Sua Recomenda&ccedil;&atilde;o foi enviada com sucesso! O GDE agradece!!!\", \"".CONFIG_URL."/index/\")";
		else
			echo "$.guaycuru.confirmacao(\"N&atilde;o foi poss&iacute;vel enviar a Recomenda&ccedil;&atilde;o.\")";
	}
}

echo "});</script>";

echo $FIM;
