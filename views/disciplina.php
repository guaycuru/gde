<?php

namespace GDE;

if(isset($_GET['r']))
	define("NO_HTML", true);

// ToDo: Titulo mais informativo
define('TITULO', 'Disciplina');
require_once('../common/common.inc.php');

if(empty($_GET['id']))
	die("Cad&ecirc; o ID da Disciplina?".$FIM);

$Disciplina = Disciplina::Load($_GET['id']);
if($Disciplina->getId() == null)
	die("Disciplina não encontrada!".$FIM);

$cursada = $_Usuario->Eliminou($Disciplina);
$eliminada = $_Usuario->Eliminada($Disciplina, true, true);

if(isset($_GET['m'])) {
	if($Disciplina->getCreditos() != -1) {
		if($cursada || $eliminada) {
			$tp_cursada = 'Sim';
			if($eliminada->getProficiencia())
				$tp_cursada .= ' (Profici&ecirc;ncia)';
			if($eliminada->getParcial())
				$tp_cursada .= ' (Parcialmente)';
			if($cursada[1]) {
				$cursadas = array(array());
				foreach($cursada[0] as $crs)
					$cursadas[0][] = $crs[0];
				$tp_cursada .= ' (Por Equival&ecirc;ncia: '.Disciplina::Formata_Conjuntos($cursadas).')';
			}
		} else
			$tp_cursada = 'N&atilde;o';
	}
	exit($tp_cursada);
}

?>
<script type="text/javascript">
	// <![CDATA[
	var carregou_oferecimentos = false;

	var Atualizar_Oferecimentos = function(periodo) {
		if(!periodo)
			periodo = '';
		$("#tab_oferecimentos").Carregando('Carregando Oferecimentos...');
		$.post('<?= CONFIG_URL; ?>ajax/oferecimentos.php', {p: periodo, sigla: '<?= $Disciplina->getSigla(true); ?>'}, function(data) {
			if(data)
				$("#tab_oferecimentos").html(data);
		});
	};

	$(document).ready(function() {
		$('#periodo_horario').live('change', function() {
			Atualizar_Oferecimentos($(this).val());
		});

		$("#tabs").tabs({
			show: function(event, ui) {
				if((ui.panel.id == 'tab_oferecimentos') && (!carregou_oferecimentos)) {
					Atualizar_Oferecimentos();
					carregou_oferecimentos = true;
				}
			},
			select: function(event, ui) {
				window.location.hash = ui.tab.hash;
			}
		});
		Tamanho_Abas('tabs');
		$("input[name='eliminada']").click(function() {
			//opcao = $("input[name='eliminada']:checked").val();
			opcao = $(this).val();
			if(opcao == 1) // Normalmente
				$.post('<?= CONFIG_URL; ?>ajax/disciplina.php', {id: '<?= $Disciplina->getId(); ?>', e: '1', a: '0', r: '0'}, function(data) {
					<?php if (!isset($_GET['v'])) { ?>
					$.guaycuru.confirmacao("<i><?= $Disciplina->getSigla(true) ?></i> foi eliminada normalmente do seu curr&iacute;culo!", null);
					<?php } else ?>
					$.guaycuru.confirmacao(false);
				});
			else if(opcao == 2) // Parcialmente
				$.post('<?= CONFIG_URL; ?>ajax/disciplina.php', {id: '<?= $Disciplina->getId(); ?>', e: '1', a: '1', r: '0'}, function(data) {
					<?php if (!isset($_GET['v'])) { ?>
					$.guaycuru.confirmacao("<i><?= $Disciplina->getSigla(true) ?></i> foi eliminada parcialmente do seu curr&iacute;culo!", null);
					<?php } else ?>
					$.guaycuru.confirmacao(false);
				});
			else if(opcao == 3) // Proficiencia
				$.post('<?= CONFIG_URL; ?>ajax/disciplina.php', {id: '<?= $Disciplina->getId(); ?>', e: '1', a: '0', r: '1'}, function(data) {
					<?php if (!isset($_GET['v'])) { ?>
					$.guaycuru.confirmacao("<i><?= $Disciplina->getSigla(true) ?></i> foi eliminada por profici&ecirc;ncia do seu curr&iacute;culo!", null);
					<?php } else ?>
					$.guaycuru.confirmacao(false);
				});
			else // Nenhuma das Anteriores
				$.post('<?= CONFIG_URL; ?>ajax/disciplina.php', {id: '<?= $Disciplina->getId(); ?>', e: '0'}, function(data) {
					$.guaycuru.confirmacao("<i><?= $Disciplina->getSigla(true) ?></i> foi des-eliminada do seu curr&iacute;culo!", null);
				});
		});
	});
	// ]]>
</script>

<div id="coluna_esquerda_wrapper">
	<div id="coluna_esquerda">
		<div id="perfil_cabecalho">
			<div id="perfil_mensagem_botoes">
				<div id="perfil_cabecalho_nome"><?=$Disciplina->getSigla(true); ?> - <?=$Disciplina->getNome(true); ?></div>
			</div>
		</div>
		<div class="tip" id="disciplina_tip">Dica: Para acessar diretamente a p&aacute;gina desta disciplina use <span class="link"><a href="http://gde.ir/d/<?= $Disciplina->getId(); ?>">http://gde.ir/d/<?= $Disciplina->getId(); ?></a></span></div>
		<div id="perfil_abas">
			<div id="tabs">
				<ul>
					<li><a href="#tab_informacoes" class="ativo">Informa&ccedil;&otilde;es</a></li>
					<li><a href="#tab_curso">Curso</a></li>
					<li><a href="#tab_oferecimentos">Oferecimentos</a></li>
					<!-- <li><a href="#tab_professores">Professores</a></li> -->
				</ul>
				<div id="tab_informacoes" class="tab_content">
					<table id="tabela_informacoes" cellspacing="0" class="tabela_bonyta_branca">
						<tr>
							<td width="25%"><b>Sigla:</b></td>
							<td><?= ((isset($_GET['of']))?'<a href="'.CONFIG_URL.'disciplina/'.$Disciplina->getId().'">':null).$Disciplina->getSigla(true).((isset($_GET['of']))?'</a>':null); ?></td>
						</tr>
						<tr>
							<td width="25%"><b>Nome:</b></td>
							<td><?=$Disciplina->getNome(true); ?></td>
						</tr>
						<tr>
							<td width="25%"><b>N&iacute;vel:</b></td>
							<td><?=$Disciplina->getNivel(true); ?></td>
						</tr>
						<?php if($Disciplina->getInstituto(false) !== null) { ?>
							<tr>
								<td width="25%"><b>Instituto:</b></td>
								<td><?=$Disciplina->getInstituto(true)->getNome(true); ?> (<?=$Disciplina->getInstituto(true)->getSigla(true); ?>)</td>
							</tr>
						<?php } if($Disciplina->getCreditos(false) != -1) { ?>
							<tr>
								<td width="25%"><b>Cr&eacute;ditos:</b></td>
								<td><?=$Disciplina->getCreditos(true); ?></td>
							</tr>
							<tr>
								<td width="25%"><b>Oferecida Em:</b></td>
								<td><?=$Disciplina->getPeriodicidade(true); ?></td>
							</tr>
							<tr>
								<td width="25%"><b>Pr&eacute;-Requisitos:</b></td>
								<td><?=$Disciplina->getPre_Requisitos($_Usuario, true); ?></td>
							</tr>
							<tr>
								<td width="25%"><b>Contida Em:</b></td>
								<td><?=$Disciplina->Equivalencias(true); ?></td>
							</tr>
						<?php } ?>
						<tr>
							<td width="25%"><b>Ementa:</b></td>
							<td><?=$Disciplina->getEmenta(true); ?></td>
						</tr>
						<?php if(isset($_GET['of'])) { ?>
							<tr>
								<td colspan="2" align="center"><a href="<?= CONFIG_URL; ?>disciplina/<?= $Disciplina->getId(); ?>">Mais Informa&ccedil;&otilde;es da Disciplina</a></td>
							</tr>
						<?php } ?>
					</table>
				</div>
				<div id="tab_curso" class="tab_content">
					<table cellspacing="0" class="tabela_bonyta_branca">
						<?php if($Disciplina->getCreditos() != -1) { ?>
							<tr>
								<td width="25%"><b>Vezes Cursada:</b></td>
								<td><?=$Disciplina->getCursacoes(true); ?> desde 2007</td>
							</tr>
							<tr>
								<td width="25%"><b>Reprova&ccedil;&otilde;es:</b></td>
								<td><?=$Disciplina->getReprovacoes(true); ?> (<?= ($Disciplina->getCursacoes() > 0) ? number_format(($Disciplina->getReprovacoes() / $Disciplina->getCursacoes())*100, 2) : '-'; ?>%) (Valor estimado)</td>
							</tr>
							<tr>
								<td width="25%"><b>Desist&ecirc;ncias:</b></td>
								<td><?=$Disciplina->Desistencias(true); ?></td>
							</tr>
							<tr>
								<td width="25%" id="eliminada_td"><b>Eliminada:</b></td>
								<td>
									<?php
									if(($cursada !== false) && ($cursada[1] === true)) {
										$cursadas = array(array());
										foreach($cursada[0] as $crs)
											$cursadas[0][] = $crs[0];
										echo 'Por Equival&ecirc;ncia: '.Disciplina::Formata_Conjuntos($cursadas).'<br />';
									}
									?>
									<input type="radio" name="eliminada" value="1" id="eliminada_1"<?php if(($cursada !== false) && ($cursada[1] === false) && ($cursada[0][0][1] === false)) echo " checked=\"checked\""; ?> /><label for="eliminada_1">Normalmente (Cursei e passei com Nota >= 5,0)</label><br />
									<input type="radio" name="eliminada" value="2" id="eliminada_2"<?php if(($cursada === false) && ($eliminada !== false)) echo " checked=\"checked\""; ?> /><label for="eliminada_2">Parcialmente (Cursei, n&atilde;o passei, mas tive Nota >= 3,0)</label><br />
									<input type="radio" name="eliminada" value="3" id="eliminada_3"<?php if(($cursada !== false) && ($cursada[1] === false) && ($cursada[0][0][1] !== false)) echo " checked=\"checked\""; ?> /><label for="eliminada_3">Por Profici&ecirc;ncia (Passei em teste de Profici&ecirc;ncia)</label><br />
									<input type="radio" name="eliminada" value="0" id="eliminada_0"<?php if((($cursada === false) && ($eliminada === false)) || (($cursada !== false) && ($cursada[1] === true))) echo " checked=\"checked\""; ?> /><label for="eliminada_0">Nenhuma das Anteriores</label><br />
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
				<div id="tab_oferecimentos" class="tab_content">
					<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Oferecimentos...
				</div>
				<!-- <div id="tab_professores" class="tab_content">
					<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Professores...
				</div> -->
			</div>
		</div>
	</div>
</div>

<?= $FIM; ?>
