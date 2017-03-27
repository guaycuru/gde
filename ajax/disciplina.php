<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if(isset($_POST['e'])) {
	$Disciplina = Disciplina::Por_Sigla($_POST['sigla']);
	if($Disciplina->getID() == null)
		exit;
	if($_POST['e'] == 1) {
		if((!isset($_POST['a'])) || (!isset($_POST['r'])))
			exit;
		$Eliminada = new UsuarioEliminada();
		$Eliminada->setUsuario($_Usuario);
		$Eliminada->setDisciplina($Disciplina);
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
		$Eliminada = UsuarioEliminada::FindOneBy(array('usuario' => $_Usuario, 'disciplina' => $Disciplina));
		if(is_object($Eliminada)) {
			$Eliminada->Delete(true);
		}
	}
}
