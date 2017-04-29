<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if(isset($_POST['q'])) {
	$simples = true;
	$q = Util::Limpa_Busca($_POST['q']);
	if(strlen($q) < 1)
		exit();
} elseif(isset($_POST['buscar']))
	$simples = false;
else
	exit();

$tp = (isset($_POST['t'])) ? str_replace('tab_', '', $_POST['t']) : 'tudo';

$total = array();
$total['tudo'] = $total['alunos'] = $total['professores'] = $total['disciplinas'] = $total['oferecimentos'] = $total['salas'] = '?';
$qts['tudo'] = $qts['alunos'] = $qts['professores'] = $qts['disciplinas'] = $qts['oferecimentos'] = $qts['salas'] = 0;

if($tp == 'tudo') {
	$qts['tudo'] = -1;
	$qts['alunos'] = 20;
	$qts['professores'] = 5;
	$qts['disciplinas'] = 5;
	$qts['oferecimentos'] = 10;
	$qts['salas'] = 12;
	$apg['tudo'] = 1;
	$sta['tudo'] = $sta['alunos'] = $sta['professores'] = $sta['disciplinas'] = $sta['oferecimentos'] = $sta['salas'] = 0;
} else {
	if(isset($_POST['resultados_pagina']))
		$qts[$tp] = intval($_POST['resultados_pagina']);
	else
		$qts[$tp] = 20;
	$apg[$tp] = (isset($_POST['p'])) ? intval($_POST['p'])-1 : 0;
	$sta[$tp] = $apg[$tp] * $qts[$tp];
}

if($simples) {
	$ordem = array();
	$ordem['alunos'] = (isset($_POST['ord']['alunos_s'])) ? Aluno::$ordens_inte[$_POST['ord']['alunos_s']]." ".(($_POST['em']['alunos_s'] == 1) ? 'ASC' : 'DESC') : 'rank DESC';
	$ordem['professores'] = (isset($_POST['ord']['professores_s'])) ? Professor::$ordens_inte[$_POST['ord']['professores_s']]." ".(($_POST['em']['professores_s'] == 1) ? 'ASC' : 'DESC') : 'rank DESC';
	$ordem['disciplinas'] = (isset($_POST['ord']['disciplinas_s'])) ? Disciplina::$ordens_inte[$_POST['ord']['disciplinas_s']]." ".(($_POST['em']['disciplinas_s'] == 1) ? 'ASC' : 'DESC') : 'rank DESC';
	$ordem['oferecimentos'] = (isset($_POST['ord']['oferecimentos_s'])) ? Oferecimento::$ordens_inte[$_POST['ord']['oferecimentos_s']]." ".(($_POST['em']['oferecimentos_s'] == 1) ? 'ASC' : 'DESC') : 'rank DESC';
	$ordem['salas'] = (isset($_POST['ord']['salas_s'])) ? Sala::$ordens_inte[$_POST['ord']['salas_s']]." ".(($_POST['em']['salas_s'] == 1) ? 'ASC' : 'DESC') : 'S.nome ASC';

	$start = microtime(true);
	$Alunos = ($qts['alunos'] != 0) ? Aluno::Consultar_Simples($q, $ordem['alunos'], $total['alunos'], $qts['alunos'], $sta['alunos']) : array();
	$Professores = ($qts['professores'] != 0) ? Professor::Consultar_Simples($q, $ordem['professores'], $total['professores'], $qts['professores'], $sta['professores']) : array();
	$Disciplinas = ($qts['disciplinas'] != 0) ? Disciplina::Consultar_Simples($q, $ordem['disciplinas'], $total['disciplinas'], $qts['disciplinas'], $sta['disciplinas']) : array();
	$Oferecimentos = ($qts['oferecimentos'] != 0) ? Oferecimento::Consultar_Simples($q, $ordem['oferecimentos'], $total['oferecimentos'], $qts['oferecimentos'], $sta['oferecimentos']) : array();
	$Salas = ($qts['salas'] != 0) ? Sala::Consultar_Simples($q, $ordem['salas'], $total['salas'], $qts['salas'], $sta['salas']) : array();
	$tempo = number_format(microtime(true) - $start, 3, ',', '.');
} else {
	$parametros = array();
	
	if($tp == 'alunos') {
	
		if((isset($_POST['nome'])) && ($_POST['nome'] != ''))
			$parametros['nome'] = trim(Util::Limpa_Busca($_POST['nome']));
			
		if((isset($_POST['ra'])) && ($_POST['ra'] != ''))
			$parametros['ra'] = intval($_POST['ra']);
			
		if((isset($_POST['nivel'])) && ($_POST['nivel'] != ''))
			$parametros['nivel'] = trim(Util::Limpa_Busca($_POST['nivel']));
			
		if((isset($_POST['curso'])) && ($_POST['curso'] != '-1'))
			$parametros['curso'] = intval($_POST['curso']);
			
		if((isset($_POST['modalidade'])) && ($_POST['modalidade'] != ''))
			$parametros['modalidade'] = trim(Util::Limpa_Busca($_POST['modalidade']));
		
		if((isset($_POST['nivel_pos'])) && ($_POST['nivel_pos'] != ''))
			$parametros['nivel_pos'] = trim(Util::Limpa_Busca($_POST['nivel_pos']));
			
		if((isset($_POST['curso_pos'])) && ($_POST['curso_pos'] != '-1'))
			$parametros['curso_pos'] = intval($_POST['curso_pos']);
			
		if((isset($_POST['modalidade_pos'])) && ($_POST['modalidade_pos'] != ''))
			$parametros['modalidade_pos'] = trim(Util::Limpa_Busca($_POST['modalidade_pos']));
		
		if((isset($_POST['id_oferecimento'])) && ($_POST['id_oferecimento'] > 0))
			$parametros['id_oferecimento'] = intval($_POST['id_oferecimento']);
		
		if((isset($_POST['cursando_tipo'])) && (isset($_POST['cursando_sigla']))) {
			$parametros['oferecimentos'][0] = ($_POST['cursando_tipo'] == 'E');
			$parametros['oferecimentos'][1] = array();
			foreach($_POST['cursando_sigla'] as $k => $sigla) {
				if($sigla != '')
					$parametros['oferecimentos'][1][] = array(Util::Limpa_Busca($sigla), Util::Limpa_Busca($_POST['cursando_turma'][$k]));
			}
		}
		
		if(isset($_POST['periodo']))
			$parametros['periodo'] = intval($_POST['periodo']);
		
		if((isset($_POST['ano'])) && ($_POST['ano'] != '-1'))
			$parametros['ano'] = intval($_POST['ano']);
		
		if((isset($_POST['sexo'])) && ($_POST['sexo'] != ''))
			$parametros['sexo'] = $_POST['sexo'][0];
		
		if((isset($_POST['relacionamento'])) && (intval($_POST['relacionamento']) != 0))
			$parametros['relacionamento'] = intval($_POST['relacionamento']);
		
		if((isset($_POST['cidade'])) && ($_POST['cidade'] != ''))
			$parametros['cidade'] = trim(Util::Limpa_Busca($_POST['cidade']));
		
		if((isset($_POST['estado'])) && ($_POST['estado'] != ''))
			$parametros['estado'] = trim(Util::Limpa_Busca($_POST['estado']));
		
		if((isset($_POST['gde'])) && ($_POST['gde'] != ''))
			$parametros['gde'] = $_POST['gde'][0];
		
		if(isset($_POST['ord'][$tp.'_a'])) {
			if($_POST['ord'][$tp.'_a'] == 0) // Nao tem relevancia nesta consulta
				$_POST['ord'][$tp.'_a'] = 1;
			$ordem[$tp] = Aluno::$ordens_inte[$_POST['ord'][$tp.'_a']]." ".(($_POST['em'][$tp.'_a'] == 1) ? 'ASC' : 'DESC');
		} else
			$ordem[$tp] = null;
		
		if(isset($_POST['amigos']) && $_POST['amigos'] == 't') {
			$parametros['amigos'] = true;
			$parametros['id_usuario'] = $_Usuario->getID();
		}
		
		$start = microtime(true);
		$Alunos = Aluno::Consultar($parametros, $ordem[$tp], $total[$tp], $qts[$tp], $sta[$tp]);
		$tempo = number_format(microtime(true) - $start, 3, ',', '.');
		
	} elseif($tp == 'disciplinas') {
	
		if((isset($_POST['sigla'])) && ($_POST['sigla'] != ''))
			$parametros['sigla'] = trim(Util::Limpa_Busca($_POST['sigla']));
			
		if((isset($_POST['nome'])) && ($_POST['nome'] != ''))
			$parametros['nome'] = trim(Util::Limpa_Busca($_POST['nome']));
			
		if((isset($_POST['nivel'])) && ($_POST['nivel'] != '0'))
			$parametros['nivel'] = trim(Util::Limpa_Busca($_POST['nivel']));
			
		if((isset($_POST['instituto'])) && ($_POST['instituto'] != '0'))
			$parametros['instituto'] = intval($_POST['instituto']);
		
		if((isset($_POST['creditos'])) && ($_POST['creditos'] != ''))
			$parametros['creditos'] = intval($_POST['creditos']);
		
		if((isset($_POST['periodicidade'])) && ($_POST['periodicidade'] != ''))
			$parametros['periodicidade'] = intval($_POST['periodicidade']);
			
		if((isset($_POST['ementa'])) && ($_POST['ementa'] != ''))
			$parametros['ementa'] = trim(Util::Limpa_Busca($_POST['ementa']));
		
		if(isset($_POST['ord'][$tp.'_a'])) {
			if($_POST['ord'][$tp.'_a'] == 0) // Nao tem relevancia nesta consulta
				$_POST['ord'][$tp.'_a'] = 1;
			$ordem[$tp] = Disciplina::$ordens_inte[$_POST['ord'][$tp.'_a']]." ".(($_POST['em'][$tp.'_a'] == 1) ? 'ASC' : 'DESC');
		} else
			$ordem[$tp] = null;
		
		$start = microtime(true);
		$Disciplinas = Disciplina::Consultar($parametros, $ordem[$tp], $total[$tp], $qts[$tp], $sta[$tp]);
		$tempo = number_format(microtime(true) - $start, 3, ',', '.');
		
	} elseif($tp == 'oferecimentos') {
	
		if((isset($_POST['periodo'])) && ($_POST['periodo'] != 0))
			$parametros['periodo'] = intval($_POST['periodo']);
		
		if((isset($_POST['sigla'])) && ($_POST['sigla'] != ''))
			$parametros['sigla'] = trim(Util::Limpa_Busca($_POST['sigla']));
			
		if((isset($_POST['turma'])) && ($_POST['turma'] != ''))
			$parametros['turma'] = trim(Util::Limpa_Busca($_POST['turma']));
			
		if((isset($_POST['nome'])) && ($_POST['nome'] != ''))
			$parametros['nome'] = trim(Util::Limpa_Busca($_POST['nome']));
			
		if((isset($_POST['professor'])) && ($_POST['professor'] != ''))
			$parametros['professor'] = trim(Util::Limpa_Busca($_POST['professor']));
			
		if((isset($_POST['creditos'])) && ($_POST['creditos'] != ''))
			$parametros['creditos'] = intval($_POST['creditos']);
		
		if((isset($_POST['instituto'])) && ($_POST['instituto'] != '0'))
			$parametros['instituto'] = intval($_POST['instituto']);
		
		if((isset($_POST['nivel'])) && ($_POST['nivel'] != '0'))
			$parametros['nivel'] = trim(Util::Limpa_Busca($_POST['nivel']));
		
		if((isset($_POST['dia'])) && ($_POST['dia'] != ''))
			$parametros['dia'] = intval($_POST['dia']);
		
		if((isset($_POST['horario'])) && ($_POST['horario'] != ''))
			$parametros['horario'] = intval($_POST['horario']);
		
		if((isset($_POST['sala'])) && ($_POST['sala'] != ''))
			$parametros['sala'] = trim(Util::Limpa_Busca($_POST['sala']));
		
		if(isset($_POST['ord'][$tp.'_a'])) {
			if($_POST['ord'][$tp.'_a'] == 0) // Nao tem relevancia nesta consulta
				$_POST['ord'][$tp.'_a'] = 1;
			$ordem[$tp] = Oferecimento::$ordens_inte[$_POST['ord'][$tp.'_a']]." ".(($_POST['em'][$tp.'_a'] == 1) ? 'ASC' : 'DESC');
		} else
			$ordem[$tp] = null;
		
		$start = microtime(true);
		$Oferecimentos = Oferecimento::Consultar($parametros, $ordem[$tp], $total[$tp], $qts[$tp], $sta[$tp]);
		$tempo = number_format(microtime(true) - $start, 3, ',', '.');
	
	}
}

$fim[$tp] = $sta[$tp] + $qts[$tp];
if($fim[$tp] > $total[$tp])
	$fim[$tp] = $total[$tp];

$pgs = ceil(intval($total[$tp]) / intval($qts[$tp]));
$paginas = "";
$range = 3;

for($i = 1; $i <= min($range + 1, $pgs); $i++) {
	$paginas .= '<a href="#tab_'.$tp.'$'.$i.'" class="link_pagina">'.(($i-1 == $apg[$tp])?'<strong>'.$i.'</strong>':$i).'</a> ';
}
$lower = $apg[$tp] + 1;
$upper = $apg[$tp] + 1;

for($i = 0; $i < $range; $i++) {
	if($lower > 1 + $range)
		$lower--;
	if($upper < $pgs + $range)
		$upper++;
}
while($lower <= $range + 1)
	$lower++;
while($upper >= $pgs - $range)
	$upper--;
	
if(($apg[$tp] + 1) - $lower == $range && ($apg[$tp] + 1) != $lower && $lower != ($range + 2))
	$paginas .= ' ... ';

for($i = $lower; $i <= $upper; $i++)
	$paginas .= '<a href="#tab_'.$tp.'$'.$i.'" class="link_pagina">'.(($i-1 == $apg[$tp])?'<strong>'.$i.'</strong>':$i).'</a> ';

if($upper - ($apg[$tp] + 1) == $range && $pgs != ($upper + 1) && $upper != ($pgs - $range - 1))
	$paginas .= ' ... ';

if($upper < $pgs - $range)
	for($i = max($upper + 1, $pgs - $range, min($range + 1, $pgs) + 1); $i <= $pgs; $i++)
		$paginas .= '<a href="#tab_'.$tp.'$'.$i.'" class="link_pagina">'.(($i-1 == $apg[$tp])?'<strong>'.$i.'</strong>':$i).'</a> ';

$total['tudo'] = intval($total['alunos']) + intval($total['professores']) + intval($total['disciplinas']) + intval($total['oferecimentos']) + intval($total['salas']);

?>
<script type="text/javascript">
// <![CDATA[
<?php
foreach($total as $t => $c)
	if($qts[$t] != 0)
		echo '$("#resultados_'.$t.'").text("'.$c.'");';
?>
// ]]>
</script>
<?php
if($tp == 'tudo') {
?>
<span class="cabecalho_resultados_busca">Exibindo <?= $total['tudo']; ?> resultado(s)  (<?= $tempo; ?> segundos)</span>
<?php
}
if($qts['alunos'] != 0) {
	if($total['alunos'] == 0) {
		if($tp != 'tudo')
			echo '<br />Nenhum resultado encontrado!';
	} else {	
		echo ($tp == 'tudo') ? '<h2>Alunos ('.$total['alunos'].'):</h2>' : '<span class="cabecalho_resultados_busca">Exibindo resultados '.($sta['alunos']+1).' - '.$fim['alunos'].' de '.$total['alunos'].' ('.$tempo.' segundos)</span>';
		if(isset($_POST['tpres']) && ($_POST['tpres'] == 1)) {
?>
<table border='0' width='100%'>
<?php
			foreach($Alunos as $a => $Aluno) {
				if($a % 2 == 0)
					echo "	<tr>";
?>
		<td width="50%">
			<table border="1" width="100%">
				<tr>
					<td width="128" height="150" align="center" rowspan="7"><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Aluno->getRA(); ?>"><img src="<?= ($Aluno->getUsuario(false) !== null) ? $Aluno->getUsuario()->getFoto(true) : Usuario::getFoto_Padrao(); ?>" alt="Foto" border="0" /></a></td>
					<td width="25%" height="20%"><strong>RA:</strong></td><td height="20%"><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Aluno->getRA(true); ?>"><?= $Aluno->getRA(true); ?></a></td>
				</tr>
				<tr>
					<td width="25%" height="20%"><strong>Nome:</strong></td>
					<td height="20%"><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Aluno->getRA(); ?>"><?= ($Aluno->getUsuario(false) !== null) ? (($_Usuario->Amigo($Aluno->getUsuario()) !== false)?"<strong>":null).$Aluno->getNome().(($_Usuario->Amigo($Aluno->getUsuario()) !== false)?"</strong>":null) : $Aluno->getNome(); ?></a></td>
				</tr>
				<tr>
					<td width="25%" height="20%"><strong>Curso:</strong></td>
					<td height="20%"><?= ($Aluno->getCurso(false) !== null) ? $Aluno->getCurso()->getNome(true)." (".$Aluno->getCurso()->getNumero(true).")" : '-'; ?></td>
				</tr>
				<tr>
					<td width="25%" height="20%"><strong>Modalidade:</strong></td>
					<td height="20%"><?= ($Aluno->getModalidade(false) !== null) ? $Aluno->getModalidade(true) : '-'; ?></td>
				</tr>
				<tr>
					<td width="25%" height="20%"><strong>Curso P&oacute;s:</strong></td>
					<td height="20%"><?= ($Aluno->getCurso_Pos(false) !== null) ? $Aluno->getCurso_Pos()->getNome(true)." (".$Aluno->getCurso_Pos()->getNumero(true).")" : '-'; ?></td>
				</tr>
				<tr>
					<td width="25%" height="20%"><strong>Modalidade P&oacute;s:</strong></td>
					<td height="20%"><?= ($Aluno->getModalidade_Pos(false) !== null) ? $Aluno->getModalidade_Pos(true) : '-'; ?></td>
				</tr>
				<tr>
					<td width="25%" height="20%"><strong>Usa o GDE:</strong></td>
					<td height="20%"><?= ($Aluno->getUsuario(false) !== null) ? 'Sim' : 'N&atilde;o - <a href="'.CONFIG_URL.'recomendar/?ra='.$Aluno->getRA().'">Convidar</a>' ?></td>
				</tr>
			</table>
		</td>
<?php
				if($a % 2 == 1)
					echo "	</tr>";
			} ?>
	<tr>
		<td colspan="2" align="center"><?= $paginas; ?></td>
	</tr>
<?php
		} else { ?>
<table border="1" width="100%" class="tabela_busca">
	<tr>
		<th align='center'>RA</th>
		<th align='center'>Nome</th>
		<th align='center'>Curso</th>
		<th align='center'>Modalidade</th>
		<th align='center'>Curso P&oacute;s</th>
		<th align='center'>Modalidade P&oacute;s</th>
	</tr>
<?php
			foreach($Alunos as $Aluno) {
?>
	<tr>
		<td><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Aluno->getRA(true); ?>"><?= $Aluno->getRA(true); ?></a></td>
		<td><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Aluno->getRA(true); ?>"><?= ($Aluno->getUsuario(false) !== null) ? (($_Usuario->Amigo($Aluno->getUsuario(false)) !== false)?"<strong>":null).$Aluno->getNome(true).(($_Usuario->Amigo($Aluno->getUsuario(false)) !== false)?"</strong>":null) : $Aluno->getNome(true); ?></a></td>
		<td><?= ($Aluno->getCurso(false) !== null) ? $Aluno->getCurso()->getNome(true)." (".$Aluno->getCurso()->getNUmero(true).")" : '-'; ?></td>
		<td><?= ($Aluno->getModalidade(false) !== null) ? $Aluno->getModalidade(true) : '-'; ?></td>
		<td><?= ($Aluno->getCurso_Pos(false) !== null) ? $Aluno->getCurso_Pos()->getNome(true)." (".$Aluno->getCurso_Pos()->getNumero(true).")" : '-'; ?></td>
		<td><?= ($Aluno->getModalidade_Pos(false) !== null) ? $Aluno->getModalidade_Pos(true) : '-'; ?></td>
	</tr>
<?php
			}
?>
</table>
		<?= (($tp == 'tudo') && ($total['alunos'] > $qts['alunos'])) ? '<a href="#tab_alunos" id="ir_tab_alunos" class="ir_tab">Mais Alunos...</a>' : $paginas; ?>
<br />
<?php
		}
	}
} if($qts['professores'] != 0) {
	if($total['professores'] == 0) {
		if($tp != 'tudo')
			echo '<br />Nenhum resultado encontrado!';
	} else {
		echo ($tp == 'tudo') ? '<h2>Professores ('.$total['professores'].'):</h2>' : '<span class="cabecalho_resultados_busca">Exibindo resultados '.($sta['professores']+1).' - '.$fim['professores'].' de '.$total['professores'].' ('.$tempo.' segundos)</span>';
?>
<table border="1" width="100%" class="tabela_busca">
	<tr>
		<th align='center'>Nome</th>
		<th align='center'>Instituto</th>
	</tr>
<?php
		foreach($Professores as $Professor) {
?>
	<tr>
		<td><a href="<?= CONFIG_URL; ?>perfil/?professor=<?= $Professor->getID(); ?>"><?= $Professor->getNome(true); ?></a></td>
		<td><?= ($Professor->getInstituto() === null) ? 'Desconhecido' : $Professor->getInstituto()->getSigla(true).' - '.$Professor->getInstituto()->getNome(true); ?></td>
	</tr>
<?php
		}
?>
</table>
		<?= (($tp == 'tudo') && ($total['professores'] > $qts['professores'])) ? '<a href="#tab_professores" id="ir_tab_professores" class="ir_tab">Mais Professores...</a>' : $paginas; ?>
<br />
<?php
	}
} if($qts['disciplinas'] != 0) {
	if($total['disciplinas'] == 0) {
		if($tp != 'tudo')
			echo '<br />Nenhum resultado encontrado!';
	} else {
		echo ($tp == 'tudo') ? '<h2>Disciplinas ('.$total['disciplinas'].'):</h2>' : '<span class="cabecalho_resultados_busca">Exibindo resultados '.($sta['disciplinas']+1).' - '.$fim['disciplinas'].' de '.$total['disciplinas'].' ('.$tempo.' segundos)</span>';
?>
<table border="1" width="100%" class="tabela_busca">
	<tr>
		<th align='center'>Sigla</th>
		<th align='center'>Nome</th>
		<th align='center'>Cr&eacute;ditos</th>
		<th align='center'>Ementa</th>
	</tr>
<?php
		foreach($Disciplinas as $Disciplina) {
?>
	<tr>
		<td><a href="<?= CONFIG_URL; ?>disciplina/<?= $Disciplina->getSigla(true); ?>/"><?= $Disciplina->getSigla(true); ?></a></td>
		<td><a href="<?= CONFIG_URL; ?>disciplina/<?= $Disciplina->getSigla(true); ?>/"><?= $Disciplina->getNome(true); ?></a></td>
		<td><?= $Disciplina->getCreditos(true); ?></td>
		<td><?= Util::Limita($Disciplina->getEmenta(true), 100); ?></td>
	</tr>
<?php
		}
?>
</table>
	<?= (($tp == 'tudo')  && ($total['disciplinas'] > $qts['disciplinas'])) ? '<a href="#tab_disciplinas" id="ir_tab_disciplinas" class="ir_tab">Mais Disciplinas...</a>' : $paginas; ?>
<br />
<?php
	}
} if($qts['oferecimentos'] != 0) {
	if($total['oferecimentos'] == 0) {
		if($tp != 'tudo')
			echo '<br />Nenhum resultado encontrado!';
	} else {
		$niveis_oferecimentos = array('G' => 'Grad', 'P' => 'P&oacute;s', 'T' => 'T&eacute;cnol.', 'S' => 'Mes. Prof.');
		echo ($tp == 'tudo') ? '<h2>Oferecimentos ('.$total['oferecimentos'].'):</h2>' : '<span class="cabecalho_resultados_busca">Exibindo resultados '.($sta['oferecimentos']+1).' - '.$fim['oferecimentos'].' de '.$total['oferecimentos'].' ('.$tempo.' segundos)</span>';
?>
<table border="1" width="100%" class="tabela_busca">
	<tr>
		<th align='center'>N&iacute;vel</th>
		<th align='center'>Sigla e Turma</th>
		<th align='center'>Nome</th>
		<th align='center'>Professor</th>
		<th align='center'>Per&iacute;odo</th>
		<th align='center' width='5%'>Vagas</th>
		<th align='center' width='5%'>Alunos</th>
		<th align='center' width='10%'>Situa&ccedil;&atilde;o</th>
	</tr>
<?php
		foreach($Oferecimentos as $Oferecimento) {
			$vagas = $Oferecimento->getVagas();
			$matriculados = $Oferecimento->Matriculados();
			$nivel_of = $Oferecimento->getDisciplina()->getNivel(false);
			$nivel_of = ($nivel_of != null) ? $niveis_oferecimentos[$nivel_of] : '?';
			if($Oferecimento->getFechado())
				$situacao = "Fechada";
			elseif($matriculados >= $vagas)
				$situacao = "Lotada";
			else
				$situacao = "";
?>
	<tr>
		<td><?= $nivel_of; ?></td>
		<td><a href="<?= CONFIG_URL; ?>oferecimento/<?= $Oferecimento->getID(); ?>/"><?= $Oferecimento->getDisciplina(true)->getSigla(true)." ".$Oferecimento->getTurma(true); ?></a></td>
		<td><a href="<?= CONFIG_URL; ?>oferecimento/<?= $Oferecimento->getID(); ?>/"><?= $Oferecimento->getDisciplina(true)->getNome(true); ?></a></td>
		<td><?= ($Oferecimento->getProfessor(false) !== null) ? '<a href="'.CONFIG_URL.'perfil/?professor='.$Oferecimento->getProfessor()->getID().'">'.$Oferecimento->getProfessor(true)->getNome(true).'</a>' : 'Desconhecido'; ?></td>
		<td><?= $Oferecimento->getPeriodo(true)->getNome(true); ?></td>
		<td><?= $vagas; ?></td>
		<td><?= $matriculados; ?></td>
		<td><?= $situacao; ?></td>
	</tr>
<?php
		}
?>

</table>
	<?= (($tp == 'tudo') && ($total['oferecimentos'] > $qts['oferecimentos'])) ? '<a href="#tab_oferecimentos" id="ir_tab_oferecimentos" class="ir_tab">Mais Oferecimentos...</a>' : $paginas; ?>
<br />
<?php
	}
} if($qts['salas'] != 0) {
	if($total['salas'] == 0) {
		if($tp != 'tudo')
			echo '<br />Nenhum resultado encontrado!';
	} else {
		echo ($tp == 'tudo') ? '<h2>Salas ('.$total['salas'].'):</h2>' : '<span class="cabecalho_resultados_busca">Exibindo resultados '.($sta['salas']+1).' - '.$fim['salas'].' de '.$total['salas'].' ('.$tempo.' segundos)</span>';
?>
<table border="1" width="100%" class="tabela_busca">
	<tr>
		<th align="center" colspan="4">Sala</th>
	</tr>
<?php
		foreach($Salas as $s => $Sala) {
			if($s % 4 == 0)
				echo "	<tr>";
?>
		<td width="25%"><a href="<?= CONFIG_URL; ?>sala/<?= $Sala->getNome(true); ?>/"><?= $Sala->getNome(true); ?></a></td>
<?php
		if($s % 4 == 3)
			echo "	</tr>";
		}
		for(;$s % 4 != 3; $s++)
			echo '		<td width="25%">&nbsp;</td>';
?>

</table>
		<?= (($tp == 'tudo') && ($total['salas'] > $qts['salas'])) ? '<a href="#tab_salas" id="ir_tab_salas" class="ir_tab">Mais Salas...</a>' : $paginas; ?>
<br />
<?php
	}
}
?>
