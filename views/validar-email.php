<?php

namespace GDE;

define('TITULO', 'Valida&ccedil;&atilde;o do Email');

require_once("../common/common.inc.php");

if((!empty($_GET['id'])) && (!empty($_GET['token']))) {
	if($_Usuario->getID() != $_GET['id'])
		die("Voc&ecirc; est&aacute; logado como um usu&aacute;rio diferente do qual est&aacute; tentando validar o email!");
	if($_Usuario->Token_Email() == $_GET['token']) {
		$_Usuario->setEmail_Validado(true);
		$_Usuario->Save(true);
		echo "Seu email foi validado com sucesso!";
	} else {
		echo "Token ou ID inv&aacute;lido!";
	}
}
?>
<p><a href="<?= CONFIG_URL; ?>">Ir para a p&aacute;gina principal</a></p>
