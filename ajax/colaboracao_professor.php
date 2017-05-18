<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if(!isset($_POST['campo']) || !isset($_POST['valor']) || !isset($_POST['id_professor']))
	Base::Error_JSON('Faltando dados.');

$campo = trim($_POST['campo']);
if(ColaboracaoProfessor::Campo_Valido($campo) === false)
	Base::Error_JSON('Campo inv&aacute;lido.');

if($campo == 'instituto')
	$valor = intval($_POST['valor']);
else {
	$valor = trim($_POST['valor']);
	if($campo == 'pagina') {
		if(substr($valor, 0, 7) != 'http://' || strpos($valor, 'unicamp.br') == false)
			Base::Error_JSON("A p&aacute;gina deve come&ccedil;ar com 'http://' e deve conter 'unicamp.br'.");
	}
	else if($campo == 'email') {
		if(strpos($valor, 'unicamp.br') == false || preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $valor) == 0)
			Base::Error_JSON("O e-mail deve conter 'unicamp.br'.");
	}
	else if($campo == 'lattes') {
		if(substr($valor, 0, 55) != 'http://buscatextual.cnpq.br/buscatextual/visualizacv.do' && substr($valor, 0, 22) != 'http://lattes.cnpq.br/')
			Base::Error_JSON("O link para o curriculo Lattes deve come&ccedil;ar com 'http://buscatextual.cnpq.br/buscatextual/visualizacv.do' ou 'http://lattes.cnpq.br/'.");
	}
}

$id_professor = intval($_POST['id_professor']);
if(ColaboracaoProfessor::Existe_Colaboracao($id_professor, $campo) == true)
	Base::Error_JSON('J&aacute; existe uma colabora&ccedil;&atilde;o para este item.');

$Professor = Professor::Load($id_professor);
if($Professor->getID() == null)
	Base::Error_JSON('Professor n&atilde;o encontrado.');

$Colaboracao = new ColaboracaoProfessor();
$Colaboracao->setProfessor($Professor);
$Colaboracao->setUsuario($_Usuario);
$Colaboracao->setCampo($campo);
$Colaboracao->setValor($valor);
$Colaboracao->setStatus(ColaboracaoProfessor::STATUS_PENDENTE);
$Colaboracao->setData();
$Colaboracao->Save_JSON();
