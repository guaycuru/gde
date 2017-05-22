<?php

namespace GDE;

// Pra aluno, professor, etc
define('NO_CACHE', true);
define('NO_HTML', true);

require_once('../common/common.inc.php');

$ra = intval($_GET['ra']);
$p = (isset($_GET['p'])) ? intval($_GET['p']) : null;
$n = (isset($_GET['n'])) ? $_GET['n'][0] : 'G';

?>
<html>
<head>
</head>
<body>
<style>
	* { color: #000000; }
	table.tabela_bonyta {	border-top: 1px solid #000000;	border-left: 1px solid #000000;	width: 100%;}
	table.tabela_bonyta td {	border-right: 1px solid #000000;	border-bottom: 1px solid #000000;}
</style>

<?php
$Periodo_Selecionado = ($p > 0) ? Periodo::Load($p) : Periodo::getAtual();

$Aluno = ($ra > 0) ? Aluno::Load($ra) : $_Usuario->getAluno(true);
$Horario = $Aluno->Monta_Horario($Periodo_Selecionado->getPeriodo(), $n);

$UsuarioAluno = ($Aluno->getUsuario(false) !== null) ? $Aluno->getUsuario(false) : new Usuario();
$pode_ver = $_Usuario->Pode_Ver($UsuarioAluno, 'horario');
if((is_array($pode_ver) && !$pode_ver[0]) || !$pode_ver)
	exit;

$meu = ($_Usuario->getAluno(true)->getID() == $Aluno->getID());
$limpos = Util::Horarios_Livres($Horario);
?>
<table border="0" cellspacing="0" class="tabela_bonyta">
	<tr>
		<td colspan="7" align="center" style="padding: 10px;"><strong><?= $Aluno->getNome(true); ?><br />Hor&aacute;rio para <?= $Periodo_Selecionado->getNome(false); ?></strong></td>
	</tr>
	<?php if(count($limpos) == 16) { ?>
		<tr>
			<td align="center" colspan="7"><strong>Hor&aacute;rio indispon&iacute;vel para <?= $Periodo_Selecionado->getNome(false); ?>!</strong></td>
		</tr>
		<?php
	} else {
		?>
		<tr>
			<td align="center"><strong>-</strong></td>
			<td align="center"><strong>Segunda</strong></td>
			<td align="center"><strong>Ter&ccedil;a</strong></td>
			<td align="center"><strong>Quarta</strong></td>
			<td align="center"><strong>Quinta</strong></td>
			<td align="center"><strong>Sexta</strong></td>
			<td align="center"><strong>S&aacute;bado</strong></td>
		</tr>
		<?php
		for($j = 7; $j < 23; $j++) {
			if((!isset($_GET['f'])) && (in_array($j, $limpos)))
				continue;
			?>
			<tr>
				<td align="center"><strong><?= $j; ?>:00</strong></td>
				<?php
				for($i = 2; $i < 8; $i++) {
					$tem = isset($Horario[$i][$j]);
					?>
					<td align="center"><?= $_Usuario->Formata_Horario($Horario, $i, $j, $meu, $Periodo_Selecionado, false); ?></td>
				<?php } ?>
			</tr>
			<?php
		}
	}
	?>
	<tr>
		<td colspan="2" style="padding: 10px;"><strong>Matr&iacute;culas (<?=$Aluno->Creditos_Atuais($Periodo_Selecionado->getPeriodo(), $n); ?>):</strong></td>
		<td colspan="5" style="padding: 10px;"><?=$Aluno->getOferecimentos($Periodo_Selecionado->getPeriodo(), $n, true, false); ?></td>
	</tr>
</table><br />
<a href="#" onclick="window.print(); return false;">Imprimir</a>
<?= (!isset($_GET['f'])) ? "<br /><a href=\"".CONFIG_URL."imprimir-horario/?ra=".intval($_GET['ra'])."&p=".$Periodo_Selecionado->getID(true)."&n=".$n."&f\">Hor&aacute;rio Completo</a>" : "<br /><a href=\"".CONFIG_URL."imprimir-horario/?ra=".intval($_GET['ra'])."&p=".$Periodo_Selecionado->getID(true)."&n=".$n."\">Hor&aacute;rio Resumido</a>"; ?>
</body>
<html>
