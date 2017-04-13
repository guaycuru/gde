<?php

namespace GDE;

define('JSON', true);

set_time_limit(0);

require_once('../common/common.inc.php');

if(isset($_POST['salvar'])) {
	$erro = false;
	if($_FILES['foto']['size'] > 0) {
		if($_Usuario->Enviar_Foto($_FILES['foto']) === false) {
			Base::Error_JSON('A Foto deve ser JPG, GIF ou PNG de no m&aacute;ximo 5 MB!');
		}
	} elseif((isset($_POST['excluir_foto'])) && ($_POST['excluir_foto'] == 't'))
		$_Usuario->setFoto(null);
	if(($_POST['senha'] != null) && ($_POST['senha'] != $_POST['conf_senha'])) {
		Base::Error_JSON('As senhas n&atilde;o conferem!');
	}
	if($_Usuario->getEmail() != $_POST['email'])
		$_Usuario->setEmail_Validado(false);
	$_Usuario->setEmail($_POST['email']);
	if($_POST['senha'] != null)
		$_Usuario->setSenha($_POST['senha']);
	$_Usuario->setNome($_POST['nome']);
	$_Usuario->setSobrenome($_POST['sobrenome']);
	$Curso = Curso::Por_Numero($_POST['curso']);
	if($Curso === null)
		Base::Error_JSON('Curso n&atilde;o encontrado!');
	$_Usuario->setCurso($Curso);
	$_Usuario->setCatalogo(intval($_POST['catalogo']));
	if(!empty($_POST['modalidade']))
		$Modalidade = Modalidade::Por_Curso_Sigla_Catalogo($Curso->getNumero(false), $_POST['modalidade'], array('G', 'T'), $_POST['catalogo']);
	else
		$Modalidade = null;
	$_Usuario->setModalidade($Modalidade);
	$_Usuario->setIngresso(intval($_POST['ingresso']));
	$_Usuario->setSexo($_POST['sexo'][0]);
	$data_nascimento = ($_POST['data_nascimento'] != "") ? implode('-', array_reverse(explode('/', $_POST['data_nascimento']))) : null;
	$_Usuario->setData_Nascimento($data_nascimento);
	$_Usuario->setApelido($_POST['apelido']);
	$_Usuario->setCidade($_POST['cidade']);
	$_Usuario->setEstado($_POST['estado']);
	$_Usuario->setEstado_Civil($_POST['estado_civil'][0]);
	$_Usuario->setBlog((($_POST['blog'] != "") && ($_POST['blog'] != "http://")) ? $_POST['blog'] : null);
	$_Usuario->setFacebook((($_POST['facebook'] != "") && ($_POST['facebook'] != "http://")) ? $_POST['facebook'] : null);
	$_Usuario->setTwitter(str_replace('@', null, $_POST['twitter']));
	$_Usuario->setMais($_POST['mais']);
	if(isset($_POST['compartilha_arvore']))
		$_Usuario->setCompartilha_Arvore($_POST['compartilha_arvore']);
	if(isset($_POST['compartilha_horario']))
		$_Usuario->setCompartilha_Horario($_POST['compartilha_horario']);
	if($_POST['procurando_emprego'] == '-')
		$_Usuario->setProcurando_Emprego(null);
	else
		$_Usuario->setProcurando_Emprego($_POST['procurando_emprego']);
	$_Usuario->setExp_Profissionais($_POST['exp_profissionais']);
	$_Usuario->setHab_Pessoais($_POST['hab_pessoais']);
	$_Usuario->setEsp_Tecnicas($_POST['esp_tecnicas']);
	$_Usuario->setInfo_Profissional($_POST['info_profissional']);

	/*$empregos_atual = $_Usuario->getEmpregos();
	for($i = 0; $i < $_POST['num_empregos']; $i++){
		$id_emprego = null;
		if(isset($empregos_atual[$i]))
			$id_emprego = $empregos_atual[$i]->getID();
		if(!empty($id_emprego)) {
			$emprego = UsuarioEmprego::Load($id_emprego);
			if(isset($_POST['emprego_remover_'.$i]))
				$emprego->setExcluir(false);
		} elseif(!isset($_POST['emprego_remover_'.$i])) {
			$emprego = new UsuarioEmprego();
			if((isset($_POST['emprego_'.$i]))&&($_POST['emprego_'.$i] != "Nome")){
				$emprego->setNome($_POST['emprego_'.$i]);
			}
			if(isset($_POST['emprego_inicio_'.$i]) && ($_POST['emprego_inicio_'.$i] != "Data Inicio")){
				$emprego->setInicio(implode('-', array_reverse(explode('/', $_POST['emprego_inicio_'.$i]))));
			}
			if(isset($_POST['emprego_fim_'.$i]) && ($_POST['emprego_fim_'.$i] != "Data Fim")){
				$emprego->setFim(implode('-', array_reverse(explode('/', $_POST['emprego_fim_'.$i]))));
			}
			if(isset($_POST['emprego_tipo_'.$i]))
				$emprego->setTipo($_POST['emprego_tipo_'.$i]);
			$emprego->setUsuario($_Usuario);
			if(isset($_POST['emprego_atual_'.$i]))
				$emprego->setAtual('t');
			else
				$emprego->setAtual('f');
			if(isset($_POST['emprego_cargo_'.$i])){
				$emprego->setCargo($_POST['emprego_cargo_'.$i]);
			}
			if(isset($_POST['emprego_site_'.$i])){
				$emprego->setSite($_POST['emprego_site_'.$i]);
			}
			if(($emprego->getNome(false) != null) && ($emprego->getInicio(false) !== null))
				$_Usuario->addEmpregos($emprego);
		}
	}*/

	if($erro === false) {
		$_Usuario->Save_JSON(true);
	} elseif($erro === false && (count(empregos_erros) != 0)) {
		$erros = '';
		foreach($_Usuario->getErros() as $erro)
			$erros .= $erro."<br />";
		Base::Error_JSON('Os seguintes erros foram encontrados, favor corrig&iacute;-los:'.$erros);
	}
}
