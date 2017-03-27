<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if(isset($_POST['idp'])) {
	$Pergunta = AvaliacaoPergunta::Load($_POST['idp']);
	$Professor = (!empty($_POST['professor'])) ? Professor::Load($_POST['professor']) : null;
	$Disciplina = (!empty($_POST['sigla'])) ? Disciplina::Por_Sigla($_POST['sigla']) : null;
	if(($Pergunta->Pode_Votar($_Usuario, $Professor, $Disciplina) !== true) || ($_POST['nota'] > 5) || ($_POST['nota'] < 1))
		die('0');
	$Resposta = new AvaliacaoResposta();
	$Resposta->setPergunta($Pergunta);
	$Resposta->setUsuario($_Usuario);
	if($Professor !== null)
		$Resposta->setProfessor($Professor);
	if($Disciplina !== null)
		$Resposta->setDisciplina($Disciplina);
	$Resposta->setResposta(intval($_POST['nota']));
	$Resposta->setData();
	echo ($Resposta->Save(true) !== false) ? '1' : '0';
}
