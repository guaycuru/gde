<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');
if(!isset($_POST['tp'])) {
	
	$Periodo_Selecionado = ((isset($_POST['p'])) && ($_POST['p'] != null)) ? Periodo::Load(intval($_POST['p'])) : Periodo::getAtual();

	$Periodos = Periodo::Listar();
	$periodos = "";
	foreach($Periodos as $Periodo)
		$periodos .= '<option value="'.$Periodo->getID().'"'.(($Periodo_Selecionado->getPeriodo() == $Periodo->getPeriodo())?' selected="selected"':null).'>'.$Periodo->getNome(false).'</option>';

	$Disciplina = Disciplina::Por_Sigla($_POST['sigla'], 0, 5);
	if($Disciplina === null)
		exit;

	$parametros['periodo'] = $Periodo_Selecionado->getPeriodo();
	$parametros['sigla'] = $Disciplina->getSigla();
	$total = 0;
	$Oferecimentos = Oferecimento::Consultar($parametros, "O.turma ASC", $total);

} else {
	$Oferecimentos = Oferecimento::Consultar(array("sigla" => urldecode(str_replace("_", " ", $_POST['q'])), "periodo" => intval($_POST['p'])));

	if($_POST['tp'] == 3)
		echo "<option value=\"*\">?</option>";

	foreach($Oferecimentos as $Oferecimento) {
		if($_POST['tp'] == 1)
			echo $Oferecimento->getSigla()."\n";
		elseif($_POST['tp'] == 2)
			echo $Oferecimento->getSigla()." ".$Oferecimento->getTurma(true)."\n";
		else
			echo "<option value=\"".$Oferecimento->getTurma()."\"".((isset($_POST['s']) && $_POST['s'] == $Oferecimento->getTurma(true))?" selected=\"selected\"":null).">".$Oferecimento->getTurma(true)."</option>";
	}
}
?>
<?php if(!isset($_POST['tp'])) { ?>
<table border="0" cellspacing="0" class="tabela_bonyta_branca">
	<tr>
		<td colspan="7" align="center" style="padding: 10px;"><strong>Oferecimentos Para <select id="periodo_horario"><?= $periodos; ?></select></strong></td>
	</tr>
<?php if($total == 0) { ?>
	<tr>
		<td align="center" colspan="7"><strong>Nenhum oferecimento cadastrado para <?= $Periodo_Selecionado->getNome(true); ?>!</strong></td>
	</tr>
<?php } else { ?>
	<table border='1' width='95%'><tr><td align='center'><strong>Sigla e Turma</strong></td><td align='center'><strong>Nome</strong></td><td align='center'><strong>Professor</strong></td><td align='center' width='5%'><strong>Vagas</strong></td><td align='center' width='5%'><strong>Alunos</strong></td><td align='center' width='10%'><strong>Situa&ccedil;&atilde;o</strong></td></tr>
<?php
	foreach($Oferecimentos as $Oferecimento) {
		$vagas = $Oferecimento->getVagas();
		$matriculados = $Oferecimento->Matriculados();
		if($Oferecimento->getFechada())
			$situacao = "Fechada";
		elseif($matriculados >= $vagas)
			$situacao = "Lotada";
		else
		$situacao = "";
?>
		<tr>
			<td><a href="<?= CONFIG_URL; ?>oferecimento/<?= $Oferecimento->getID(); ?>"><?= $Oferecimento->getSigla(true)." ".$Oferecimento->getTurma(true); ?></a></td>
			<td><a href="<?= CONFIG_URL; ?>oferecimento/<?= $Oferecimento->getID(); ?>"><?= $Oferecimento->getDisciplina(true)->getNome(true); ?></a></td>
			<td><?= ($Oferecimento->getProfessor() != null) ? '<a href="'.CONFIG_URL.'perfil/?p='.$Oferecimento->getProfessor(true)->getID().'">'.$Oferecimento->getProfessor(true)->getNome(true).'</a>' : 'Desconhecido'; ?></td>
			<td><?= $vagas; ?></td>
			<td><?= $matriculados; ?></td>
			<td><?= $situacao; ?></td>
		</tr>
<?php } ?>
	</table>
<?php
	if((isset($_GET['r'])) || ($Disciplina->getCreditos() == -1)) {
		die($FIM);
	}
}
}
