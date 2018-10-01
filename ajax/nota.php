<?php

namespace GDE;

define('JSON', true);
require_once('../common/common.inc.php');

if($_POST['tp'] == 'n') {
	if(!empty($_POST['i'])) {
		$Nota = Nota::Load($_POST['i']);
		if($Nota->getID() == null)
			Base::Error_JSON('Nota não encontrada.');
		if($Nota->getUsuario()->getID() != $_Usuario->getID())
			Base::Error_JSON('Acesso negado.');
	} else
		$Nota = new Nota();

	if(strlen($_POST['sigla']) < 1)
		Base::Error_JSON("A sigla &eacute; muito curta!");

	if($_POST['sigla'] == "Exame")
		Base::Error_JSON("Para inserir uma nota de exame, use o campo Adicionar Exame.");

	$Nota->setUsuario($_Usuario);
	$Oferecimento = Oferecimento::Load($_POST['m']);
	if($Oferecimento->getID() == null)
		Base::Error_JSON('Oferecimento não encontrado.');
	$Nota->setOferecimento($Oferecimento);
	$Nota->setSigla($_POST['sigla']);
	$Nota->setNota(floatval(str_replace(',', '.', $_POST['nota'])));
	if($Nota->getNota() > 99999)
		Base::Error_JSON('Nota inválida.');
	$Nota->setPeso(floatval(str_replace(',', '.', $_POST['peso'])));
	if($Nota->getPeso() > 99999)
		Base::Error_JSON('Peso inválido.');
	$Nota->Save_JSON(true);
} elseif($_POST['tp'] == 'e') { // Exame
	if(!empty($_POST['i'])) {
		$Nota = Nota::Load($_POST['i']);
		if($Nota->getID() == null)
			Base::Error_JSON('Nota não encontrada.');
		if($Nota->getUsuario()->getID() != $_Usuario->getID())
			Base::Error_JSON('Acesso negado.');
	} else
		$Nota = new Nota();

	if(strlen($_POST['sigla']) < 1)
		Base::Error_JSON("A sigla &eacute; muito curta!");

	$Nota->setUsuario($_Usuario);
	$Oferecimento = Oferecimento::Load($_POST['m']);
	if($Oferecimento->getID() == null)
		Base::Error_JSON('Oferecimento não encontrado.');
	$Nota->setOferecimento($Oferecimento);
	$Nota->setSigla($_POST['sigla']);
	$Nota->setNota(floatval(str_replace(',', '.', $_POST['nota'])));
	$Nota->setPeso('1');
	$Nota->Save_JSON(true);
} elseif($_POST['tp'] == 'x') { // Excluir
	$Nota = Nota::Load($_POST['id']);
	if($Nota->getID() == null)
		Base::Error_JSON('Nota não encontrada.');
	if($Nota->getUsuario()->getID() != $_Usuario->getID())
		Base::Error_JSON('Acesso negado.');
	$Nota->Delete_JSON(true);
}
