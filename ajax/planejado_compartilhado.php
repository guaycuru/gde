<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$Planejado = Planejado::Load(intval($_POST['id']));
if($Planejado->getID() == null)
	exit();

if(($_Usuario->getAdmin() === false) && ($_Usuario->getID() != $Planejado->getUsuario()->getID()) && (
	($Planejado->getUsuario(true)->Amigo($_Usuario) === false) || ($Planejado->getCompartilhado() === false))
) {
	exit();
}

$Horario = $Planejado->Monta_Horario();
$limpos = Util::Horarios_Livres($Horario);

$creditos = 0;
$matriculas = array();
foreach($Planejado->getOferecimentos() as $Oferecimento) {
	$creditos += $Oferecimento->getDisciplina(true)->getCreditos(false);
	$matriculas[] = $Oferecimento->getDisciplina(true)->getSigla(true).$Oferecimento->getTurma(true)." (".$Oferecimento->getDisciplina(true)->getCreditos(true).")";
}

if(isset($_POST['p']))
	echo "<h2 align=\"center\">GDE - Planejamento de Hor&aacute;rio</h2>";

?>
<table border="0" cellspacing="0" class="<?= (isset($_POST['p'])) ? "tabela_bonyta" : "tabela_bonyta_branca"; ?>">
	<tr>
		<td width="20%"><b>Per&iacute;odo:</b></td>
		<td><?= $Planejado->getPeriodo(true)->getNome(true); ?></td>
	</tr>
	<tr>
		<td width="20%"><b>Cr&eacute;ditos:</b></td>
		<td><?= intval($creditos); ?></td>
	</tr>
	<tr>
		<td width="20%"><b>Matr&iacute;culas:</b></td>
		<td><?= implode(", ", $matriculas); ?></td>
	</tr>
</table>
<table border="0" class="<?= (isset($_POST['p'])) ? "tabela_bonyta" : "tabela_bonyta_branca"; ?>">
	<tr>
		<td align="center"><b>-</b></td>
		<td align="center"><b>Segunda</b></td>
		<td align="center"><b>Ter&ccedil;a</b></td>
		<td align="center"><b>Quarta</b></td>
		<td align="center"><b>Quinta</b></td>
		<td align="center"><b>Sexta</b></td>
		<td align="center"><b>S&aacute;bado</b></td>
	</tr>
<?php

if((isset($_POST['full'])) || (count($limpos) < 16)) {
	for($j = 7; $j < 23; $j++) {
		if((!isset($_POST['full'])) && (in_array($j, $limpos)))
			continue;
		echo "<tr><td align=\"center\"><b>".$j.":00</b></td>";
		for($i = 2; $i < 8; $i++) {
			echo "<td align=\"center\">".$_Usuario->Formata_Horario($Horario, $i, $j, false)."</td>";
		}
		echo "</tr>";
	}
}

?>
</table>
<?php
if(isset($_POST['p']))
	echo "<br /><a href=\"#\" onclick=\"window.print(); return false;\">Imprimir</a>";
