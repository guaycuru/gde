<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

foreach(AvaliacaoPergunta::Listar('t') as $Pergunta) {
	$Professor = Professor::Load($_POST['id_professor']);
	$Disciplina = Disciplina::Por_Sigla(str_replace("_", " ", $_POST['sigla']));
	$sigla = $Disciplina->getSigla(true);
	$Media = $Pergunta->getMedia($_POST['id_professor'], $sigla);
	echo "<strong>".$Pergunta->getPergunta(true)."</strong><br />";
	if($Media['v'] < CONFIG_AVALIACAO_MINIMO)
		echo "Ainda n&atilde;o foi atingido o n&uacute;mero m&iacute;nimo de votos.<br /><br />";
	else {
		//echo "Pontua&ccedil;&atilde;o: <span id=\"span_fixo_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\" style=\"font-weight: bold;\">".number_format($Media['w'], 2, ',', '.')."</span> (".$Media['v']." votos) - Ranking: <strong>".$Pergunta->Ranking($Professor, $Disciplina)."/".$Pergunta->Max_Ranking($Disciplina)."</strong><div id=\"fixo_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\" class=\"nota_slider_fixo\"></div><br />";
		echo "Pontua&ccedil;&atilde;o: <span id=\"span_fixo_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\" style=\"font-weight: bold;\">".number_format($Media['w'], 2, ',', '.')."</span> (".$Media['v']." votos)";
		if($_Usuario->getAdmin() === true)
			echo " - Ranking: <strong>".$Pergunta->Ranking($Professor, $Disciplina)."/".$Pergunta->Max_Ranking($Disciplina)."</strong><div id=\"fixo_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\" class=\"nota_slider_fixo\"></div>";
		echo "<br />";
	}
	$pode = $Pergunta->Pode_Votar($_Usuario, $Professor, $Disciplina);
	if($pode === true)
		echo "<div id=\"votar_nota_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\" class=\"seu_voto\">Seu voto: <span id=\"span_nota_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\"></span><div id=\"nota_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\" class=\"nota_slider\"></div><a href=\"#\" id=\"votar_".$Pergunta->getID()."_".$Professor->getID()."_".str_replace(" ", "-", $sigla)."\" class=\"link_votar\">Votar</a></div>";
	elseif($pode == AvaliacaoPergunta::ERRO_JA_VOTOU)
		echo "Voc&ecirc; j&aacute; votou nesta pergunta! Seu voto: ".$Pergunta->Meu_Voto($_Usuario, $Professor, $Disciplina)."<br />";
	elseif($pode == AvaliacaoPergunta::ERRO_NAO_CURSOU)
		echo "Voc&ecirc; n&atilde;o pode votar pois ainda n&atilde;o cursou ".$sigla." com ".$Professor->getNome(true).".";
	elseif($pode == AvaliacaoPergunta::ERRO_NAO_ALUNO)
		echo "Voc&ecirc; n&atilde;o pode votar pois apenas alunos podem avaliar Professores.";
	echo "<br /><br />";
}
