<?php

namespace GDE;

define('NO_LOGIN_CHECK', true);
define('JSON', true);
require_once('../common/common.inc.php');

if((!isset($_POST['logout'])) && (!isset($_POST['login'])) && (!isset($_POST['token'])))
	Base::Error_JSON('Faltando acao.');

$erro = $return = null;
if(!empty($_POST['token'])) {
	$_Usuario = Usuario::Efetuar_Login_DAC($_POST['token'], $erro);
} elseif(isset($_POST['logout'])) {
	Usuario::Logout();
	die(Base::To_JSON(array('ok' => true)));
} elseif((empty($_POST['login'])) || (empty($_POST['senha'])))
	Base::Error_JSON('Faltando login ou senha.');
else {
	if(!empty($_SESSION['last_pg']))
		$return = $_SESSION['last_pg'];
	$_Usuario = Usuario::Verificar_Login($_POST['login'], $_POST['senha'], ((!empty($_POST['lembrar'])) && ($_POST['lembrar'] == 't')), $erro);
}

if((is_object($_Usuario)) && ($_Usuario->getID() != null)) { // Login OK
	Base::OK_JSON(null, 200, array('destino' => $return));
} else { // Login falhou
	$extra = array('destino' => '');
	switch($erro) {
		case Usuario::ERRO_LOGIN_NAO_ENCONTRADO:
			if(!empty($_POST['token'])) {
				$erro = 'Login não encontrado. Por favor, efetue seu cadastro.';
				$extra['destino'] = CONFIG_URL . 'cadastro/?token=' . urlencode($_POST['token']);
			} else {
				$erro = 'Login não encontrado.';
			}
			break;
		case Usuario::ERRO_LOGIN_SENHA_INCORRETA:
			$erro = 'Login ou senha incorretos.';
			break;
		case Usuario::ERRO_LOGIN_USUARIO_INATIVO:
			$erro = 'Usuário inativo.';
			break;
		case Usuario::ERRO_LOGIN_TOKEN_INVALIDO:
			$erro = 'Token inválido.';
			break;
		default:
			$erro = 'Erro desconhecido.';
			break;
	}
	Base::Error_JSON($erro, 200, $extra);
}
