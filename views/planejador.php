<?php

namespace GDE;

define('TITULO', 'Planejador');

if(isset($_GET['idi'])) {
	$_POST['id'] = $_GET['idi'];
	$_POST['p'] = true;
	if(isset($_GET['full']))
		$_POST['full'] = true;
	echo "<style>* { color: #000000; } table.tabela_bonyta { border-top: 1px solid #000000; border-left: 1px solid #000000;	width: 100%;} table.tabela_bonyta td { border-right: 1px solid #000000; border-bottom: 1px solid #000000;}</style>";
	include('../ajax/planejado_compartilhado.php');
	echo (!isset($_GET['full'])) ? "<br /><a href=\"".CONFIG_URL."planejador/?idi=".intval($_GET['idi'])."&amp;full\">Ver Hor&aacute;rio Completo</a>" : "<br /><a href=\"".CONFIG_URL."planejador/?idi=".intval($_GET['idi'])."\">Ver Hor&aacute;rio Resumido</a>";
	exit();
}

require_once('../common/common.inc.php');

if(($_Usuario->getCatalogo(false) == null) || ($_Usuario->getCurso(false) == null))
	die("<strong>Erro:</strong> N&atilde;o &eacute; poss&iacute;vel montar a &aacute;rvore para o planejador: Cat&aacute;logo ou Curso de Gradua&ccedil;&atilde;o n&atilde;o especificado no Perfil!<br />".$FIM);

$cores = Planejado::getCores();
$nce = count(PlanejadoExtra::getCores());

$id = (isset($_GET['id'])) ? intval($_GET['id']) : null;

if(!isset($_GET['p']))
	$periodo = Dado::Pega_Dados('planejador_periodo_proximo');
else
	$periodo = intval($_GET['p']);

if($id == null) {
	$Planejados = null;
	$Planejado = Planejado::Algum($_Usuario, $periodo, $Planejados);
	$id = $Planejado->getID();
} else {
	$Planejado = Planejado::Load($id);
	$Planejados = Planejado::Por_Usuario($_Usuario, $periodo, false);
}

$periodo = $Planejado->getPeriodo(true)->getID();

//Eu amo o Felipe G. Franco o mais lindo do Brasil e do universo!!!!! Ass: Najara I. Guaycuru Gonçalves

?>
<style>
	<?php foreach($cores as $c => $cor) { ?>
	div.psd_cor_<?= $c; ?> {
		border-color: <?= $cor; ?> !important;
	}
	div.mtr_cor_<?= $c; ?> {
		background-color: <?= $cor; ?> !important;
	}
	<?php } ?>
</style>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.planejador.js?<?= REVISION; ?>"></script>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function() {
		nce = <?= $nce; ?>;
		periodo = '<?= $periodo; ?>';
		InicializarPlanejador('<?= $id; ?>');
	});
	// ]]>
</script>
<div class="tip" id="planejador_tip">Dica: Para acessar diretamente seu planejador, use <span class="link"><a href="http://gde.ir/planejador">http://gde.ir/planejador</a></span></div><br />
<strong>Aten&ccedil;&atilde;o:</strong> <!-- <strong>Planejador parcial e incompleto: Estes ainda n&atilde;o s&atilde;o os dados oficiais da DAC, e poder&atilde;o sofrer altera&ccedil;&otilde;es!</strong><br /> -->Este &eacute; apenas um planejamento. Voc&ecirc; dever&aacute; fazer sua matr&iacute;cula normalmente pelo site da DAC!<br />
<div id="planejador_opcoes" class="gde_jquery_ui">
	<?php foreach($Planejados as $p => $Planejado) { ?>
		<input type="radio" id="planejador_opcao<?= $p; ?>" name="radio" value="<?= $Planejado->getID(); ?>"<?php if($Planejado->getID() == $id) echo ' checked="checked"'; ?> /><label for="planejador_opcao<?= $p; ?>">Op&ccedil;&atilde;o <?= $p+1; ?><?php if($Planejado->getID() == $id) { ?> <img id="planejador_excluir" class="planejador_excluir" src="<?= CONFIG_URL; ?>web/images/close.png" /><?php } ?></label>
	<?php } ?>
	<input type="radio" id="planejador_opcaon" name="radio" value="n" /><label for="planejador_opcaon">Nova Op&ccedil;&atilde;o</label>
</div>
<div id="planejador_cabecalho">
	<table>
		<tr>
			<td width="20%"><b>Per&iacute;odo Planejado:</b></td>
			<td width="30%"><span id="planejador_periodo">?</span></td>
			<td width="20%"><b>Per&iacute;odo Atual:</b></td>
			<td width="30%"><span id="planejador_periodo_atual">?</span></td>
		</tr>
		<tr>
			<td width="20%"><b>Per&iacute;odo de Matr&iacute;cula:</b></td>
			<td width="30%"><span id="planejador_periodo_datas_matricula">?</span></td>
			<td width="20%"><b>Per&iacute;odo de Altera&ccedil;&atilde;o:</b></td>
			<td width="30%"><span id="planejador_periodo_datas_alteracao">?</span></td>
		</tr>
		<tr>
			<td width="20%"><b>CP:</b></td>
			<td width="30%"><span id="planejador_cp">X</span></td>
			<td width="20%"><b>CPF:</b></td>
			<td width="30%"><span id="planejador_cpf">X</span></td>
		</tr>
		<tr>
			<td width="20%"><b>Prov&aacute;vel Aprova&ccedil;&atilde;o:</b></td>
			<td colspan="3"><a href="#" id="toggle_configurar">Clique aqui para selecionar as disciplinas nas quais voc&ecirc; acha que ser&aacute; aprovado(a)</a><br />
				<div id="planejador_configurar" style="display: none;"><form id="form_planejador_configurar"><br /><br /><input type="submit" name="configurar" value="Salvar" class="botao_salvar" /></form></div>
			</td>
		</tr>
		<tr>
			<td width="20%"><b>Integraliza&ccedil;&atilde;o:</b></td>
			<td colspan="3"><a href="#" id="toggle_integralizacao">Mostrar Integraliza&ccedil;&atilde;o Planejada</a><br /><div id="planejador_integralizacao" style="display: none;"></div></td>
		</tr>
		<tr>
			<td width="20%"><b>Matr&iacute;culas (<span id="planejador_creditos">0</span>):</b></td>
			<td colspan="3"><span id="planejador_matriculas"></span></td>
		</tr>
		<tr>
			<td width="20%"><b>Compartilhado:</b></td>
			<td colspan="3"><input type="radio" name="compartilhado" value="f" id="compartilhado_f" /><label for="compartilhado_f">N&atilde;o</label> <input type="radio" name="compartilhado" value="t" id="compartilhado_t" /><label for="compartilhado_t">Com Amigos</label> (Est&aacute;gios e Atividades Extra-Curriculares n&atilde;o ser&atilde;o compartilhados).</td>
		</tr>
		<?php if($_Usuario->getAdmin()) { ?>
		<tr>
			<td width="20%"><b>Simulado:</b></td>
			<td colspan="3"><input type="radio" name="simulado" value="f" id="simulado_f" /><label for="simulado_f">N&atilde;o</label> <input type="radio" name="simulado" value="t" id="simulado_t" /><label for="simulado_t">Sim</label> (Permite que sejam adicionadas disciplinas que voc&ecirc; n&atilde;o pode cursar).</td>
		</tr>
		<?php } ?>
		<tr>
			<td width="50%" colspan="2" align="center"><a href="#" id="visualizar_impressao">Visualizar Para Impress&atilde;o</a></td>
			<td width="50%" colspan="2" align="center"><a href="#" class="planejador_excluir">Excluir esta op&ccedil;&atilde;o</a></td>
		</tr>
	</table>
</div>
<div id="planejador_adicionar_extra">
	<a href="#div_novo_extra" id="link_novo_extra">Adicionar Est&aacute;gio / Atividade Extra-Curricular</a>
	<span class="formInfo"><a href="#" id="TT_planejador_dicas">Dicas de uso</a></span>
</div>
<div id="planejador_adicionar">
	Adicionar Eletiva: <input type="text" name="sigla_eletiva" id="sigla_eletiva" style="width: 250px;" /> <span class="formInfo"><a href="#" id="TT_eletivas">?</a></span>
</div>
<div id="planejador_calendario"></div>
<div id="planejador_disciplinas">
	<div class="planejador_semestre" style="display: none;">
		<div class="planejador_semestre_numero">*</div>
		<div id="psd_dN" class="planejador_semestre_disciplinas"></div>
	</div>
</div>
<div id="div_novo_extra">
	<form id="form_novo_extra">
		<input type="hidden" name="extra_nome" />
		<table class="tabela_bonyta_branca" style="width: 300px;">
			<tr>
				<td>
					<label for="extra_nome">Nome:</label>
				</td>
				<td>
					<select name="nome_lista" id="extra_lista_nomes">
						<option value="-1">Novo:</option>
					</select>
					<input type="text" name="novo_nome" id="extra_novo_nome" style="width: 100px" />
				</td>
			</tr>
			<tr>
				<td>
					<label for="extra_dia_da_semana">Dia da Semana:</label>
				</td>
				<td>
					<select name="dia_da_semana" id="extra_dia_da_semana">
						<option value="1">Segunda-Feira</option>
						<option value="2">Ter&ccedil;a-Feira</option>
						<option value="3">Quarta-Feira</option>
						<option value="4">Quinta-Feira</option>
						<option value="5">Sexta-Feira</option>
						<option value="6">S&aacute;bado</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label>Hor&aacute;rio:</label>
				</td>
				<td>
					<input type="text" name="horario1" id="extra_horario1" size="5" placeholder="8:00" /> &agrave;s <input type="text" name="horario2" id="extra_horario2" size="5" placeholder="9:00" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="button" id="novo_extra_salvar" name="salvar" class="botao_salvar" value=" " alt="Salvar" />
					<input type="button" id="novo_extra_cancelar" name="cancelar" class="botao_cancelar" value=" " alt="Cancelar" />
				</td>
			</tr>
		</table>
	</form>
</div>
<div id="planejador_divtip">
	<div class="linha">
		<span class="divtip_titulo">Disciplina:</span>
		<span id="divtip_disciplina"></span>
	</div>
	<div class="linha">
		<span class="divtip_titulo">Professor(es):</span>
		<span id="divtip_professor"></span>
	</div>
	<div class="linha">
		<span class="divtip_titulo">Tipo:</span>
		<span id="divtip_tipo"></span>
	</div>
	<div class="linha">
		<span class="divtip_titulo">Viola Reserva:</span>
		<span id="divtip_viola"></span>
	</div>
	<div class="linha">
		<span class="divtip_titulo">Depende de AA200:</span>
		<span id="divtip_AA200"></span>
	</div>
	<div class="linha">
		<span class="divtip_titulo">Planejados:</span>
		<span id="divtip_total"></span>
	</div>
	<div class="linha">
		<span class="divtip_titulo">Vagas:</span>
		<span id="divtip_vagas"></span>
	</div>
	<div class="linha">
		<span class="divtip_titulo">Amigos (<span id="divtip_amigos"></span>):</span>
		<span id="divtip_lista_amigos"></span>
	</div>
</div>
<?= $FIM; ?>
