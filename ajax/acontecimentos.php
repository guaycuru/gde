<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

/*if(isset($_POST['ui']))
	die(Acontecimento::Ultimo_ID($_GDE['DB']));*/

$por_pagina = (isset($_POST['pp'])) ? intval($_POST['pp']) : 10;
$start = (isset($_POST['st'])) ? intval($_POST['st']) : 0;
$maior_que = (isset($_POST['nvs'])) ? intval($_POST['ultimo']) : false;
if(isset($_POST['nvs']))
	$por_pagina = '-1';

if(empty($_GET['i'])) {
	$home = $meu = true;
	$mensagens = (!empty($_GET['msg']));
	$minhas = (!empty($_GET['min']));
	$amizades = $minhas;
	$amigos = (!empty($_GET['am']));
	$gde = (!empty($_GET['gde']));
	$todas_respostas = (!empty($_GET['rt']));
	// ToDo: Testar salvar
	if(!isset($_GET['o'])) {
		$Usuario_Config = $_Usuario->getConfig(true);
		$Usuario_Config->setAcontecimentos_Mensagens($mensagens);
		$Usuario_Config->setAcontecimentos_Minhas($minhas);
		$Usuario_Config->setAcontecimentos_Amigos($amigos);
		$Usuario_Config->setAcontecimentos_GDE($gde);
		$Usuario_Config->Save(true);
	}
	$Usr = $_Usuario;
} else {
	$home = $meu = false;
	$mensagens = $minhas = $todas_respostas = true;
	$amizades = $amigos = $gde = false;
	$Usr = Usuario::Load($_GET['i']);
	if($Usr->getID() == null)
		exit();
}
// Um Acontecimento especifico...
if(!empty($_GET['o'])) {
	$todas_respostas = true;
	$Acontecimentos = array(Acontecimento::Load($_GET['o']));
} else
	$Acontecimentos = Acontecimento::Listar($Usr, $por_pagina, $start, $maior_que, $mensagens, $minhas, $amizades, $amigos, $gde);

$maior_id = (isset($_GET['ultimo'])) ? intval($_GET['ultimo']) : 0;

if(isset($_GET['mais'])) // Gambiarra pro find do jQuery funcionar
	echo '<div>';

if(count($Acontecimentos) > 0)
	echo '<div id="atualizacao_maior_id" style="display:none;">'.Acontecimento::Ultimo_ID().'</div>';

foreach($Acontecimentos as $Acontece) {
	$Respostas  = $Acontece->Listar_Respostas(($todas_respostas) ? null : $Usr);
?>
<div class="atualizacao<?php if(($maior_id > -1) && ($Acontece->getID() > $maior_id)) echo " atualizacao_nova"; if(isset($_GET['nvs'])) echo " atualizacao_nova_escondida"; ?>" id="<?php if(isset($_GET['nvs'])) echo "nova_"; ?>atualizacao_<?= $Acontece->getID(); ?>"<?php if(isset($_GET['nvs'])) echo " style=\"display: none;\""; ?>>
	<div class="atualizacao_foto">
		<a href="<?= $Acontece->getLink(); ?>"><img src="<?= $Acontece->getFoto(); ?>" border="0" alt="<?= $Acontece->getNome(); ?>" class="escala" /></a>
	</div>
	<div class="atualizacao_texto_data">
		<div class="atualizacao_texto">
			<?php if(($home) && ($Acontece->getOrigem() !== null)/* Nao preciso pq na home nao aparece quem nao eh meu amigo && ($Usr->Amigo($Acontece->getUsuario_Origem()))*/) echo $Acontece->getOrigem()->getChat_Status(true); ?><a href="<?= $Acontece->getLink(); ?>" title="<?= $Acontece->getNome(true); ?>"><span class="atualizacao_nome"><?= $Acontece->getNome(); ?></span></a><?= $Acontece->getTexto(true, true, $meu, $Usr); ?>
		</div>
		<div class="atualizacao_data_link">
			<span class="atualizacao_data"><?= $Acontece->getData('d/m/Y H:i:s'); ?></span><br />
			<span class="responder_remover">
			<?php if($Acontece->Pode_Responder($_Usuario)) { ?><a href="#" class="atualizacao_responder" id="responder_<?= $Acontece->getID(); ?>"><strong>Responder</strong></a><?php } ?>
			<?php if(($Acontece->Pode_Responder($_Usuario)) && ($Acontece->Pode_Apagar($_Usuario))) echo " / "; ?>
			<?php if($Acontece->Pode_Apagar($_Usuario)) { ?><a href="#" class="atualizacao_remover" id="remover_<?= $Acontece->getID(); ?>"><strong>Remover</strong></a><?php } ?>
			</span><br />
			<?php if($Acontece->getNumero_Respostas() > count($Respostas)) { ?><span class="todas_respostas"><a href="#" class="atualizacao_todas_respostas" id="todas_respostas_<?= $Acontece->getID(); ?>">Exibir <?= ($Acontece->getNumero_Respostas() > 1) ? 'as '.$Acontece->getNumero_Respostas().' respostas' : ' 1 resposta'; ?></a></span><?php } ?>
		</div>
	</div>
	<div class="clear_all"></div>
</div>
<div class="atualizacao_respostas<?php if(isset($_GET['nvs'])) echo " atualizacao_nova_escondida"; ?>" id="<?php if(isset($_GET['nvs'])) echo "nova_"; ?>respostas_<?= $Acontece->getID(); ?>"<?php if(isset($_GET['nvs'])) echo " style=\"display: none;\""; ?>>
<?php
	foreach($Respostas as $Resposta) {
?>
	<div class="atualizacao_resposta<?php if(($maior_id > -1) && ($Resposta->getID() > $maior_id)) echo " atualizacao_nova"; if(isset($_GET['nvs'])) echo " atualizacao_nova_escondida"; ?>" id="<?php if(isset($_GET['nvs'])) echo "nova_"; ?>atualizacao_<?= $Resposta->getID(); ?>"<?php if(isset($_GET['nvs'])) echo " style=\"display: none;\" class=\"atualizacao_nova_escondida\""; ?>>
		<div class="atualizacao_foto">
			<a href="<?= $Resposta->getLink(); ?>"><img src="<?= $Resposta->getFoto(); ?>" border="0" alt="<?= $Resposta->getNome(); ?>" class="escala" /></a>
		</div>
		<div class="atualizacao_texto_data">
			<div class="atualizacao_texto">
				<?php if(($home) && ($Resposta->getOrigem() !== null) && ($Usr->Amigo($Resposta->getOrigem()) !== false)) echo $Resposta->getOrigem()->getChat_Status(true); ?>
					<a href="<?= $Resposta->getLink(); ?>" title="<?= $Resposta->getNome(true); ?>"><span class="atualizacao_nome"><?= $Resposta->getNome(); ?></span></a>
					<?= $Resposta->getTexto(true, true, $home, $_Usuario); ?>
			</div>
			<div class="atualizacao_data_link">
				<span class="atualizacao_data"><?= $Resposta->getData('d/m/Y H:i:s'); ?></span><br />
				<?php if($Acontece->Pode_Responder($_Usuario)) { ?><a href="#" class="atualizacao_responder" id="responder_<?= $Resposta->getID(); ?>_<?= $Resposta->getOriginal()->getID(); ?>"><strong>Responder</strong></a><?php } ?>
				<?php if(($Acontece->Pode_Responder($_Usuario)) && ($Acontece->Pode_Apagar($_Usuario))) echo " / "; ?>
				<?php if($Resposta->Pode_Apagar($_Usuario) === true) { ?><a href="#" class="atualizacao_remover" id="remover_<?= $Resposta->getID(); ?>"><strong>Remover</strong></a><?php } ?>
			</div>
		</div>
		<div class="clear_all"></div>
	</div>
<?php } ?>
</div>
<?php
}
if(isset($_GET['mais']))
	echo '</div>';

echo $FIM;
?>
