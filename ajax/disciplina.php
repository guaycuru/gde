<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if(isset($_POST['e'])) {
	$Disciplina = Disciplina::Load($_POST['id']);
	if($Disciplina->getId() == null)
		Base::Error_JSON('Disciplina não encontrada.');
	if($_POST['e'] == 1) {
		if((!isset($_POST['a'])) || (!isset($_POST['r'])))
			Base::Error_JSON('Faltando parâmetros.');

		if($_POST['a'] == 1)
			$modo = 'parcialmente';
		elseif($_POST['r'] == 1)
			$modo = 'por profici&ecirc;ncia';
		else
			$modo = 'normalmente';

		$Eliminada = UsuarioEliminada::Por_Unique($_Usuario, $Disciplina);
		if(!is_object($Eliminada))
			$Eliminada = UsuarioEliminada::Nova($_Usuario, $Disciplina);
		$Eliminada->setParcial($_POST['a'] == 1);
		$Eliminada->setProficiencia($_POST['r'] == 1);
		$Eliminada->Save_JSON(true, array('modo' => $modo));
	} else {
		$Eliminada = UsuarioEliminada::Por_Unique($_Usuario, $Disciplina);
		if(is_object($Eliminada)) {
			$Eliminada->Delete_JSON(true);
		}
	}
}
