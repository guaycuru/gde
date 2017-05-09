<?php

namespace GDE;

define('NO_LOGIN_CHECK', true);
define('JSON', true);
require_once('../common/common.inc.php');

if(isset($_POST['alterar'])) {
	$_SESSION['trocar_senha'] = true;
	exit();
}

if(isset($_POST['enviar'])) {
	list($resultado, $identificador, $tipo) = DAC::Validar_Token($_POST['token']);
	$campo = null;
	if($tipo == 'A')
		$campo = 'ra';
	elseif($tipo == 'D')
		$campo = 'matricula';
	else
		Base::Error_JSON("Tipo inv&aacute;lido!");
	if($resultado === false)
		Base::Error_JSON("TOKEN inv&aacute;lido!");
	elseif($_POST['senha'] != $_POST['conf_senha'])
		Base::Error_JSON("As senhas n&atilde;o conferem!");
	elseif(isset($_POST['trocar_senha'])) {
		$Usuario = Usuario::Por_Unique($identificador, $campo);
		if($Usuario === null)
			Base::Error_JSON("Usu&aacute;rio n&atilde;o encontrado!");
		$Usuario->setSenha($_POST['senha']);
		unset($_SESSION['trocar_senha']);
		if($Usuario->Save(true) !== false)
			Base::OK_JSON($_POST['token']);
		else
			Base::Error_JSON("Um erro ocorreu. Por favor, tente novamente mais tarde.");
	} else {
		if(Usuario::Por_Unique($identificador, $campo, true) !== null)
			Base::OK_JSON($_POST['token']);
		else {
			$Ja_tem = Usuario::Por_Unique($identificador, $campo);
			if($Ja_tem === null)
				$Usuario = new Usuario();
			else
				$Usuario = $Ja_tem;
			if($Ja_tem === null)
				$Usuario->setLogin($_POST['login']);
			if(strlen($_POST['senha']) < 3)
				Base::Error_JSON("A senha precisa ter no m&iacute;nimo 3 caracteres.");
			if((empty($_POST['email'])) || (Util::Validar_Email($_POST['email']) === false))
				Base::Error_JSON("Favor informar um email v&aacute;lido.");
			elseif(Usuario::Por_Unique($_POST['email'], 'email') !== null)
				Base::Error_JSON("J&aacute; existe um usu&aacute;rio cadastrado com o email informado.");
			$Usuario->setSenha($_POST['senha']);
			$Usuario->setEmail($_POST['email']);
			$Usuario->setNome($_POST['nome']);
			$Usuario->setSobrenome($_POST['sobrenome']);
			if(!empty($_POST['ingresso']))
				$Usuario->setIngresso(intval($_POST['ingresso']));
			if(($tipo == 'A') && (!empty($_POST['curso']))) {
				$Curso = Curso::Por_Numero($_POST['curso']);
				if($Curso === null)
					Base::Error_JSON('Curso de gradua&ccedil;&atilde;o n&atilde;o encontrado!');
				$Usuario->setCurso($Curso);
				if(!empty($_POST['catalogo']))
					$Usuario->setCatalogo(intval($_POST['catalogo']));
				if(!empty($_POST['modalidade']))
					$Modalidade = Modalidade::Por_Unique($Curso->getID(), $_POST['modalidade'], $_POST['catalogo']);
				else
					$Modalidade = null;
				$Usuario->setModalidade($Modalidade);
			}
			if($Ja_tem === null)
				$Usuario->setData_Cadastro();
			$Usuario->setAtivo(true);
			if($tipo == 'A') {
				$Aluno = Aluno::Por_RA($identificador);
				if($Aluno === null) {
					$Aluno = new Aluno();
					$Aluno->setRA($identificador);
					$Aluno->setNome($Usuario->getNome_Completo(false));
					$Aluno->setNivel((!empty($_POST['nivel'])) ? $_POST['nivel'][0] : null);
					$Aluno->setCurso($Usuario->getCurso(false));
					if($Usuario->getModalidade(false) !== null)
						$Aluno->setModalidade($Usuario->getModalidade(true)->getSigla(false));
					if(!empty($_POST['curso_pos'])) {
						$Curso_Pos = Curso::Por_Numero($_POST['curso_pos'], Curso::NIVEIS_POS);
						if($Curso_Pos === null)
							Base::Error_JSON('Curso de p&oacute;s-gradua&ccedil;&atilde;o n&atilde;o encontrado!');
						$Aluno->setCurso_Pos($Curso_Pos);
						$Aluno->setNivel_Pos((!empty($_POST['nivel_pos'])) ? $_POST['nivel_pos'][0] : null);
						$Aluno->setModalidade_Pos(($_POST['modalidade_pos'] == "") ? null : strtoupper(substr($_POST['modalidade_pos'], 0, 2)));
					}
					$Aluno->Save(false);
				}
				$Usuario->setAluno($Aluno);
			} elseif($tipo == 'D') {
				$Professor = Professor::Por_Matricula($identificador);
				if($Professor === null) {
					if(!empty($_POST['id_professor'])) {
						$Professor = Professor::Load($_POST['id_professor']);
						$Professor->setMatricula($identificador);
					} else {
						$ST = Professor::Nome_Unico($_POST['professor']);
						if($ST !== false) {
							$Professor = $ST;
							$Professor->setMatricula($identificador);
						} else {
							$Professor = new Professor();
							$Professor->setMatricula($identificador);
							$Professor->setNome($_POST['professor']);
						}
					}
					$Professor->Save(false);
				}
				$Usuario->setProfessor($Professor);
			}
			$Usuario->Enviar_Email_Validar();
			if($Usuario->Save(true) !== false)
				Base::OK_JSON($_POST['token']);
			else
				Base::Error_JSON("Um erro ocorreu. Por favor, tente novamente mais tarde.");
			//echo "$.guaycuru.confirmacao(\"Seu cadastro foi efetuado com sucesso!<br />Agora voc&ecirc; pode usar o GDE!\", \"../visoes/VisaoLogin.php?token=".$_POST['token']."\");";
		}
	}
}
