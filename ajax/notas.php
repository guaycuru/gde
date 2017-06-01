<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

if($_Usuario->getAluno(false) === null)
	exit();
	
if(!empty($_GET['idn'])) {
	$Nota = Nota::Load($_GET['idn']);
	if(($Nota->getID() == null) || ($Nota->getUsuario(true)->getID() != $_Usuario->getID()))
		exit();
?>
<div id="div_nota_<?= $Nota->getID(); ?>" class="nota">
	<span class="nota_sigla"><?= $Nota->getSigla(true); ?></span>: <span class="nota_nota"><?= $Nota->getNota(true); ?></span>
	<span class="peso_texto">Peso</span>: <span class="peso_valor"><?= $Nota->getPeso(true); ?></span>
	<a href="#" onclick="return Alterar_Nota('<?= $Nota->getID(); ?>', '<?= $Nota->getOferecimento()->getID(); ?>');">Alterar</a>
	<a href="#" onclick="return Remover_Nota('<?= $Nota->getID(); ?>');">Excluir</a>
</div>
<?php
	exit();
}

$Periodo_Selecionado = (!empty($_GET['p'])) ? Periodo::Load($_GET['p']) : Periodo::getAtual();

$Periodos = Periodo::Listar();
$periodos = "";
foreach($Periodos as $Periodo)
	$periodos .= '<option value="'.$Periodo->getID().'"'.(($Periodo_Selecionado->getID() == $Periodo->getID())?' selected="selected"':null).'>'.$Periodo->getNome(false).'</option>';

$Oferecimentos = $_Usuario->getAluno()->getOferecimentos($Periodo_Selecionado->getID());

?>
<div class="div_periodo"><strong>Notas de <select id="periodo_notas"><?= $periodos; ?></select></strong></div>
<?php
if(count($Oferecimentos) == 0)
	die('	<div style="text-align: center;"><strong>Voc&ecirc; n&atilde;o cursou nenhuma Disciplina em '.$Periodo_Selecionado->getNome(false).'!</strong></div>');
?>
	<div style="text-align: center;"><strong>Aviso:</strong> Esta ainda &eacute; uma vers&atilde;o preliminar do controle de notas.</div>
	<div id="tabs_notas" class="tabs-bottom">
	<ul>
<?php foreach($Oferecimentos as $Oferecimento) { ?>
		<li><a href="#tab_notas_<?= $Oferecimento->getID(); ?>"><?= $Oferecimento->getSigla(true); ?> <?= $Oferecimento->getTurma(true); ?></a></li>
<?php } ?>
	</ul>
<?php foreach($Oferecimentos as $Oferecimento) { ?>
	<div id="tab_notas_<?= $Oferecimento->getID(); ?>" class="tab_content">
		<div class="notas_header_sigla"><?= $Oferecimento->getSigla(true); ?> <?= $Oferecimento->getTurma(true); ?></div>
		<div class="notas_header_nome"><?= $Oferecimento->getDisciplina()->getNome(true); ?></div>
		<div class="notas">
<?php foreach(Nota::Listar($_Usuario, $Oferecimento) as $Nota)
	if ($Nota->getSigla() != "Exame") { ?>
		<div id="div_nota_<?= $Nota->getID(); ?>" class="nota">
			<div class="nota_sigla">
				<?= $Nota->getSigla(true); ?>
				<a href="#" onclick="return Alterar_Nota('<?= $Nota->getID(); ?>', '<?= $Nota->getOferecimento()->getID(); ?>');"><img src="<?= CONFIG_URL; ?>web/images/EditOFF.png" alt="Alterar" title="Alterar" class="nota_botao_lapis" /></a>
				<a href="#" id="excluir_nota_<?= $Nota->getID(); ?>" onclick="return Remover_Nota('<?= $Nota->getID(); ?>');"><img class="nota_botao_x" src="<?= CONFIG_URL; ?>web/images/CancelOFF.png" alt="Excluir" title="Excluir" /></a>
			</div>
			<div class="nota_texto">
				<span class="nota_texto">Nota</span>: <span class="nota_valor"><?= $Nota->getNota(true); ?></span>
			</div>
			<div class="peso_texto">
				<span class="peso_texto">Peso</span>: <span class="peso_valor"><?= $Nota->getPeso(true); ?></span>
			</div>
		</div>
<?php
	}
$totalPeso = 0;
$media = 0;
foreach(Nota::Listar($_Usuario, $Oferecimento) as $Nota)
	if($Nota->getSigla() != "Exame"){
		$totalPeso += floatval(str_replace(",", ".", $Nota->getPeso(true)));
		$media += floatval(str_replace(",", ".", $Nota->getNota(true))) * floatval(str_replace(",", ".", $Nota->getPeso(false)));
	}
	else
		$Exame = $Nota;
if($totalPeso > 0) {
	$media = floatval($media / $totalPeso);
?>
		<div id="div_media_<?= $Oferecimento->getID(); ?>" class="nota">
			<div class="nota_sigla">M&eacute;dia</div>
			<div class="nota_texto">
				<span class="nota_texto">Nota</span>: <span class="media_valor"><?= number_format($media, 2, ',', '.'); ?></span>
			</div>
		</div>
<?php } 
if(isset($Exame) && ($Exame->getOferecimento()->getID() == $Oferecimento->getID() )) { ?>
		<div id="div_nota_<?= $Exame->getID(); ?>" class="nota">
			<div class="nota_sigla">Exame <a href="#" onclick="return Alterar_Nota('<?= $Exame->getID(); ?>', '<?= $Exame->getOferecimento()->getID(); ?>');"><img src="<?= CONFIG_URL; ?>web/images/EditOFF.png" alt="Alterar" title="Alterar" class="nota_botao_lapis" /></a> <a href="#" id="excluir_nota_<?= $Exame->getID(); ?>" onclick="return Remover_Nota('<?= $Exame->getID(); ?>');"><img class="nota_botao_x" src="<?= CONFIG_URL; ?>web/images/CancelOFF.png" alt="Excluir" title="Excluir" /></a></div>
			<div class="nota_texto">
				<span class="nota_texto">Nota</span>: <span class="nota_valor"><?= $Exame->getNota(true); ?></span>
			</div>
		</div>
		<div id="div_media_final_<?= $Oferecimento->getID(); ?>" class="nota">
			<div class="media_sigla">M&eacute;dia Final</div>
			<span class="nota_texto">Nota</span>: <span class="media_valor"><?=number_format((($media+str_replace(",", ".", $Exame->getNota(false)))/2), 2, ',', '.'); ?></span>
		</div>
<?php } ?>
		<div id="nota_funcoes_<?= $Oferecimento->getID() ?>" class="nota">
			<div class="notas_funcoes" id="funcao_nota_<?= $Oferecimento->getID() ?>">
				<a href="#" id="link_nova_nota_<?= $Oferecimento->getID(); ?>" onclick="return Adicionar_Nota('<?= $Oferecimento->getID(); ?>', false);">Nova Nota</a>
			</div>
<?php if((count(Nota::Listar($_Usuario, $Oferecimento)) > 0) && (!isset($Exame))) { ?>
			<div class="notas_funcoes" id="funcao_exame_<?= $Oferecimento->getID() ?>">
				<a href="#" id="link_novo_exame_<?= $Oferecimento->getID(); ?>" onclick="return Adicionar_Nota('<?= $Oferecimento->getID(); ?>', true);" class="nota_funcoes">Adicionar Exame</a>
			</div>
<?php
	}
	unset($Exame);
?>
			</div>
		</div>
	</div>
<?php } ?>
</div>
