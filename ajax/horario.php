<?php

namespace GDE;

// Pra aluno, professor, etc
define('NO_CACHE', true);
define('NO_HTML', true);

require_once('../common/common.inc.php');

if(isset($_POST['ra']))
	$_tipo = 'A';
elseif(isset($_POST['sala']))
	$_tipo = 'S';
elseif(isset($_POST['professor']))
	$_tipo = 'P';
else { // Meu horario
	if($_Usuario->getAluno(false) !== null)
		$_tipo = 'A';
	elseif($_Usuario->getProfessor(false) !== null)
		$_tipo = 'P';
	else
		die('Eu sou uma sala! oO');
}

$Periodo_Selecionado = ((isset($_POST['p'])) && ($_POST['p'] != null)) ? Periodo::Load(intval($_POST['p'])) : Periodo::getAtual();
if($Periodo_Selecionado === null)
	die('Nenhum periodo selecionado!');

$Periodos = Periodo::Listar();
$periodos = "";
if($Periodos !== null) {
	foreach($Periodos as $Periodo)
		$periodos .= '<option value="' . $Periodo->getPeriodo() . '"' . (($Periodo_Selecionado->getPeriodo() == $Periodo->getPeriodo()) ? ' selected="selected"' : null) . '>' . $Periodo->getNome() . '</option>';
}

if($_tipo == 'A') {
	$Aluno = ((isset($_POST['ra'])) && ($_POST['ra'] > 0)) ? Aluno::Load($_POST['ra']) : $_Usuario->getAluno(true);
	$Usuario = ((isset($_POST['ra'])) && ($_POST['ra'] > 0)) ? Usuario::Por_RA($_POST['ra'], null, true) : $_Usuario;
	$pode_ver = $_Usuario->Pode_Ver($Usuario, 'horario');
	if($pode_ver !== true)
		die("Voc&ecirc; n&atilde;o pode ver o Hor&aacute;rio deste(a) Aluno(a) devido &agrave;s ".(($pode_ver == Usuario::NAO_PODE_VER_MEU) ? "suas" : "")." configura&ccedil;&otilde;es de compartilhamento de hor&aacute;rio".((Usuario::NAO_PODE_VER_ALHEIO) ? " dele(a)" : "")."! Para mais informa&ccedil;&otilde;es, consulte o site da DAC.");
	$tem_grad = ($Aluno->getNivel(false) != null);
	$tem_pos = ($Aluno->getNivel_Pos(false) != null);
	if(empty($_POST['n'])) {
		$nivel = (($tem_pos) && ($Aluno->getNivel_Pos(false) != Aluno::NIVEL_EGRESSADO) && (($Aluno->getNivel(false) == Aluno::NIVEL_EGRESSADO) || ($Aluno->getNivel(false) == null))) ? Aluno::NIVEL_POS : Aluno::NIVEL_GRAD;
	} else
		$nivel = $_POST['n'][0];
	$Horario = $Aluno->Monta_Horario($Periodo_Selecionado->getPeriodo(), $nivel);
	$meu = ($_Usuario->getAluno(true)->getID() == $Aluno->getID());
} elseif($_tipo == 'S') {
	$Sala = Sala::Load($_POST['sala']);
	$Horario = $Sala->Monta_Horario($Periodo_Selecionado->getPeriodo());
	$meu = false;
	$nivel = null;
	$tem_grad = $tem_pos = false;
} elseif($_tipo == 'P') {
	$Professor = ((isset($_POST['professor'])) && ($_POST['professor'] > 0)) ? Professor::Load(intval($_POST['professor'])) : $_Usuario->getProfessor(true);
	$Horario = $Professor->Monta_Horario($Periodo_Selecionado);
	$meu = (($_Usuario->getProfessor(false) !== null) && ($_Usuario->getProfessor()->getID() == $Professor->getID()));
	$nivel = null;
	$tem_grad = $tem_pos = false;
}

$limpos = Util::Horarios_Livres($Horario);
?>
<div style="padding: 10px; text-align: center;">
	<strong><?= ($meu) ? "Meu " : null; ?>Hor&aacute;rio para <select id="periodo_horario"><?= $periodos; ?></select></strong><br />
<?php if(($tem_grad) && ($tem_pos)) { ?>
	<input type="radio" name="n" value="G" id="n_G"<?php if($nivel == 'G') echo ' checked="checked"'; ?> /><label for="n_G">Gradua&ccedil;&atilde;o</label> <input type="radio" name="n" value="P" id="n_P"<?php if($nivel == 'P') echo ' checked="checked"'; ?> /><label for="n_P">P&oacute;s-Gradua&ccedil;&atilde;o</label>
<?php } elseif($_tipo == 'A') { ?>
	<strong><?= ($nivel == 'G') ? $Aluno->getNivel(true) : $Aluno->getNivel_Pos(true); ?></strong>
<?php } ?>
</div>
<table border="0" cellspacing="0" class="tabela_bonyta_branca tabela_busca">
<?php if(count($limpos) == 16) { ?>
	<tr>
		<th align="center" colspan="7"><strong>Hor&aacute;rio indispon&iacute;vel (ou vazio) para <?= $Periodo_Selecionado->getNome(); ?>!</strong></th>
	</tr>
<?php
} else {
?>
	<tr>
		<th align="center"><strong>-</strong></th>
		<th align="center"><strong>Segunda</strong></th>
		<th align="center"><strong>Ter&ccedil;a</strong></th>
		<th align="center"><strong>Quarta</strong></th>
		<th align="center"><strong>Quinta</strong></th>
		<th align="center"><strong>Sexta</strong></th>
		<th align="center"><strong>S&aacute;bado</strong></th>
	</tr>
<?php			
for($j = 7; $j < 23; $j++) {
	if(in_array($j, $limpos))
		continue;
?>
	<tr>
		<td align="center"><strong><?= $j; ?>:00</strong></td>
<?php
	for($i = 2; $i < 8; $i++) {
		$tem = isset($Horario[$i][$j]);
?>
		<td align="center"><?= isset($_POST['sala']) ? $_Usuario->Formata_Horario_Sala($Horario, $i, $j) : $_Usuario->Formata_Horario($Horario, $i, $j, $meu, $Periodo_Selecionado, true); ?></td>
<?php } ?>
	</tr>
<?php
	}
}
if($_tipo == 'A') {
?>
	<tr>
		<td colspan="2" style="padding: 10px 5px;"><strong>Matr&iacute;culas (<?=$Aluno->Creditos_Atuais($Periodo_Selecionado->getPeriodo(), $nivel); ?>):</strong></td>
		<td colspan="5" style="padding: 10px 5px;"><?=$Aluno->getOferecimentos($Periodo_Selecionado->getPeriodo(), $nivel, true); ?></td>
	</tr>
	<tr>
		<td colspan="2" style="padding: 10px 5px;"><strong>Desist&ecirc;ncias (<?=$Aluno->Creditos_Trancados($Periodo_Selecionado->getPeriodo(), $nivel); ?>):</strong></td>
		<td colspan="5" style="padding: 10px 5px;"><?=$Aluno->getTrancados($Periodo_Selecionado->getPeriodo(), $nivel, true); ?></td>
	</tr>
	<tr>
		<td colspan="7" align="center" style="padding: 10px;"><a href="#" onclick="window.open('<?= CONFIG_URL; ?>imprimir-horario/?ra=<?= $Aluno->getRA(true); ?>&p=<?= $Periodo_Selecionado->getID(true); ?>&n=<?= $nivel; ?>', '_blank', 'width=700, height=550, scrollbars=yes'); return false;">Visualizar Para Impress&atilde;o</a></td>
	</tr>
<?php } if($_tipo == 'P') {
?>
	<tr>
		<td colspan="2" style="padding: 10px 5px;"><strong>Oferecimentos:</strong></td>
		<td colspan="5" style="padding: 10px 5px;"><?= $Professor->getOferecimentos($Periodo_Selecionado, $nivel, true); ?></td>
	</tr>
<?php } ?>
</table>
