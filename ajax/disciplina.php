<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if(isset($_POST['e'])) {
	$Disciplina = Disciplina::Load($_POST['id']);
	if($Disciplina->getId() == null)
		exit;
	if($_POST['e'] == 1) {
		if((!isset($_POST['a'])) || (!isset($_POST['r'])))
			exit;
		$Eliminada = UsuarioEliminada::Por_Unique($_Usuario, $Disciplina);
		if(!is_object($Eliminada)) {
			$Eliminada = new UsuarioEliminada();
			$Eliminada->setUsuario($_Usuario);
			$Eliminada->setDisciplina($Disciplina);
		}
		$Eliminada->setParcial($_POST['a'] == 1);
		$Eliminada->setProficiencia($_POST['r'] == 1);
		if($Eliminada->Save(true) === false)
			exit;
		if($_POST['a'] == 1)
			echo 'parcialmente';
		elseif($_POST['r'] == 1)
			echo 'por profici&ecirc;ncia';
		else
			echo 'normalmente';
	} else {
		$Eliminada = UsuarioEliminada::Por_Unique($_Usuario, $Disciplina);
		if(is_object($Eliminada)) {
			$Eliminada->Delete(true);
		}
	}
}
