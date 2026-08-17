<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if(!isset($_POST['a']))
	die(json_encode(false));

$Ret = false;

if($_POST['a'] == 'n') { // Nova Opcao
	$Planejado = Planejado::Criar_Opcao($_Usuario, $_POST['pp'], $_POST['pa']);
	if($Planejado === null)
		die(json_encode(false));
	$Ret = array();
	$Ret['ok'] = ($Planejado->Save(true) !== false);
	if($Ret['ok'] === true)
		$Ret['id'] = $Planejado->getID();
} elseif($_POST['a'] == 'x') { // Excluir Opcao
	$Planejado = Planejado::Load($_POST['id']);
	if($Planejado->Pertence_A($_Usuario) === false)
		die(json_encode(false));
	$Ret = array();
	$Ret['ok'] = ($Planejado->Delete(true) != false);
	$Planejados = null;
	if($Ret['ok'] === true)
		$Ret['id'] = Planejado::Algum($_Usuario, $Planejado->getPeriodo()->getID(), $Planejados, $Planejado->getPeriodo_Atual()->getID())->getID();
} else {
	if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1)) {
		$times = array('arvore1' => array(), 'arvore2' => array());
		$times['start'] = microtime(true);
		$tt = 0;
	} else
		$times = array('arvore1' => false, 'arvore2' => false);

	$cores_extras = PlanejadoExtra::getCores();

	if(empty($_POST['id']))
		die('forbidden');

	$Planejado = Planejado::Load($_POST['id']);

	if($Planejado->Pertence_A($_Usuario) === false)
		die('forbidden');

	if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
		$tt += $times['load'] = microtime(true) - $times['start'] - $tt;

	if($_POST['a'] == 'c') { // Carregar
		$c = (isset($_POST['c'])) ? intval($_POST['c']) : 0;
		$ce = (isset($_POST['ce'])) ? intval($_POST['ce']) : 0;
		$debug = ((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1));

		if(!isset($_POST['d'])) // Planejador inteiro
			$Ret = $Planejado->Montar_Dados($_Usuario, $c, $ce, (isset($_POST['pa'])) ? $_POST['pa'] : null, $debug, $times, $tt);
		else // Apenas uma disciplina
			$Ret = $Planejado->Montar_Dados_Disciplina($_Usuario, Disciplina::Load($_POST['d']), $c);

		if($Ret === null)
			die(json_encode(false));
	} elseif($_POST['a'] == 'a') { // Adicionar
		$Oferecimento = Oferecimento::Load($_POST['o']);
		if($Oferecimento->getID() == null) {
			$Ret = array('ok' => false, 'Removido' => false, 'motivo' => 'nao_encontrado');
		} else {
			$Arvore = $Planejado->Montar_Arvore($_Usuario);
			if($Arvore->getErro() === true)
				die(json_encode(false));

			$Ret = $Planejado->Adicionar_Oferecimento($Oferecimento, $Arvore, true);
			if($Ret['ok'] !== false) {
				// Preciso recalcular a Arvore para atualizar a integralizacao
				$_Usuario->Adicionar_Oferecimentos(array($Oferecimento));
				$Arvore = new Arvore($_Usuario, false, $Planejado->getPeriodo());
				if($Arvore->getErro() === true)
					die(json_encode(false));
				$Ret['Arvore'] = $Arvore->Dados(array(
					$Oferecimento->getSigla(true) => $Arvore->getTipo($Oferecimento->getSigla(), false)
				));
			}
		}
	} elseif($_POST['a'] == 'r') { // Remover
		$Oferecimento = Oferecimento::Load($_POST['o']);
		$Ret = array();
		$Ret['ok'] = ($Planejado->Remover_Oferecimento($Oferecimento) !== false);
		if($Ret['ok'] !== false) {
			$Arvore = $Planejado->Montar_Arvore($_Usuario);
			if($Arvore->getErro() === true)
				die(json_encode(false));
			$Ret['Arvore'] = $Arvore->Dados();
		}
	} elseif($_POST['a'] == 'm') { // Mudar compartilhado
		$Planejado->setCompartilhado(($_POST['v'] == 't'));
		$Ret = ($Planejado->Save(true) !== false);
	} elseif(($_POST['a'] == 's') && ($_Usuario->getAdmin())) { // Mudar simulado
		$Planejado->setSimulado(($_POST['v'] == 't'));
		$Ret = ($Planejado->Save(true) !== false);
	} elseif($_POST['a'] == 'f') { // Marcar eliminadas
		$Ret = $Planejado->Marcar_Eliminadas(
			(isset($_POST['conf'])) ? $_POST['conf'] : null,
			(isset($_POST['parciais'])) ? $_POST['parciais'] : null
		);
	} elseif($_POST['a'] == 'ae') { // Adicionar Extra
		$erros = array();
		$Extra = PlanejadoExtra::Criar($Planejado, $_POST['nome'], $_POST['dia'], $_POST['inicio'], $_POST['fim'], $erros);
		if(count($erros) > 0)
			Base::Error_JSON(implode(' ', $erros));
		elseif($Extra->Save(true) === false)
			Base::Error_JSON('Ocorreu um erro. Por favor, tente novamente.');
		else {
			$extra = array('c' => (!empty($cores_extras[intval($_POST['c'])])) ? $Extra->Evento($cores_extras[intval($_POST['c'])]) : '');
			Base::OK_JSON(null, 200, $extra);
		}
	} elseif($_POST['a'] == 're') { // Remover Extra
		$Extra = PlanejadoExtra::Load($_POST['ide']);
		if(($Extra->Pertence_A($Planejado) === false) || ($Extra->Delete(true) === false))
			Base::Error_JSON('Ocorreu um erro. Por favor, tente novamente.');
		else
			Base::OK_JSON(null);
	} elseif($_POST['a'] == 'ee') { // Editar Extra
		$Extra = PlanejadoExtra::Load($_POST['ide']);
		if($Extra->Pertence_A($Planejado) === false)
			Base::Error_JSON('Acesso negado.');
		else {
			$Extra->Mover(intval($_POST['dd']), intval($_POST['md']), ($_POST['t'] == 'd'));
			if($Extra->Save(true) === false)
				Base::Error_JSON('Ocorreu um erro. Por favor, tente novamente.');
			else
				Base::OK_JSON(null);
		}
	} elseif($_POST['a'] == 'cd') { // Carregar Dados extendidos do Oferecimento
		$Oferecimento = Oferecimento::Load($_POST['oid']);

		$Ret = array(
			'Amigos' => Planejado::Links_Amigos($Planejado->Amigos_Por_Oferecimento($Oferecimento)),
			'total' => $Planejado::Total_Por_Oferecimento($Oferecimento)
		);
	}
}

echo json_encode($Ret);
