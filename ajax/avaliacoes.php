<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

foreach(AvaliacaoPergunta::Listar('t') as $Pergunta) {
	$Professor = Professor::Load($_GET['id_professor']);
	if($Professor->getId_Professor() == null)
		exit;
	$Disciplina = Disciplina::Load($_GET['id_disciplina']);
	if($Disciplina->getId_Disciplina() == null)
		exit;
	$id_disciplina = $Disciplina->getId_Disciplina();
	$Media = $Pergunta->getMedia($_GET['id_professor'], $id_disciplina);
	echo "<strong>".$Pergunta->getPergunta(true)."</strong><br />";
	if($Media['v'] < CONFIG_AVALIACAO_MINIMO)
		echo "Ainda n&atilde;o foi atingido o n&uacute;mero m&iacute;nimo de votos.<br /><br />";
	else {
		echo "Pontua&ccedil;&atilde;o: <span id=\"span_fixo_".$Pergunta->getID()."_".$Professor->getID()."_".$id_disciplina."\" style=\"font-weight: bold;\">".number_format($Media['w'], 2, ',', '.')."</span> (".$Media['v']." votos)";
		if($_Usuario->getAdmin() === true)
			echo " - Ranking: <strong>".$Pergunta->Ranking($Professor, $Disciplina)."/".$Pergunta->Max_Ranking($Disciplina)."</strong><div id=\"fixo_".$Pergunta->getID()."_".$Professor->getID()."_".$id_disciplina."\" class=\"nota_slider_fixo\"></div>";
		echo "<br />";
	}
	$pode = $Pergunta->Pode_Votar($_Usuario, $Professor, $Disciplina);

	if($pode === true)
		echo "<div id=\"votar_nota_".$Pergunta->getID()."_".$Professor->getID()."_".$id_disciplina."\" class=\"seu_voto\">Seu voto: <span id=\"span_nota_".$Pergunta->getID()."_".$Professor->getID()."_".$id_disciplina."\"></span><div id=\"nota_".$Pergunta->getID()."_".$Professor->getID()."_".$id_disciplina."\" class=\"nota_slider\"></div><a href=\"#\" id=\"votar_".$Pergunta->getID()."_".$Professor->getID()."_".$id_disciplina."\" class=\"link_votar\">Votar</a></div>";
	elseif($pode == AvaliacaoPergunta::ERRO_JA_VOTOU)
		echo "Voc&ecirc; j&aacute; votou nesta pergunta! Seu voto: ".$Pergunta->Meu_Voto($_Usuario, $Professor, $Disciplina)."<br />";
	elseif($pode == AvaliacaoPergunta::ERRO_NAO_CURSOU)
		echo "Voc&ecirc; n&atilde;o pode votar pois ainda n&atilde;o cursou ".$Disciplina->getSigla(true)." com ".$Professor->getNome(true).".";
	elseif($pode == AvaliacaoPergunta::ERRO_NAO_ALUNO)
		echo "Voc&ecirc; n&atilde;o pode votar pois apenas alunos podem avaliar Professores.";
	echo "<br /><br />";
}
