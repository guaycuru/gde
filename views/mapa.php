<?php

define('TITULO', 'Mapa do Campus');

require_once('../common/common.inc.php');

if(isset($_GET['cm']))
	die("<img src='".CONFIG_URL."web/images/loading.gif' /> Carregando Mapa...");

$link = "https://www.google.com/maps/d/embed?mid=1P2EmRJ18yUjdKwqg8gbpLR0WGi3Kxug&ehbc=2E312F";

?>
<script type="text/javascript">
	// <![CDATA[
	var carregou_mapa = false;
	$(document).ready(function() {
		if(!carregou_mapa) {
			$("#iframe_mapa").attr('src', '<?= $link ?>');
			carregou_mapa = true;
		}
	});
	// ]]>
</script>
<div id="mapa_campus" style="width: 100%">
	<div id="texto_mapa_campus" style="width: 100%;"><h1 style="text-align: center;">Mapa do Campus - UNICAMP</h1></div>
	<div id="iframe_mapa_campus">
		<iframe class="iframe_mapa" id="iframe_mapa" scrolling="no" frameborder="0" src="<?= CONFIG_URL; ?>sala/?cm" marginwidth="0" marginheight="0"></iframe>
	</div>
</div>

<?= $FIM; ?>
