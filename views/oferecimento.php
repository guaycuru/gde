<?php

namespace GDE;

if(isset($_GET['ax']))
	define("NO_HTML", true);

// ToDo: Titulo mais informativo
define('TITULO', 'Oferecimento');
require_once('../common/common.inc.php');

if(empty($_GET['id']))
	exit();

$Oferecimento = Oferecimento::Load($_GET['id']);
if($Oferecimento->getID() == null)
	die('Oferecimento inexistente...'.$FIM);

$Horario = $Oferecimento->Monta_Horario();

$oferecimento_pagina = $Oferecimento->getPagina();
$html_oferecimento_pagina = '';

if($oferecimento_pagina == null) {
	if(ColaboracaoOferecimento::Existe_Colaboracao($Oferecimento->getID(), 'pagina') == false) {
		$html_oferecimento_pagina = '<input id="pagina_valor" type="text" class="valor_colaborar"><a href="#" id="pagina_colaborar" class="link_colaborar">Colaborar</a>';
	} else {
		$html_oferecimento_pagina = 'Colaboracao pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.';
	}
} else
	$html_oferecimento_pagina = "<a href='".$oferecimento_pagina."' target='_blank'>".$oferecimento_pagina."</a>"; //.' <a href="#" id="pagina_reclamar" class="link_reclamar">Reclamar</a>';

?>
	<script type="text/javascript">
		// <![CDATA[
		var carregou_informacoes = false;
		var tipo = 1;
		var paginacao = 1;
		var colaborar_padrao = 'Colabore aqui';
		var id_oferecimento = '<?= $Oferecimento->getId(); ?>';

		var Informacoes = function() {
			$("#tab_disciplina").load('<?= CONFIG_URL; ?>disciplina/?id=<?= $Oferecimento->getDisciplina(true)->getId(); ?>&of #tabela_informacoes');
		};

		var Carrega = function(tp, pg) {
			$("#lista_alunos").Carregando();
			if(tp == 1) {
				$("#lista_alunos").attr("border", "0");
			} else {
				$("#lista_alunos").attr("border", "1");
			}
			tipo = tp;
			paginacao = pg;
			Lista_Alunos();
		};

		var Lista_Alunos = function() {
			$("#lista_alunos").Carregando();
			var filtro = (!$("#filtro_nome").hasClass('padrao'));
			var amigos = ($("#apenas_amigos").is(':checked') > 0)?'t':'f';
			$("#lista_alunos").load("<?= CONFIG_URL; ?>ajax/busca.php", {t: 'alunos', nome: ((filtro) ? $("#filtro_nome").val() : ''), amigos: amigos, id_oferecimento: '<?= $Oferecimento->getID(); ?>', tpres: tipo, p: paginacao, buscar: ''});
		};

		$(document).ready(function() {
			$("#tabs").tabs({
				show: function(event, ui) {
					if((ui.panel.id == 'tab_disciplina') && (!carregou_informacoes)) {
						Informacoes();
						carregou_informacoes = true;
					}
				},
				select: function(event, ui) {
					window.location.hash = ui.tab.hash;
				}

			});
			Tamanho_Abas('tabs');
			$("#filtro_nome").Valor_Padrao('Filtrar por nome...', 'padrao');
			$("#filtro_nome").bind('keyup', function() {
				paginacao = 1;
				Lista_Alunos();
			});
			$("a.link_pagina").live('click', function() {
				var pg = $(this).attr('href').split('$')[1];
				Carrega(tipo, pg);
				return false;
			});
			Carrega(1, 1);

			if($("#pagina_valor").length > 0)
				$("#pagina_valor").val("http://");

			$("a.link_colaborar").click(function() {
				var campo = $(this).attr("id").split("_")[0];
				var valor = $.trim($("#" + campo + "_valor").val().replace(colaborar_padrao, ""));

				if(valor == "" || valor == 0)
					$.guaycuru.confirmacao("Valor inv&aacute;lido.");
				else {
					$("#" + campo + "_valor").attr('disabled', 'disabled');
					$("#" + campo + "_colaborar").hide();
					$.post("<?= CONFIG_URL; ?>ajax/colaboracao_oferecimento.php", {campo: campo, valor: valor, id_oferecimento: id_oferecimento}, function(data) {
						if(data && data.ok) {
							$.guaycuru.confirmacao("Colabora&ccedil;&atilde;o enviada. Aguarde autoriza&ccedil;&atilde;o.");
							$("#" + campo + "_valor").hide();
							$("#" + campo + "_valor").after("<label>Colabora&ccedil;&atilde;o pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.</label>");
							$("#" + campo + "_colaborar").hide();
						} else if(data.error) {
							$.guaycuru.confirmacao(data.error, "<?= CONFIG_URL; ?>oferecimento/<?= $Oferecimento->getID(); ?>");
						} else {
							$("#" + campo + "_valor").removeAttr('disabled');
							$("#" + campo + "_colaborar").show();
							$.guaycuru.confirmacao("Houve um problema com o seu pedido. Verifique se a p&aacute;gina colaborada referencia um site na Unicamp ou tente novamente mais tarde.");
						}
					});
				}

				return false;
			});

			$("#todos_alunos, #apenas_amigos").click(function() {
				Carrega(1, 1);
			});
		});

		// ]]>
	</script>
	<div id="coluna_esquerda_wrapper">
		<div id="coluna_esquerda">
			<div id="perfil_cabecalho">
				<div id="perfil_mensagem_botoes">
					<div id="perfil_cabecalho_nome"><?=$Oferecimento->getSigla(true); ?> <?=$Oferecimento->getTurma(true); ?> - <?=$Oferecimento->getDisciplina()->getNome(true); ?></div>
				</div>
			</div>
			<div class="tip" id="disciplina_tip">Dica: Para acessar diretamente a p&aacute;gina deste oferecimento use <span class="link"><a href="http://gde.ir/o/<?= $Oferecimento->getID(); ?>">http://gde.ir/o/<?= $Oferecimento->getID(); ?></a></span></div>
			<div id="perfil_abas">
				<div id="tabs">
					<ul>
						<li><a href="#tab_informacoes" class="ativo">Informa&ccedil;&otilde;es</a></li>
						<li><a href="#tab_disciplina">Disciplina</a></li>
						<li><a href="#tab_horario">Hor&aacute;rio</a></li>
					</ul>
					<div id="tab_informacoes" class="tab_content">
						<table cellspacing="0" class="tabela_bonyta_branca">
							<tr><td width="25%"><b>Per&iacute;odo:</b></td><td><?= $Oferecimento->getPeriodo(true)->getNome(false); ?></td></tr>
							<tr><td width="25%"><b>Turma:</b></td><td><?= $Oferecimento->getTurma(true); ?></td></tr>
							<tr><td width="25%"><b>Professor(es):</b></td><td><?= $Oferecimento->getProfessores(true); ?></td></tr>
							<tr><td width="25%"><b>Vagas:</b></td><td><?= $Oferecimento->getVagas().(($Oferecimento->getFechado())?' - <b>Fechada</b>':null); ?></td></tr>
							<tr><td width="25%"><b>Alunos:</b></td><td><?= $Oferecimento->getMatriculados(); ?></td></tr>
							<tr><td width="25%"><b>Desist&ecirc;ncias:</b></td><td><?= $Oferecimento->Desistencias(); ?></td></tr>
							<tr><td width="25%"><b>Reservas:</b></td><td><?= $Oferecimento->getReservas(true); ?></td></tr>
							<tr><td width="25%"><b>P&aacute;gina:</b></td><td><?= $html_oferecimento_pagina; ?></td></tr>
						</table>
					</div>
					<div id="tab_disciplina" class="tab_content">
						<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando dados da disciplina...
					</div>
					<div id="tab_horario" class="tab_content">
						<table cellspacing="0" class="tabela_bonyta_branca">
							<tr><td align="center"><b>-</b></td><td align="center"><b>Segunda</b></td><td align="center"><b>Ter&ccedil;a</b></td><td align="center"><b>Quarta</b></td><td align="center"><b>Quinta</b></td><td align="center"><b>Sexta</b></td><td align="center"><b>S&aacute;bado</b></td></tr>
							<?php
							$limpos = Util::Horarios_Livres($Horario);
							for($j = 7; $j < 23; $j++) {
								if(in_array($j, $limpos)) continue;
								echo "<tr><td align=\"center\"><b>".$j.":00</b></td>";
								for($i = 2; $i < 8; $i++) {
									echo "<td align=\"center\">".Oferecimento::Formata_Horario($Horario, $i, $j)."</td>";
								}
								echo "</tr>";
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<table cellspacing="0" class="tabela_bonyta_branca">
		<tr><td colspan="2">
				<br /><b>Alunos: (<a href="#" onclick="Carrega(1, 1); return false;" id="link_fotos">Fotos</a> | <a href="#" onclick="Carrega(2, 1); return false;" id="link_lista">Lista</a>)</b><br />
				<div style="margin-top: 10px;">
					<input style="margin-left: 0;" type="radio" name="filtro_amigos" id="todos_alunos" class="tipo_atualizacoes" checked="checked"><label for="todos_alunos">Todos Alunos</label>
					<input type="radio" id="apenas_amigos" name="filtro_amigos" class="tipo_atualizacoes"><label for="apenas_amigos">Apenas Amigos</label>
				</div><br />
				<input type="text" class="busca_simples" id="filtro_nome" />
				<div id="lista_alunos"><img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /></div>
			</td></tr>
	</table>

<?= $FIM; ?>
