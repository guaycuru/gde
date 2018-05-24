<?php

namespace GDE;

define('TITULO', 'Busca');

require_once('../common/common.inc.php');

if(isset($_GET['buscar']) || isset($_GET['q'])) {
	$buscar = true;
	if(isset($_GET['q'])) { // Busca Simples
		$simples = true;
		$q = Util::Limpa_Busca($_GET['q']);
		$tp = 'tudo';
	} else { // Busca Avancada
		$simples = false;
		$q = null;
		$tp = (!empty($_GET['t'])) ? htmlspecialchars($_GET['t']) : 'tudo';
	}
} else {
	$buscar = false;
	$simples = false;
}

function Select_Organizar($tab, $opcoes, $simples) {
	if($simples === false)
		unset($opcoes[0]);
	$tipo = ($simples) ? '_s' : '_a';
	if(!isset($_GET['ord'][$tab.$tipo]))
		$_GET['ord'][$tab.$tipo] = 0;
	$ret = '<select name="ord['.$tab.$tipo.']">';
	foreach($opcoes as $k => $opcao)
		$ret .= '<option value="'.$k.'"'.(($_GET['ord'][$tab.$tipo] == $k) ? 'selected="selected"' : '').'>'.$opcao.'</option>';
	$ret .= '</select>';
	return $ret;
}

function Select_Ordem($tab, $simples) {
	$tipo = ($simples) ? '_s' : '_a';
	if(!isset($_GET['em'][$tab.$tipo]))
		$_GET['em'][$tab.$tipo] = ($simples) ? 2 : 1;
	$ret = '<select name="em['.$tab.$tipo.']"><option value="1"'.(($_GET['em'][$tab.$tipo] == 1) ? 'selected="selected"' : '').'>Crescente</option><option value="2"'.(($_GET['em'][$tab.$tipo] == 2) ? 'selected="selected"' : '').'>Decrescente</option></select>';
	return $ret;
}

$niveis = Aluno::Listar_Niveis_Grad();
$lista_niveis = "<option value=\"\">Indiferente</option>";
foreach($niveis as $c => $d)
	$lista_niveis .= "<option value=\"".$c."\"".(((isset($_GET['nivel'])) && ($_GET['nivel'] == $c))?" selected=\"selected\"":null).">".$d." (".$c.")</option>";

$niveis_pos = Aluno::Listar_Niveis_Pos();
$lista_niveis_pos = "<option value=\"\">Indiferente</option>";
foreach($niveis_pos as $c => $d)
	$lista_niveis_pos .= "<option value=\"".$c."\"".(((isset($_GET['nivel_pos'])) && ($_GET['nivel_pos'] == $c))?" selected=\"selected\"":null).">".$d." (".$c.")</option>";

$Cursos = Curso::Listar(array('G', 'T'), "curso ASC");
$lista_cursos = "<option value=\"-1\">Indiferente</option>";
foreach($Cursos as $Curso)
	$lista_cursos .= "<option value=\"".$Curso->getID()."\"".(((isset($_GET['curso'])) && ($_GET['curso'] == $Curso->getID()))?" selected=\"selected\"":null).">".$Curso->getNome(true)." (".$Curso->getNumero(true).")</option>";

$Cursos_Pos = Curso::Listar(array('M', 'D'), "curso ASC");
$lista_cursos_pos = "<option value=\"-1\">Indiferente</option>";
foreach($Cursos_Pos as $Curso_Pos)
	$lista_cursos_pos .= "<option value=\"".$Curso_Pos->getID()."\"".(((isset($_GET['curso_pos'])) && ($_GET['curso_pos'] == $Curso_Pos->getID()))?" selected=\"selected\"":null).">".$Curso_Pos->getNome(true)." (".$Curso_Pos->getNumero(true).")</option>";

$periodos = "";
$Periodos = Periodo::Listar();
foreach($Periodos as $Periodo)
	$periodos .= '<option value="'.$Periodo->getPeriodo(true).'"'.(((isset($_GET['periodo'])) && ($_GET['periodo'] == $Periodo->getPeriodo(false)))?' selected="selected"':null).'>'.$Periodo->getNome(false).'</option>';

$periodicidades = "<option value=\"\">Indiferente</option>";
foreach(Disciplina::Listar_Periodicidades() as $p => $periodicidade) {
	if($p == 0)
		continue;
	$periodicidades .= '<option value="'.$p.'"'.(((isset($_GET['periodicidade'])) && ($_GET['periodicidade'] == $p))?' selected':null).'>'.$periodicidade.'</option>';
}

$nome_dias = array(2 => 'Segunda-Feira', 'Ter&ccedil;a-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'S&aacute;bado');
$dias = "<option value=\"\">Indiferente</option>";
for($i = 2; $i < 8; $i++)
	$dias .= "<option value=\"".$i."\"".(((isset($_GET['dia'])) && ($_GET['dia']==$i))?' selected="selected"':null).">".$nome_dias[$i]."</option>";

$horarios = "<option value=\"\">Indiferente</option>";
for($i = 7; $i < 23; $i++)
	$horarios .= "<option value=\"".$i."\"".(((isset($_GET['horario'])) && ($_GET['horario']==$i))?' selected="selected"':null).">".$i.":00</option>";

$institutos = "<option value=\"0\">Indiferente</option>";
$Institutos = Instituto::FindBy();
foreach($Institutos as $Instituto)
	$institutos .= '<option value="'.$Instituto->getID().'"'.(((isset($_GET['instituto'])) && ($_GET['instituto'] == $Instituto->getID()))?' selected':null).'>'.$Instituto->getNome(true).'</option>';

$disciplina_niveis = "<option value=\"0\">Indiferente</option>";
foreach(Disciplina::Listar_Niveis() as $i => $nivel)
	$disciplina_niveis .= "<option value=\"".$i."\"".(((isset($_GET['nivel'])) && ($_GET['nivel'] == $i))?" selected=\"selected\"":null).">".$nivel."</option>";

$resultados_pagina = "";
for($i = 0; $i < 4; $i++)
	if ($i == 0)
		$resultados_pagina .= "<option value=\"10\"".(((isset($_GET['resultados_pagina'])) && ($_GET['resultados_pagina']==10))?' selected="selected"':null).">10</option>";
	else if ($i == 1)
		$resultados_pagina .= "<option value=\"20\"".((((isset($_GET['resultados_pagina'])) && ($_GET['resultados_pagina']==20)) || (!isset($_GET['resultados_pagina'])))?' selected="selected"':null).">20</option>";
	else if ($i == 2)
		$resultados_pagina .= "<option value=\"50\"".(((isset($_GET['resultados_pagina'])) && ($_GET['resultados_pagina']==50))?' selected="selected"':null).">50</option>";
	else if ($i == 3)
		$resultados_pagina .= "<option value=\"100\"".(((isset($_GET['resultados_pagina'])) && ($_GET['resultados_pagina']==100))?' selected="selected"':null).">100</option>";

$estados_civis = "";
foreach(Usuario::Listar_Estados_Civis() as $n => $e)
	$estados_civis .= "<option value=\"".$n."\"".(((isset($_GET['relacionamento'])) && ($_GET['relacionamento'] == $n))?" selected=\"selected\"":null).">".$e."</option>";

?>
<script type="text/javascript">
	// <![CDATA[
	var buscar = <?= ($buscar) ? 'true' : 'false'; ?>;
	atualiza_modalidades = function() {
		$('#select_modalidade').addClass("ac_loading");
		$('#select_modalidade').load('<?= CONFIG_URL; ?>ajax/modalidades.php?c='+$('#aluno_curso').val()+'&s=<?= (isset($_GET['modalidade']))?$_GET['modalidade']:null; ?>&o=0', {}, function(){$('#select_modalidade').removeClass("ac_loading");});
	};
	atualiza_turmas = function(numero, sigla, turma) {
		if(sigla == "")
			return;
		sigla = sigla.replace(" ", "_");
		var periodo = $("#aluno_periodo").val();
		$.post('<?= CONFIG_URL; ?>ajax/oferecimentos.php', {tp: 3, p: periodo, q: sigla, s: turma}, function(data) {
			$("#cursando_turma_"+numero).html(data);
		});
	};
	var conta_cursando = 0;
	mais_cursando = function(sigla, turma) {
		var numero = conta_cursando;
		if(conta_cursando > 0)
			$("#td_cursando").append(' <span class="tipo_cursando" style="text-decoration: underline; cursor: pointer;">'+$("#cursando_tipo").val()+'</span>');
		$("#td_cursando").append(' <input type="text" id="cursando_sigla_'+conta_cursando+'" name="cursando_sigla[]" class="sigla" size="8" maxlength="5" style="text-transform: uppercase;" /><select id="cursando_turma_'+conta_cursando+'" name="cursando_turma[]"></select>');
		$("#cursando_sigla_"+conta_cursando).Autocompletar({
			json: '<?= CONFIG_URL; ?>ajax/disciplinas.php',
			data: {tp: 1},
			delay: 200,
			instantaneo: true,
			obrigatorio: true,
			maxHeight: '350px',
			select: function(event, ui) { atualiza_turmas(numero, ui.item.raw, turma); },
			create: function(event, ui) { $("ul.ui-autocomplete").not("div.gde_jquery_ui > ul").wrap('<div class="gde_jquery_ui" />'); }
		});
		conta_cursando++;
	};
	var pga = [];
	<?php if($buscar) { ?>
	var simples = <?= ($simples) ? 'true' : 'false'?>;
	var hasho = (window.location.hash) ? window.location.hash.replace('#', '').split('$') : ['tab_tudo', 0];
	var lod = [];
	if(hasho[1])
		pga[hasho[0]] = hasho[1];
	var Buscar = function(hash, pg) {
		var hash = (window.location.hash) ? window.location.hash.replace('#', '').split('$') : ['tab_tudo', 0];
		tab = hash[0];
		pg = hash[1];
		if(!$("#tabs > div#"+tab).is(":visible"))
			$tabs.tabs('select', tab);
		if(!pg)
			pg = 1;
		if((simples || tab == '<?= $tp; ?>') && (!lod[tab] || pga[tab] != pg)) {
			$('#'+tab+'_resultados').Carregando('Carregando Resultados...');
			if(simples)
				var params = '<?= str_replace("'", "\\'", $_SERVER['QUERY_STRING']); ?>&t='+tab+'&p='+pg;
			else
				var params = '<?= str_replace("'", "\\'", $_SERVER['QUERY_STRING']); ?>&p='+pg;
			$.post('<?= CONFIG_URL; ?>ajax/busca.php', params, function(data) {
				$('#'+tab+'_resultados').html(data);
			});
			pga[tab] = pg;
			lod[tab] = true;
		}
	}
	<?php } ?>
	var $tabs;
	$(document).ready(function() {
		$tabs = $("#tabs").tabs({
			select: function(event, ui) {
				var tab = ui.tab.hash.replace('#', '');
				window.location.hash = (pga[tab] && pga[tab] != 1 && tab != 'tab_tudo') ? tab+'$'+pga[tab] : tab;
			}
		});
		Tamanho_Abas('tabs');
		$(window).resize(function() { Tamanho_Abas('tabs'); });
		$("input.tipo_busca").change(function() {
			var name = $(this).attr('name').replace('tipo_', '');
			if($(this).val() == 's') {
				$("#"+name+"_a").hide('slow');
				$("#"+name+"_s").show('slow');
			} else {
				$("#"+name+"_s").hide('slow');
				$("#"+name+"_a").show('slow');
			}
		});
		$('#aluno_curso').change(atualiza_modalidades);
		$('span.tipo_cursando').live('click', function() {
			tipo = ($("#cursando_tipo").val() == "E") ? "OU" : "E";
			$("span.tipo_cursando").text(tipo);
			$("#cursando_tipo").val(tipo);
		});
		$("#oferecimento_sigla").Autocompletar({
			json: '<?= CONFIG_URL; ?>ajax/disciplinas.php',
			data: {tp: 1},
			delay: 100,
			minLength: 2,
			highlight: true,
			instantaneo: true,
			obrigatorio: false,
			maxHeight: '350px',
			create: function(event, ui) { $("ul.ui-autocomplete").not("div.gde_jquery_ui > ul").wrap('<div class="gde_jquery_ui" />'); }
		});
		$("#oferecimento_professor").Autocompletar({
			json: '<?= CONFIG_URL; ?>ajax/professores.php',
			delay: 100,
			valField: 'nome',
			highlight: true,
			instantaneo: true,
			obrigatorio: false,
			maxHeight: '360px',
			create: function(event, ui) { $("ul.ui-autocomplete").not("div.gde_jquery_ui > ul").wrap('<div class="gde_jquery_ui" />'); }
		});
		<?php
		$adicionadas_cursando = 0;
		if((!empty($_GET['cursando_sigla'])) && (!empty($_GET['cursando_turma']))) {
		foreach($_GET['cursando_sigla'] as $k => $sigla)
		if($sigla != '') {
		$adicionadas_cursando++;
		?>
		mais_cursando('<?= $sigla; ?>', '<?= $_GET['cursando_turma'][$k]; ?>');
		<?php
		}
		}
		if($adicionadas_cursando == 0) { ?>
		mais_cursando('', '');
		<?php } ?>
		atualiza_modalidades();
		<?php if($buscar) { ?>
		Buscar();
		$(window).hashchange(function() {
			Buscar();
		});
		<?php } else { ?>
		$(window).hashchange(function() {
			var hash = (window.location.hash) ? window.location.hash.replace('#', '').split('$') : ['tab_tudo', 0];
			tab = hash[0];
			$tabs.tabs('select', tab);
		});
		<?php } ?>
	});
	// Quem diria: um hack pro CHROME funcionar direito...
	$(window).load(function() { Tamanho_Abas('tabs'); });
	// ]]>
</script>
<div class="tip" id="busca_tip">Dica: Para fazer uma busca rapidamente, use <span class="link">http://gde.ir/b/BUSCA</span>, por exemplo, <span class="link"><a href="http://gde.ir/b/Batata">http://gde.ir/b/Batata</a></span></div>
<div id="tabs" class="conteudo_em_tabs">
	<ul>
		<li><a href="#tab_tudo">Tudo (<span id="resultados_tudo">?</span>)</a></li>
		<li><a href="#tab_alunos">Alunos (<span id="resultados_alunos">?</span>)</a></li>
		<li><a href="#tab_professores">Professores (<span id="resultados_professores">?</span>)</a></li>
		<li><a href="#tab_disciplinas">Disciplinas (<span id="resultados_disciplinas">?</span>)</a></li>
		<li><a href="#tab_oferecimentos">Oferecimentos (<span id="resultados_oferecimentos">?</span>)</a></li>
		<li><a href="#tab_salas">Salas (<span id="resultados_salas">?</span>)</a></li>
	</ul>
	<div id="tab_tudo" class="tab_content">
		<div class="form_busca">
			<form method="get" action="<?= CONFIG_URL; ?>busca/">
				<input type="text" class="busca_simples" name="q" value="<?php if($buscar && $simples) echo htmlspecialchars($q); ?>" /> <input type="submit" class="botao_consultar botao_busca_simples" value=" " alt="Consultar" />
			</form>
		</div>
		<div id="tab_tudo_resultados" class="busca_resultados"></div>
	</div>
	<div id="tab_alunos" class="tab_content">
		<div class="tipos_busca" >
			<input type="radio" class="tipo_busca" id="tipo_busca_alunos_s" name="tipo_busca_alunos" value="s"<?php if($simples) echo ' checked="checked"'; ?> /><label for="tipo_busca_alunos_s">Busca Simples</label> <input type="radio" class="tipo_busca" id="tipo_busca_alunos_a" name="tipo_busca_alunos" value="a"<?php if(!$simples) echo ' checked="checked"'; ?> /><label for="tipo_busca_alunos_a">Busca Avan&ccedil;ada</label><br />
		</div>
		<div id="busca_alunos_s" class="form_busca_simples"<?php if(!$simples) echo ' style="display: none;"'; ?>>
			<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_alunos">
				<div class="form_busca">
					<table border="0">
						<tr>
							<td>Buscar Por:</td>
							<td><input type="text" class="busca_simples" name="q" value="<?php if($buscar && $simples) echo htmlspecialchars($q); ?>" /></td>
						</tr>
						<tr>
							<td>Tipo de Resultado:</td>
							<td><select id="aluno_tp" name="tpres"><option value="1"<?= ((isset($_GET['tpres'])) && ($_GET['tpres'] == '1')) ? ' selected="selected"': null; ?>>Fotos</option><option value="2"<?= ((!isset($_GET['tpres'])) || ($_GET['tpres'] == '2')) ? ' selected="selected"': null; ?>>Lista</option></select></td>
						</tr>
						<tr>
							<td>Organizar por:</td>
							<td><?= Select_Organizar('alunos', Aluno::$ordens_nome, true); ?></td>
						</tr>
						<tr>
							<td>Em Ordem:</td>
							<td><?= Select_Ordem('alunos', true); ?></td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" class="botao_consultar botao_busca_simples" value=" " alt="Consultar" /></td>
						</tr>
					</table>
				</div>
			</form>
		</div>
		<div id="busca_alunos_a" class="form_busca_avancada"<?php if($simples) echo ' style="display: none;"'; ?>>
			<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_alunos">
				<input type="hidden" name="t" value="tab_alunos" />
				<input type="hidden" id="cursando_tipo" name="cursando_tipo" value="<?=((isset($_GET['cursando_tipo']))?htmlspecialchars($_GET['cursando_tipo']):"E"); ?>" />
				<table border="0">
					<tr>
						<td>Nome:</td>
						<td><input type="text" id="aluno_nome" name="nome" value="<?=((isset($_GET['nome']))?htmlspecialchars($_GET['nome']):null); ?>" /></td>
					</tr>
					<tr>
						<td>RA:</td>
						<td><input type="text" id="aluno_ra" name="ra" maxlength="6" value="<?=((isset($_GET['ra']))?htmlspecialchars($_GET['ra']):null); ?>" /></td>
					</tr>
					<tr>
						<td>N&iacute;vel Grad.:</td>
						<td><select id="aluno_nivel" name="nivel"><?=$lista_niveis;?></select></td>
					</tr>
					<tr>
						<td>Curso Grad.:</td>
						<td><select id="aluno_curso" name="curso"><?=$lista_cursos; ?></select></td>
					</tr>
					<tr>
						<td>Modalidade:</td>
						<td id="select_modalidade"><select id="aluno_modalidade" name="modalidade"><option value="">Indiferente</option></select></td>
					</tr>
					<tr>
						<td>Cursando <a href="#" onclick="mais_cursando('', ''); return false;">+</a>:</td>
						<td id="td_cursando"></td>
					</tr>
					<tr>
						<td>Cursando Em:</td>
						<td><select id="aluno_periodo" name="periodo"><?=$periodos; ?></select></td>
					</tr>
					<tr>
						<td>N&iacute;vel P&oacute;s:</td>
						<td><select id="aluno_nivel_pos" name="nivel_pos"><?=$lista_niveis_pos;?></select></td>
					</tr>
					<tr>
						<td>Curso P&oacute;s:</td>
						<td><select id="aluno_curso_pos" name="curso_pos"><?=$lista_cursos_pos; ?></select></td>
					</tr>
					<tr>
						<td>Modalidade P&oacute;s:</td>
						<td><input type="text" name="modalidade_pos" value="<?=((isset($_GET['modalidade_pos']))?htmlspecialchars($_GET['modalidade_pos']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Cadastro no GDE:</td>
						<td><select id="aluno_gde" name="gde"><option value="">Indiferente</option><option value="t"<?= ((isset($_GET['gde'])) && ($_GET['gde'] == 't')) ? ' selected="selected"': null; ?>>Sim</option><option value="f"<?= ((isset($_GET['gde'])) && ($_GET['gde'] == 'f')) ? ' selected="selected"': null; ?>>N&atilde;o</option></select></td>
					</tr>
					<tr>
						<td>Sexo:</td>
						<td><select id="aluno_sexo" name="sexo"><option value="">Indiferente</option><option value="f"<?= ((isset($_GET['sexo'])) && ($_GET['sexo'] == 'f')) ? ' selected="selected"': null; ?>>Feminino</option><option value="m"<?= ((isset($_GET['sexo'])) && ($_GET['sexo'] == 'm')) ? ' selected="selected"': null; ?>>Masculino</option></select></td>
					</tr>
					<tr>
						<td>Relacionamento:</td>
						<td><select id="aluno_relacionamento" name="relacionamento"><?=$estados_civis; ?></select></td>
					</tr>
					<tr>
						<td>Cidade:</td>
						<td><input type="text" id="aluno_cidade" name="cidade" value="<?=((isset($_GET['cidade']))?htmlspecialchars($_GET['cidade']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Estado:</td>
						<td><input type="text" id="aluno_estado" name="estado" value="<?=((isset($_GET['estado']))?htmlspecialchars($_GET['estado']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Tipo de Resultado:</td>
						<td><select id="aluno_tp" name="tpres"><option value="1"<?= ((!isset($_GET['tpres'])) || ($_GET['tpres'] == '1')) ? ' selected="selected"': null; ?>>Fotos</option><option value="2"<?= ((isset($_GET['tpres'])) && ($_GET['tpres'] == '2')) ? ' selected="selected"': null; ?>>Lista</option></select></td>
					</tr>
					<tr>
						<td>Organizar por:</td>
						<td><?= Select_Organizar('alunos', Aluno::$ordens_nome, false); ?></td>
					</tr>
					<tr>
						<td>Em Ordem:</td>
						<td><?= Select_Ordem('alunos', false); ?></td>
					</tr>
					<tr>
						<td>Resultados por p&aacute;gina</td>
						<td><select name="resultados_pagina" id="resultados_pagina">
								<?= $resultados_pagina ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" name="buscar" class="botao_consultar" value=" " alt="Consultar" /> <input type="button" class="botao_limpar" value=" " alt="Limpar" onclick="document.location='<?= CONFIG_URL; ?>busca/#tab_alunos';" /></td>
					</tr>
				</table>
			</form>
		</div>
		<div id="tab_alunos_resultados" class="busca_resultados"></div>
	</div>
	<div id="tab_professores" class="tab_content">
		<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_professores">
			<div class="form_busca">
				<table border="0">
					<tr>
						<td>Buscar Por:</td>
						<td><input type="text" class="busca_simples" name="q" value="<?php if($buscar && $simples) echo htmlspecialchars($q); ?>" /></td>
					</tr>
					<tr>
						<td>Organizar por:</td>
						<td><?= Select_Organizar('professores', Professor::$ordens_nome, true); ?></td>
					</tr>
					<tr>
						<td>Em Ordem:</td>
						<td><?= Select_Ordem('professores', true); ?></td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" class="botao_consultar botao_busca_simples" value=" " alt="Consultar" /></td>
					</tr>
				</table>
			</div>
		</form>
		<div id="tab_professores_resultados" class="busca_resultados"></div>
	</div>
	<div id="tab_disciplinas" class="tab_content">
		<div class="tipos_busca">
			<input type="radio" class="tipo_busca" id="tipo_busca_disciplinas_s" name="tipo_busca_disciplinas" value="s"<?php if($simples) echo ' checked="checked"'; ?> /><label for="tipo_busca_disciplinas_s">Busca Simples</label> <input type="radio" class="tipo_busca" id="tipo_busca_disciplinas_a" name="tipo_busca_disciplinas" value="a"<?php if(!$simples) echo ' checked="checked"'; ?> /><label for="tipo_busca_disciplinas_a">Busca Avan&ccedil;ada</label><br />
		</div>
		<div id="busca_disciplinas_s" class="form_busca_simples"<?php if(!$simples) echo ' style="display: none;"'; ?>>
			<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_disciplinas">
				<div class="form_busca">
					<table border="0">
						<tr>
							<td>Buscar Por:</td>
							<td><input type="text" class="busca_simples" name="q" value="<?php if($buscar && $simples) echo htmlspecialchars($q); ?>" /></td>
						</tr>
						<tr>
							<td>Organizar por:</td>
							<td><?= Select_Organizar('disciplinas', Disciplina::$ordens_nome, true); ?></td>
						</tr>
						<tr>
							<td>Em Ordem:</td>
							<td><?= Select_Ordem('disciplinas', true); ?></td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" class="botao_consultar botao_busca_simples" value=" " alt="Consultar" /></td>
						</tr>
					</table>
				</div>
			</form>
		</div>
		<div id="busca_disciplinas_a" class="form_busca_avancada"<?php if($simples) echo ' style="display: none;"'; ?>>
			<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_disciplinas">
				<input type="hidden" name="t" value="tab_disciplinas" />
				<table border="0">
					<tr>
						<td>Sigla:</td>
						<td><input type="text" id="disciplina_sigla" name="sigla" maxlength="5" value="<?=((isset($_GET['sigla']))?htmlspecialchars($_GET['sigla']):null); ?>" style="text-transform: uppercase;" /></td>
					</tr>
					<tr>
						<td>Nome:</td>
						<td><input type="text" id="disciplina_nome" name="nome" value="<?=((isset($_GET['nome']))?htmlspecialchars($_GET['nome']):null); ?>" /></td>
					</tr>
					<tr>
						<td>N&iacute;vel:</td>
						<td><select id="disciplina_nivel" name="nivel"><?= $disciplina_niveis; ?></select></td>
					</tr>
					<tr>
						<td>Instituto:</td>
						<td><select id="disciplina_instituto" name="instituto"><?= $institutos; ?></select></td>
					</tr>
					<tr>
						<td>Cr&eacute;ditos:</td>
						<td><input type="text" id="disciplina_creditos" name="creditos" maxlength="2" value="<?=((isset($_GET['creditos']))?htmlspecialchars($_GET['creditos']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Oferecida Em:</td>
						<td><select id="disciplina_periodicidade" name="periodicidade"><?=$periodicidades; ?></select></td>
					</tr>
					<tr>
						<td>Ementa</td>
						<td><input type="text" id="disciplina_ementa" name="ementa" value="<?=((isset($_GET['ementa']))?htmlspecialchars($_GET['ementa']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Organizar por:</td>
						<td><?= Select_Organizar('disciplinas', Disciplina::$ordens_nome, false); ?></td>
					</tr>
					<tr>
						<td>Em Ordem:</td>
						<td><?= Select_Ordem('disciplinas', false); ?></td>
					</tr>
					<tr>
						<td>Resultados por p&aacute;gina</td>
						<td><select name="resultados_pagina" id="resultados_pagina">
								<?= $resultados_pagina ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" name="buscar" class="botao_consultar" value=" " alt="Consultar" /> <input type="button" class="botao_limpar" value=" " alt="Limpar" onclick="document.location='<?= CONFIG_URL; ?>busca/#tab_disciplinas';" /></td>
					</tr>
				</table>
			</form>
		</div>
		<div id="tab_disciplinas_resultados" class="busca_resultados"></div>
	</div>
	<div id="tab_oferecimentos" class="tab_content">
		<div class="tipos_busca">
			<input type="radio" class="tipo_busca" id="tipo_busca_oferecimentos_s" name="tipo_busca_oferecimentos" value="s"<?php if($simples) echo ' checked="checked"'; ?> /><label for="tipo_busca_oferecimentos_s">Busca Simples</label> <input type="radio" class="tipo_busca" id="tipo_busca_oferecimentos_a" name="tipo_busca_oferecimentos" value="a"<?php if(!$simples) echo ' checked="checked"'; ?> /><label for="tipo_busca_oferecimentos_a">Busca Avan&ccedil;ada</label><br />
		</div>
		<div id="busca_oferecimentos_s" class="form_busca_simples"<?php if(!$simples) echo ' style="display: none;"'; ?>>
			<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_oferecimentos">
				<div class="form_busca">
					<table border="0">
						<tr>
							<td>Buscar Por:</td>
							<td><input type="text" class="busca_simples" name="q" value="<?php if($buscar && $simples) echo htmlspecialchars($q); ?>" /></td>
						</tr>
						<tr>
							<td>Organizar por:</td>
							<td><?= Select_Organizar('oferecimentos', Oferecimento::$ordens_nome, true); ?></td>
						</tr>
						<tr>
							<td>Em Ordem:</td>
							<td><?= Select_Ordem('oferecimentos', true); ?></td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" class="botao_consultar botao_busca_simples" value=" " alt="Consultar" /></td>
						</tr>
					</table>
				</div>
			</form>
		</div>
		<div id="busca_oferecimentos_a" class="form_busca_avancada"<?php if($simples) echo ' style="display: none;"'; ?>>
			<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_oferecimentos">
				<input type="hidden" name="t" value="tab_oferecimentos" />
				<table border="0">
					<tr>
						<td>Per&iacute;odo:</td>
						<td><select id="oferecimento_periodo" name="periodo"><?=$periodos; ?></select></td>
					</tr>
					<tr>
						<td>Sigla:</td>
						<td><input type="text" id="oferecimento_sigla" name="sigla" value="<?=((isset($_GET['sigla']))?htmlspecialchars($_GET['sigla']):null); ?>" maxlength="5" style="text-transform: uppercase;" /></td>
					</tr>
					<tr>
						<td>Turma:</td>
						<td><input type="text" id="oferecimento_turma" name="turma" value="<?=((isset($_GET['turma']))?htmlspecialchars($_GET['turma']):null); ?>" /></td>
					</tr>
					<tr>
						<td>N&iacute;vel:</td>
						<td><select id="oferecimento_nivel" name="nivel"><?= $disciplina_niveis; ?></select></td>
					</tr>
					<tr>
						<td>Nome:</td>
						<td><input type="text" id="oferecimento_nome" name="nome" value="<?=((isset($_GET['nome']))?htmlspecialchars($_GET['nome']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Professor(a):</td>
						<td><input type="text" id="oferecimento_professor" name="professor" value="<?=((isset($_GET['professor']))?htmlspecialchars($_GET['professor']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Cr&eacute;ditos:</td>
						<td><input type="text" id="oferecimento_creditos" name="creditos" value="<?=((isset($_GET['creditos']))?htmlspecialchars($_GET['creditos']):null); ?>" /></td>
					</tr>
					<tr>
						<td>Instituto:</td>
						<td><select id="oferecimento_instituto" name="instituto"><?= $institutos; ?></select></td>
					</tr>
					<tr>
						<td>Dia:</td>
						<td><select id="oferecimento_dia" name="dia"><?= $dias; ?></select></td>
					</tr>
					<tr>
						<td>Hor&aacute;rio:</td>
						<td><select id="oferecimento_horario" name="horario"><?= $horarios; ?></select></td>
					</tr>
					<tr>
						<td>Sala:</td>
						<td><input type="text" id="oferecimento_sala" name="sala" maxlength="4" value="<?=((isset($_GET['sala']))?htmlspecialchars($_GET['sala']):null); ?>" style="text-transform: uppercase;" /></td>
					</tr>
					<tr>
						<td>Organizar por:</td>
						<td><?= Select_Organizar('oferecimentos', Oferecimento::$ordens_nome, false); ?></td>
					</tr>
					<tr>
						<td>Em Ordem:</td>
						<td><?= Select_Ordem('oferecimentos', false); ?></td>
					</tr>
					<tr>
						<td>Resultados por p&aacute;gina</td>
						<td><select name="resultados_pagina" id="resultados_pagina">
								<?= $resultados_pagina ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" name="buscar" class="botao_consultar" value=" " alt="Consultar" /> <input type="button" class="botao_limpar" value=" " alt="Limpar" onclick="document.location='<?= CONFIG_URL; ?>busca/#tab_oferecimentos';" /></td>
					</tr>
				</table>
			</form>
		</div>
		<div id="tab_oferecimentos_resultados" class="busca_resultados"></div>
	</div>
	<div id="tab_salas" class="tab_content">
		<form method="get" action="<?= CONFIG_URL; ?>busca/#tab_salas">
			<input type="hidden" name="ord[salas_s]" value="0" />
			<div class="form_busca">
				<table border="0">
					<tr>
						<td>Buscar Por:</td>
						<td><input type="text" class="busca_simples" name="q" value="<?php if($buscar && $simples) echo htmlspecialchars($q); ?>" /></td>
					</tr>
					<tr>
						<td>Em Ordem:</td>
						<td><?= Select_Ordem('salas', true); ?></td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" class="botao_consultar botao_busca_simples" value=" " alt="Consultar" /></td>
					</tr>
				</table>
			</div>
		</form>
		<div id="tab_salas_resultados" class="busca_resultados"></div>
	</div>
</div>

<?= $FIM; ?>
