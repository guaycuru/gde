<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$Evento = (!empty($_POST['id'])) ? Evento::Load($_POST['id']) : new Evento();

$tipos = "";
foreach(Evento::Listar_Tipos() as $i => $tipo) {
	if(($Evento->getTipo(false) == Evento::TIPO_FERIADO) || ($Evento->getTipo(false) == Evento::TIPO_GRADUACAO) || (($i != Evento::TIPO_GRADUACAO) && ($i != Evento::TIPO_FERIADO)))
		$tipos .= '<option value="'.$i.'" '.(($Evento->getTipo(false) == $i) ? 'selected="selected">' : '>').$tipo.'</option>\n';
}

$oferecimentos = '<option value="">Nenhum Oferecimento</option>';
$semestre = '';

$Atuais = ($_Usuario->getAluno(false) !== null) ? $_Usuario->getAluno()->getOferecimentos(Periodo::getAtual()->getID()) : array();
foreach($Atuais as $Atual) {
	if($semestre !== $Atual->getPeriodo()->getID()) {
		if($semestre != '')
			$oferecimentos .= '</optgroup>';
		$oferecimentos .= '<optgroup label="'.$Atual->getPeriodo()->getNome().'">';
		$semestre = $Atual->getPeriodo()->getID();
	}
	$oferecimentos .= '<option value="'.$Atual->getID().'" '.((($Evento->getOferecimento(false) !== null) && ($Evento->getOferecimento()->getID() == $Atual->getID())) ? 'selected="selected"' : '').'>'.$Atual->getSigla(true).' '.$Atual->getTurma(true).'</option>';
}

?>
<form method="post">
<input type="hidden" name="id_evento" id="id_evento" value="<?= $Evento->getID(); ?>" />
<table class="tabela_bonyta_branca" >
	<tr>
		<td colspan="2">
			<input type="text" name="nome" id="nomeEvento" style="width: 300px" value="<?= $Evento->getNome(true); ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<label>Data de In&iacute;cio</label>
			<input type="text" name="data_inicio" id="data_inicio" size="9" value="<?= ($Evento->getID() != null) ? $Evento->getData_Inicio('d/m/Y') : (!empty($_POST['dti']) ? htmlspecialchars($_POST['dti']) : '') ?>" />
			<label id="labelEventoInicio">Hora de In&iacute;cio</label>
			<input type="text" name="hora_inicio" id="hora_inicio" size="9" value="<?= ($Evento->getID() != null) ? $Evento->getData_Inicio('H:i') : (!empty($_POST['hri']) ? htmlspecialchars($_POST['hri']) : '') ?>" />
		</td>
		<td>
			<label>Data de T&eacute;rmino</label>
			<input type="text" name="data_fim" id="data_fim" size="9" value="<?= ($Evento->getID() != null) ? $Evento->getData_Fim('d/m/Y') : (!empty($_POST['dtf']) ? htmlspecialchars($_POST['dtf']) : '') ?>" />
			<label id="labelEventoTermino" >Hora de T&eacute;rmino</label>
			<input type="text" name="hora_fim" id="hora_fim" size="9" value="<?= ($Evento->getID() != null) ? $Evento->getData_Fim('H:i') : (!empty($_POST['hrf']) ? htmlspecialchars($_POST['hrf']) : '') ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="checkbox" name="ad" value="1" id="check_dia_todo" <?= ((($Evento->getID() != null) && $Evento->getDia_Todo()) || ((!empty($_POST['ad']) && $_POST['ad'] == 'true'))) ? 'checked=\"checked\"' : '' ?><label for="check_dia_todo"> Dia Todo</label>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<select name="tipo" id="tipoEvento">
				<?= $tipos; ?>
			</select>
			<select name="oferecimento" id="oferecimentoEvento">
				<?= $oferecimentos; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="text" name="local" id="localEvento" value="<?= ($Evento->getID() != null) ? $Evento->getLocal(true) : '' ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea name="descricao" id="descricaoEvento" rows="3" cols="30"><?php
					if($Evento->getID() != null)
						echo $Evento->getDescricao(true);
				?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php if($Evento->Pode_Alterar($_Usuario)) { ?>
			<input type="button" id="novo_evento_salvar" name="salvar" class="botao_salvar" value=" " alt="Salvar" />
			<input type="button" id="novo_evento_cancelar" name="cancelar" class="botao_cancelar" value=" " alt="Cancelar" />
			<?php } if(($Evento->getID() != null) && ($Evento->Pode_Alterar($_Usuario))) { ?>
			<input type="button" id="excluir_evento" name="excluir" class="botao_excluir" value=" " alt="Excluir" />
			<?php } ?>
		</td>
	</tr>
</table>
</form>
