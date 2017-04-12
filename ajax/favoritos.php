<?php

namespace GDE;

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);

require_once('../common/common.inc.php');

$Favoritos = $_Usuario->getFavoritos();

$pc = intval($_POST['pc']);

if(count($Favoritos) == 0)
	die('null');
	
$i = 0;
foreach($Favoritos as $Favorito) {
if(false && $i % $pc == 0)
	echo "<div class=\"favorito_linha\">";
?>
	<div class="amigo">
		<div class="amigo_foto"><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Favorito->getRA(true); ?>" style="text-decoration: none" title="<?= $Favorito->getNome(true); ?>"><img src="<?= ($Favorito->getUsuario(false) !== null) ? $Favorito->getUsuario()->getFoto(true, true) : Usuario::getFoto_Padrao(true); ?>" border="0" alt="<?= $Favorito->getNome(true); ?>" /></a></div>
		<div class="amigo_nome">
			<a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Favorito->getRA(true); ?>" class="amigo" title="<?= $Favorito->getNome(true); ?>"><?= $Favorito->getNome(true); ?></a>
		</div>
	</div>
<?php
	if(false && $i % $pc == $pc-1)
		echo "</div>";
	$i++;
}
