<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if(!isset($_POST['campo']) || !isset($_POST['valor']) || !isset($_POST['id_oferecimento']))
	Base::Error_JSON('Faltando dados.');

$campo = trim($_POST['campo']);
if(ColaboracaoOferecimento::Campo_Valido($campo) === false)
	Base::Error_JSON('Campo inv&aacute;lido.');

$valor = trim($_POST['valor']);
if($campo == 'pagina') {
	if(substr($valor, 0, 7) != 'http://' || strpos($valor, 'unicamp.br') == false)
		Base::Error_JSON("A p&aacute;gina deve come&ccedil;ar com 'http://' e deve conter 'unicamp.br'.");
}

$id_oferecimento = intval($_POST['id_oferecimento']);
if(ColaboracaoOferecimento::Existe_Colaboracao($id_oferecimento, $campo) == true)
	Base::Error_JSON('J&aacute; existe uma colabora&ccedil;&atilde;o para este item.');

$Oferecimento = Oferecimento::Load($id_oferecimento);
if($Oferecimento->getID() == null)
	Base::Error_JSON('Oferecimento n&atilde;o encontrado.');

$Colaboracao = new ColaboracaoOferecimento();
$Colaboracao->setOferecimento($Oferecimento);
$Colaboracao->setUsuario($_Usuario);
$Colaboracao->setCampo($campo);
$Colaboracao->setValor($valor);
$Colaboracao->setStatus(ColaboracaoOferecimento::STATUS_PENDENTE);
$Colaboracao->setData();
$Colaboracao->Save_JSON();
