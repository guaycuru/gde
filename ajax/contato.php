<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if((!empty($_POST['assunto'])) && (!empty($_POST['mensagem']))) {
	$dados = array(
		'id' => $_Usuario->getID(),
		'ra' => $_Usuario->getAluno(true)->getRA(false),
		'login' => $_Usuario->getLogin(false)
	);
	if(Util::Enviar_Email("gde-support@googlegroups.com", "GDE - ".$_POST['assunto'], $_POST['mensagem']."\n\n".print_r($dados, true), $_Usuario->getEmail(false)) !== false)
		Base::OK_JSON();
	else
		Base::Error_JSON('Erro: N&atilde;o foi poss&iacute;vel enviar sua mensagem. Por favor, tente novamente mais tarde.');
}
