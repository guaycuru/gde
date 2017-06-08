<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if((isset($_POST['votar'])) && (isset($_POST['enquete']))) {
	$Enquete = Enquete::Load($_POST['enquete']);
	if($Enquete->getID() == null)
		Base::Error_JSON('Enquete não encontrada.');
	if(($Enquete->Ja_Votou($_Usuario)) || ($Enquete->getAtiva() === false))
		Base::Error_JSON('Voc&ecirc; j&aacute; votou nesta Enquete');
	elseif((($Enquete->getMax_Votos() > 1) && ((!isset($_POST['votos'])) || (!is_array($_POST['votos'])))) || (($Enquete->getMax_Votos() == 1) && ((!isset($_POST['voto'])) || ($_POST['voto'] == null))))
		Base::Error_JSON('Selecione pelo menos uma op&ccedil;&atilde;o...');
	elseif((isset($_POST['votos'])) && (count($_POST['votos']) > $Enquete->getMax_Votos()))
		Base::Error_JSON('Selecione no m&aacute;ximo '.$Enquete->getMax_Votos().(($Enquete->getMax_Votos() > 1)? ' op&ccedil;&otilde;es' : ' op&ccedil;&atilde;o').'...');
	else {
		if($Enquete->getMax_Votos() == 1) {
			$Opcao = EnqueteOpcao::Load($_POST['voto']);
			if($Opcao->getID() == null)
				Base::Error_JSON('Opção não encontrada.');
			if(($Opcao->getEnquete() === null) || ($Opcao->getEnquete()->getID()) != $Enquete->getID())
				Base::Error_JSON('Opção inválida.');
			$_Usuario->addEnquetes_Opcoes($Opcao);
		} else {
			foreach($_POST['votos'] as $id_opcao) {
				$Opcao = EnqueteOpcao::Load($id_opcao);
				if($Opcao->getID() == null)
					Base::Error_JSON('Opção não encontrada.');
				if(($Opcao->getEnquete() === null) || ($Opcao->getEnquete()->getID()) != $Enquete->getID())
					Base::Error_JSON('Opção inválida.');
				$_Usuario->addEnquetes_Opcoes($Opcao);
			}
		}
		$_Usuario->Save_JSON(true);
	}
}
