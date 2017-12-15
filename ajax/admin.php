<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if((!isset($_SESSION['admin_su'])) &&  ($_Usuario->getAdmin() === false))
	Base::Error_JSON('Acesso negado!');

$periodo = Periodo::getAtual()->getPeriodo();

if(isset($_POST['info'])) {
	if(Dado::Atualizar($periodo) === false)
		Base::Error_JSON('Falha ao atualizar dados.');
	if($_POST['curss'] == 1) { // Cursacoes e Reprovacoes
		Base::_EM()->getConnection()->executeUpdate("UPDATE gde_disciplinas AS D JOIN (SELECT SUM(A.conta) AS conta, A.id_disciplina FROM (SELECT COUNT(*) AS conta, O.id_disciplina FROM gde_r_alunos_oferecimentos AS AO JOIN gde_oferecimentos AS O ON (O.id_oferecimento = AO.id_oferecimento) WHERE O.id_periodo < ? GROUP BY AO.ra, O.id_disciplina) AS A GROUP BY A.id_disciplina) AS S SET D.cursacoes = S.conta WHERE D.sigla = S.id_disciplina", array($periodo));
		Base::_EM()->getConnection()->executeUpdate("UPDATE gde_disciplinas AS D JOIN (SELECT SUM(A.conta) AS conta, A.id_disciplina FROM (SELECT COUNT(*)-1 AS conta, O.id_disciplina FROM gde_r_alunos_oferecimentos AS AO JOIN gde_oferecimentos AS O ON (O.id_oferecimento = AO.id_oferecimento) GROUP BY AO.ra, O.id_disciplina HAVING COUNT(*) > 1) AS A GROUP BY A.id_disciplina) AS S SET D.reprovacoes = S.conta WHERE D.sigla = S.id_disciplina");
	}
	if($_POST['ranks'] == 1)
		AvaliacaoRanking::Atualizar();
	Base::OK_JSON();
}

if(isset($_POST['su'])) {
	$SU = Usuario::Por_Login($_POST['su']);
	if($SU === null) {
		Base::Error_JSON('Esse login n&atilde;o existe!');
	} else {
		$_SESSION['admin_su'] = $SU->getID();
		Base::OK_JSON();
	}
} elseif(isset($_POST['unsu'])) {
	$_SESSION['admin_su'] = null;
	Base::OK_JSON();
}

if(isset($_POST['debug'])) {
	$_SESSION['admin']['debug'] = $_POST['debug'];
	Base::OK_JSON();
}
