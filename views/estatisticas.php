<?php

namespace GDE;

define('TITULO', 'Estat&iacute;sticas');

require_once('../common/common.inc.php');

$dados = Dado::Pega_Dados();
$professores = $dados['professores'];
$salas = $dados['salas'];
$usuarios = $dados['usuarios'];
$online = Usuario::Conta_Online(false);

$Periodo = Periodo::getAtual();

$connection = Base::_EM()->getConnection();

?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#estatisticas1").tablesorter();
		$("#estatisticas2").tablesorter();
	});
</script>
<h1>Estat&iacute;sticas</h1>

<table border='1' cellspacing='1' cellpadding='1' width='70%'>
	<tr>
		<td colspan='4' align='center'><strong>Dados do Sistema:</strong></td>
	</tr>
	<tr>
		<td>-</td>
		<td><strong>Gradua&ccedil;&atilde;o</strong></td>
		<td><strong>P&oacute;s-Gradua&ccedil;&atilde;o</strong></td>
		<td><strong>Total</strong></td>
	</tr>
	<tr>
		<td>Per&iacute;odo</td>
		<td colspan='3'><strong><?= $Periodo->getNome(); ?></strong></td>
	</tr>
	<tr>
		<td>Alunos</td>
		<td><strong><?= $dados['alunos_grad']; ?></strong></td>
		<td><strong><?= $dados['alunos_pos']; ?></strong></td>
		<td><strong><?= $dados['alunos']; ?></strong></td>
	</tr>
	<tr>
		<td>Alunos Matriculados neste Per&iacute;odo</td>
		<td><strong><?= $dados['ativos_grad']; ?></strong></td>
		<td><strong><?= $dados['ativos_pos']; ?></strong></td>
		<td><strong><?= $dados['ativos']; ?></strong></td>
	</tr>
	<tr>
		<td>Usu&aacute;rios no GDE</td>
		<td><strong><?= $dados['usuarios_grad']; ?></strong></td>
		<td><strong><?= $dados['usuarios_pos']; ?></strong></td>
		<td><strong><?= $usuarios; ?></strong></td>
	</tr>
	<tr>
		<td>Usu&aacute;rios matriculados neste Per&iacute;odo no GDE</td>
		<td><strong><?= $dados['usuarios_ativos_grad']; ?></strong></td>
		<td><strong><?= $dados['usuarios_ativos_pos']; ?></strong></td>
		<td><strong><?= $dados['usuarios_ativos']; ?></strong></td>
	</tr>
	<tr>
		<td>Usu&aacute;rios com acesso nos &uacute;ltimos 6 meses</td>
		<td><strong><?= $dados['usuarios_acesso_grad']; ?></strong></td>
		<td><strong><?= $dados['usuarios_acesso_pos']; ?></strong></td>
		<td><strong><?= $dados['usuarios_acesso']; ?></strong></td>
	</tr>
	<tr>
		<td>Propor&ccedil;&atilde;o Usu&aacute;rios / Alunos</td>
		<td><strong><?= number_format($dados['usuarios_grad']*100/$dados['alunos_grad'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios_pos']*100/$dados['alunos_pos'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios']*100/$dados['alunos'], 2); ?>%</strong></td>
	</tr>
	<tr>
		<td>Propor&ccedil;&atilde;o Usu&aacute;rios / Alunos matriculados</td>
		<td><strong><?= number_format($dados['usuarios_grad']*100/$dados['ativos_grad'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios_pos']*100/$dados['ativos_pos'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios']*100/$dados['ativos'], 2); ?>%</strong></td>
	</tr>
	<tr>
		<td>Propor&ccedil;&atilde;o Usu&aacute;rios Matriculados / Alunos matriculados</td>
		<td><strong><?= number_format($dados['usuarios_ativos_grad']*100/$dados['ativos_grad'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios_ativos_pos']*100/$dados['ativos_pos'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios_ativos']*100/$dados['ativos'], 2); ?>%</strong></td>
	</tr>
	<tr>
		<td>Propor&ccedil;&atilde;o Usu&aacute;rios Acesso / Alunos matriculados</td>
		<td><strong><?= number_format($dados['usuarios_acesso_grad']*100/$dados['ativos_grad'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios_acesso_pos']*100/$dados['ativos_pos'], 2); ?>%</strong></td>
		<td><strong><?= number_format($dados['usuarios_acesso']*100/$dados['ativos'], 2); ?>%</strong></td>
	</tr>
	<tr>
		<td>Disciplinas</td>
		<td><strong><?= $dados['disciplinas_grad']; ?></strong></td>
		<td><strong><?= $dados['disciplinas_pos']; ?></strong></td>
		<td><strong><?= $dados['disciplinas']; ?></strong></td>
	</tr>
	<tr>
		<td>Oferecimentos</td>
		<td><strong><?= $dados['oferecimentos_grad']; ?></strong></td>
		<td><strong><?= $dados['oferecimentos_pos']; ?></strong></td>
		<td><strong><?= $dados['oferecimentos']; ?></strong></td>
	</tr>
	<tr>
		<td>Professores</td>
		<td colspan='3'><strong><?= $professores; ?></strong></td>
	</tr>
	<tr>
		<td>Salas</td>
		<td colspan='3'><strong><?= $salas; ?></strong></td>
	</tr>
	<tr>
		<td>Online No Momento:</td>
		<td colspan='3'><strong><?= $online; ?></strong></td>
	</tr>
	<tr>
		<td>&Uacute;ltima Atualiza&ccedil;&atilde;o:</td>
		<td colspan='3'><strong><?= $dados['ultima_atualizacao']->format("d/m/Y"); ?></strong></td>
	</tr>
</table>

<?php

if(($_Usuario === null) || ($_Usuario->getAdmin() === false))
	die($FIM);

// ToDo

/*$total = 0;
foreach($Cadastros as $Usr) {
	if(!isset($cursos[$Usr->getCurso()]))
		$cursos[$Usr->getCurso()] = 0;
	if(!isset($catalogos[$Usr->getCatalogo()]))
		$catalogos[$Usr->getCatalogo()] = 0;
	if(!isset($cursos_catalogos[$Usr->getCurso()][$Usr->getCatalogo()]))
		$cursos_catalogos[$Usr->getCurso()][$Usr->getCatalogo()] = 0;
	$cursos[$Usr->getCurso()]++;
	$catalogos[$Usr->getCatalogo()]++;
	$cursos_catalogos[$Usr->getCurso()][$Usr->getCatalogo()]++;
	$total++;
}*/

$total = $dados['usuarios'];

$alunos_por_curso = $connection->executeQuery("SELECT COUNT(*) AS total, id_curso FROM gde_alunos WHERE nivel IS NOT NULL GROUP BY id_curso ORDER BY id_curso ASC")->fetchAll();
foreach($alunos_por_curso as $linha)
	$alunos_curso[$linha['id_curso']] = $linha['total'];

$alunos_por_curso_ativos = $connection->executeQuery("SELECT COUNT(DISTINCT A.ra) AS total, A.id_curso FROM gde_alunos AS A INNER JOIN gde_r_alunos_oferecimentos AS AO ON (AO.ra = A.ra) INNER JOIN gde_oferecimentos AS O ON (O.id_oferecimento = AO.id_oferecimento) WHERE A.nivel IS NOT NULL AND O.id_periodo = ? GROUP BY A.id_curso ORDER BY A.id_curso ASC", array($Periodo->getPeriodo()))->fetchAll();
foreach($alunos_por_curso_ativos as $linha)
	$alunos_curso_ativos[$linha['id_curso']] = $linha['total'];

$usuarios_por_curso = $connection->executeQuery("SELECT COUNT(A.ra) AS total, A.id_curso FROM gde_alunos AS A INNER JOIN gde_usuarios AS U ON (U.ra = A.ra) WHERE A.nivel IS NOT NULL GROUP BY A.id_curso ORDER BY A.id_curso ASC")->fetchAll();
foreach($usuarios_por_curso as $linha)
	$usuarios_curso[$linha['id_curso']] = $linha['total'];

$usuarios_por_curso_ativos = $connection->executeQuery("SELECT COUNT(DISTINCT A.ra) AS total, A.id_curso FROM gde_alunos AS A INNER JOIN gde_r_alunos_oferecimentos AS AO ON (AO.ra = A.ra) INNER JOIN gde_oferecimentos AS O ON (O.id_oferecimento = AO.id_oferecimento) WHERE A.nivel IS NOT NULL AND O.id_periodo = ? AND A.ra IN (SELECT ra FROM gde_usuarios) GROUP BY A.id_curso ORDER BY A.id_curso ASC", array($Periodo->getPeriodo()))->fetchAll();
foreach($usuarios_por_curso_ativos as $linha)
	$usuarios_curso_ativos[$linha['id_curso']] = $linha['total'];

$res2 = $connection->executeQuery("SELECT COUNT(catalogo) AS total, catalogo FROM gde_usuarios WHERE catalogo IS NOT NULL GROUP BY catalogo")->fetchAll();
foreach($res2 as $linha)
	$catalogos[$linha['catalogo']] = $linha['total'];

$vagas = array(
	'1998' => 2255,
	'1999' => 2325,
	'2000' => 2355,
	'2001' => 2355,
	'2002' => 2450,
	'2003' => 2690,
	'2004' => 2810,
	'2005' => 2810,
	'2006' => 2830,
	'2007' => 2830,
	'2008' => 2830,
	'2009' => 3310,
	'2010' => 3320,
	'2011' => 3320,
	'2012' => 3320,
	'2013' => 3320,
	'2014' => 3320,
	'2015' => 3320,
	'2016' => 3320,
	'2017' => 3330,
	'2018' => 3330
);

arsort($usuarios_curso);
arsort($catalogos);
?>

<br />
<h2>Usu&aacute;rios Cadastrados Por Curso</h2>
<table border="1" width="95%" id="estatisticas1" class="tabela_busca tablesorter">
	<thead>
	<tr>
		<th width="50%" rowspan="2"><strong>Curso</strong></th>
		<td width="22%" colspan="3" align="center"><strong>Total</strong></td>
		<td width="22%" colspan="3" align="center"><strong>Ativos</strong></td>
		<th width="6%" rowspan="2"><strong>% no GDE</strong></th>
	</tr>
	<tr>
		<th width="9%"><strong>Alunos</strong></th>
		<th width="9%"><strong>Usu&aacute;rios</strong></th>
		<th width="4%"><strong>%</strong></th>
		<th width="9%"><strong>Alunos</strong></th>
		<th width="9%"><strong>Usu&aacute;rios</strong></th>
		<th width="4%"><strong>%</strong></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($usuarios_curso as $id_curso => $quantos) {
		if(!isset($alunos_curso_ativos[$id_curso]))
			$alunos_curso_ativos[$id_curso] = 0;
		if(!isset($usuarios_curso_ativos[$id_curso]))
			$usuarios_curso_ativos[$id_curso] = 0;
		$Curso = Curso::Load($id_curso);
		if($Curso->getID() == null) {
			$Curso = new Curso;
			$Curso->setNome('Egressado');
			$Curso->setNumero(0);
		}
		?>
		<tr>
			<td><?= $Curso->getNome(true); ?> (<?= $Curso->getNumero(true); ?>)</td>
			<td><?= $alunos_curso[$id_curso]; ?></td>
			<td><?= $usuarios_curso[$id_curso]; ?></td>
			<td><?= number_format(($usuarios_curso[$id_curso] / $alunos_curso[$id_curso])*100, 2); ?>%</td>
			<td><?= $alunos_curso_ativos[$id_curso]; ?></td>
			<td><?= $usuarios_curso_ativos[$id_curso]; ?></td>
			<td><?= ($alunos_curso_ativos[$id_curso] != 0) ? number_format(($usuarios_curso_ativos[$id_curso] / $alunos_curso_ativos[$id_curso])*100, 2) : '0.00'; ?>%</td>
			<td><?= number_format(($usuarios_curso[$id_curso] / $usuarios)*100, 2); ?> %</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<br />
<h2>Usu&aacute;rios Cadastrados Por Cat&aacute;logo</h2>
<table border="1" width="95%" id="estatisticas2" class="tabela_busca tablesorter">
	<thead>
	<tr>
		<th><strong>Cat&aacute;logo</strong></th>
		<th><strong>Quantidade</strong></th>
		<th><strong>% do Cat&aacute;logo</strong></th>
		<th><strong>% no GDE</strong></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach($catalogos as $catalogo => $quantos) {
		if(!isset($vagas[$catalogo]))
			continue;
		$pc_gde = number_format(($quantos / $vagas[$catalogo])*100, 2);
		if($pc_gde > 100)
			$pc_gde = '100.00+';
		?>
		<tr>
			<td><?= $catalogo; ?></td>
			<td><?= $quantos; ?></td>
			<td><?= $pc_gde;  ?>%</td>
			<td><?= number_format(($quantos / $total)*100, 2); ?>%</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?= $FIM; ?>
