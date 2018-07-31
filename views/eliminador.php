<?php

namespace GDE;

define('TITULO', 'Eliminador de Disciplinas');

require_once('../common/common.inc.php');

?>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function() {
		$("#menuAccordion").accordion({
			autoHeight: false,
			navigation: true,
			collapsible: true
		});
		$("span.ui-icon").css({'display': 'none'});
		$("input[name^='eliminada']").click(function() {
			sigla = ($(this).attr("name")).replace("eliminada_", "");
			siglaO = sigla.replace("_", " ");
			$("#sigla_"+sigla).html("<strong>"+siglaO+"</strong><br /><img src=\"<?= CONFIG_URL; ?>web/images/loading.gif\" alt=\".\" /> Salvando...");
			//opcao = $("input[name='eliminada_'"+sigla+"']:checked").val();
			opcao = $(this).val();
			if(opcao == 1) { // Normalmente
				e = 1;
				a = 0;
				r = 0;
			} else if(opcao == 2) { // Parcialmente
				e = 1;
				a = 1;
				r = 0;
			} else if(opcao == 3) { // Proficiencia
				e = 1;
				a = 0;
				r = 1;
			} else { // Nenhuma das Anteriores
				e = 0;
				a = 0;
				r = 0;
			}
			$.post('<?= CONFIG_URL; ?>ajax/disciplina.php', {sigla: siglaO, e: e, a: a, r: r}, function() {
				$("#sigla_"+sigla).html("<strong>"+siglaO+"</strong>");
			});
		});
	});
	// ]]>
</script>
<h2>Eliminar Disciplinas</h2>
<?php
if($_Usuario->getAluno(false) === null)
	echo "<strong>Erro:</strong> Apenas Alunos podem eliminar Disciplinas!<br />";
else {
?>
Aqui est&atilde;o listadas (unicamente) todas as disciplinas que voc&ecirc; cursou (desde 2007).<br />
Selecione a op&ccedil;&atilde;o apropriada para cada uma delas, para que sua &aacute;rvore reflita sua realidade acad&ecirc;mica:<br /><br />
<div id="accordionWrapper" class="gde_jquery_ui" >
	<div id="menuAccordion" class="ui-accordion ui-widget ui-helper-reset">

		<?php
		$siglas = array();
		$Periodos = Periodo::Listar();
		foreach($Periodos as $Periodo) {
			$Oferecimentos = $_Usuario->getAluno()->getOferecimentos($Periodo->getID());
			if(count($Oferecimentos) == 0)
				continue;
			?>
			<h3 style="padding: 5px"><?= $Periodo->getNome(false) ?></h3>
			<div><table  class="tabela_bonyta_branca tabela_busca" width="100%" >
					<?php
					foreach($Oferecimentos as $Oferecimento) {
						$Disciplina = $Oferecimento->getDisciplina(true);
						$siglaO = $Disciplina->getSigla(true);
						$sigla = str_replace(" ", "_", $siglaO);
						if(isset($siglas[$sigla]))
							continue;
						$siglas[$sigla] = true;
						$eliminou = $_Usuario->Eliminou($Disciplina);
						$parcialmente = $_Usuario->Eliminada($Disciplina, true, true);
						?>
						<tr>
							<td id="sigla_<?= $sigla; ?>" width="30%"><a href="<?= CONFIG_URL; ?>disciplina/<?= $Disciplina->getId(); ?>"><strong><?= $siglaO; ?></strong><br /><?= $Disciplina->getNome(true); ?></a></td>
							<td>
								<input type="radio" name="eliminada_<?= $sigla; ?>" value="1" id="eliminada_<?= $sigla; ?>_1"<?php if(($eliminou !== false) && ($eliminou[1] === false) && ($eliminou[0][0][1] === false)) echo " checked=\"checked\""; ?> /><label for="eliminada_<?= $sigla; ?>_1">Cursei e passei com Nota >= 5,0</label><br />
								<input type="radio" name="eliminada_<?= $sigla; ?>" value="2" id="eliminada_<?= $sigla; ?>_2"<?php if(($eliminou === false) && ($parcialmente !== false)) echo " checked=\"checked\""; ?> /><label for="eliminada_<?= $sigla; ?>_2">Cursei, n&atilde;o passei, mas tive Nota >= 3,0</label><br />
								<input type="radio" name="eliminada_<?= $sigla; ?>" value="3" id="eliminada_<?= $sigla; ?>_3"<?php if(($eliminou !== false) && ($eliminou[1] === false) && ($eliminou[0][0][1] !== false)) echo " checked=\"checked\""; ?> /><label for="eliminada_<?= $sigla; ?>_3">Passei em teste de Profici&ecirc;ncia</label><br />
								<input type="radio" name="eliminada_<?= $sigla; ?>" value="0" id="eliminada_<?= $sigla; ?>_0"<?php if((($eliminou === false) && ($parcialmente === false)) || (($eliminou !== false) && ($eliminou[1] === true))) echo " checked=\"checked\""; ?> /><label for="eliminada_<?= $sigla; ?>_0">Nenhuma das Anteriores</label><br />
							</td>
						</tr>
						<?php
					}
					?>
				</table></div>
			<?php
		}
		?>

	</div>
</div>
<?php
}
echo $FIM;
?>
