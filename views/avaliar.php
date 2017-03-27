<?php

namespace GDE;

define('TITULO', 'Avaliar Professores');
require_once('../common/common.inc.php');

?>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.avaliacao.js?<?= REVISION; ?>"></script>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function() {
		$("#menuAccordion").accordion({
			autoHeight: false,
			navigation: true,
			collapsible: true
		});
		$("span.ui-icon").css({'display': 'none'});
		$("input.avaliacao_oferecimento").each(Carregar_Avaliacoes);
		$("div.nota_slider").each(function() {
			Criar_Slider($(this));
		});
		$("div.nota_slider_fixo").each(function() {
			Criar_Slider_Fixo($(this));
		});
		$("a.link_votar").live('click', function() {
			var ids = $(this).attr('id').split('_');
			if(ids[3])
				Enviar_Avaliacao($(this), ids[1], ids[2], ids[3]);
			else
				Enviar_Avaliacao($(this), ids[1], ids[2]);
			return false;
		});
	});
	// ]]>
</script>
<h2>Avaliar Professores</h2>
<?php
if($_Usuario->getAluno(false) === null)
	echo "<strong>Erro:</strong> Apenas Alunos podem avaliar Professores!<br />";
else {
	// ToDo: Nao carregar as avaliacoes por Ajax para poder aproveitar um unico SELECT das avalicacoes do usuario!
	//$_Usuario->getAvaliacao_Respostas();
	?>
	Aqui est&atilde;o listados todos os professores com os quais voc&ecirc; j&aacute; cursou alguma Disciplina (desde 2007).<br /><br />
	<div id="accordionWrapper" class="gde_jquery_ui">
		<div id="menuAccordion" class="ui-accordion ui-widget ui-helper-reset">
			<?php
			$professores = $profdisc = array();
			$p = 0;
			$Periodos = Periodo::Listar();
			foreach($Periodos as $Periodo) {
				$Oferecimentos = $_Usuario->getAluno()->getOferecimentos($Periodo->getID());
				if(count($Oferecimentos) == 0)
					continue;
				?>
				<h3 style="padding: 5px"><?= $Periodo->getNome(true); ?></h3>
				<div><table class="ui-corner-bottom" style="width: 100%; border: 1px solid #A6C9E2" >
						<?php
						foreach($Oferecimentos as $Oferecimento) {
							$Professor = $Oferecimento->getProfessor(false);
							if(($Professor === null) || (isset($profdisc[$Professor->getID()][$Oferecimento->getSigla(false)])))
								continue;
							?>

							<tr>
								<td style="width: 50%; padding: 0px 5px 0px 5px; text-align:center; border: 1px solid #A6C9E2">
									<a href="<?= CONFIG_URL; ?>/perfil/?professor=<?= $Professor->getID(); ?>" style="font-weight: bold; "><?= $Professor->getNome(true); ?></a><br />(<?= $Oferecimento->getDisciplina(true)->getSigla(true); ?> - <?= $Oferecimento->getDisciplina(true)->getNome(true); ?>)
								</td>
								<td rowspan="2" style="width: 50%; padding: 0px 5px 0px 5px; border: 1px solid #A6C9E2">
									<div class="gde_jquery_ui">
										<h2>Como Professor(a) em <?= $Oferecimento->getDisciplina(true)->getSigla(true); ?></h2>
										<input type="hidden" class="avaliacao_oferecimento" id="selectoferecimento_<?= $Professor->getID(); ?>_<?= $p; ?>" value="<?= str_replace(" ", "_", $Oferecimento->getDisciplina(true)->getSigla(false)); ?>" />
										<div class="div_avaliacoes" id="div_avaliacoes_<?= $Professor->getID(); ?>_<?= $p++; ?>"></div>
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 0px 5px 0px 5px; border: 1px solid #A6C9E2">
									<div class="gde_jquery_ui">
										<h2>Como Professor(a)</h2>
										<?php
										foreach(AvaliacaoPergunta::Listar('p') as $Pergunta) {
											$Media = $Pergunta->getMedia($Professor->getID());
											echo "<strong>Pergunta: ".$Pergunta->getPergunta()."</strong><br />";
											if($Media['v'] < CONFIG_AVALIACAO_MINIMO)
												echo "Ainda n&atilde;o foi atingido o n&uacute;mero m&iacute;nimo de votos.<br /><br />";
											else
												echo "Pontua&ccedil;&atilde;o: <span id=\"span_fixo_".$Pergunta->getID()."_".$Professor->getID()."_".$p."\" style=\"font-weight: bold;\">".number_format($Media['w'], 2, ',', '.')."</span> (".$Media['v']." votos)<br />";
											$pode = $Pergunta->Pode_Votar($_Usuario, $Professor, null);
											if($pode === true)
												echo (isset($professores[$Professor->getID()])) ? "Esta pergunta j&aacute; foi exibida acima." : "<div id=\"votar_nota_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"seu_voto\">Seu voto: <span id=\"span_nota_".$Pergunta->getID()."_".$Professor->getID()."\"></span><div id=\"nota_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"nota_slider\"></div><a href=\"#\" id=\"votar_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"link_votar\">Votar</a></div>";
											elseif($pode == AvaliacaoPergunta::ERRO_JA_VOTOU)
												echo "Voc&ecirc; j&aacute; votou nesta pergunta! Seu voto: ".$Pergunta->Meu_Voto($_Usuario, $Professor)."<br />";
											elseif($pode == AvaliacaoPergunta::ERRO_NAO_CURSOU)
												echo "Voc&ecirc; n&atilde;o pode votar pois ainda n&atilde;o cursou nenhuma Disciplina com ".$Professor->getNome().".";
											echo "<br /><br />";
										}
										?>
									</div>
								</td>
							</tr>
							<?php
							$professores[$Professor->getID()] = true;
							$profdisc[$Professor->getID()][$Oferecimento->getDisciplina(true)->getSigla(false)] = true;
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
