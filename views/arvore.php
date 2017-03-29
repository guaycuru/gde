<?php

namespace GDE;

if(isset($_GET['e'])) {

	function TrazDoCache($params) {
		if(($params[0] != 'catalogos') && ($params[0] != 'ementas'))
			return false;
		$arquivo = '../cache/'.$params[0].'/'.intval($params[1]).'/'.str_replace(array('.', '/', '\\'), null, $params[2]).'.html';
		if(file_exists($arquivo) === false)
			return "Curso / Modalidade inexistentes no cat&aacute;logo selecionado...";
		$conteudo = file_get_contents($arquivo);
		if($params[0] != 3)
			return preg_replace(array('/\.\.\/ementas\/todas(\w{1,2})\.html/i', '/\.\.\/images\//i'), array(CONFIG_URL."arvore/?e=3&ct=".$params[1]."&sg=$1", CONFIG_URL."web/images/dac_"), $conteudo);
		else
			return $conteudo;
	}

	define("NO_HTML", true);
	require_once('../common/common.inc.php');

	$catalogo = intval($_GET['ct']);
	if($catalogo < 2012)
		$curso = isset($_GET['cr']) ? sprintf("%02d", intval($_GET['cr'])) : null;
	else
		$curso = isset($_GET['cr']) ? intval($_GET['cr']) : null;
	$sigla = isset($_GET['sg']) ? $_GET['sg'] : null;
	if($_GET['e'] == 1)
		echo ($catalogo < 2012) ? TrazDoCache(array('catalogos', $catalogo, 'cpl'.$curso)) : TrazDoCache(array('catalogos', $catalogo, 'cp'.$curso));
	elseif($_GET['e'] == 2)
		echo TrazDoCache(array('catalogos', $catalogo, 'sug'.$curso));
	else
		echo TrazDoCache(array('ementas', $catalogo, 'todas'.$sigla));
	exit();
}

define('TITULO', '&Aacute;rvore');
require_once('../common/common.inc.php');

if((!isset($_GET['us'])) || ($_GET['us'] == $_Usuario->getLogin())) {
	$Usr = clone $_Usuario;
	if(isset($_GET['catalogo'])) {
		$catalogo = intval($_GET['catalogo']);
		$Usr->setCatalogo($catalogo);
	} else
		$catalogo = $Usr->getCatalogo();
	if((!empty($_GET['curso'])) && (isset($_GET['modalidade']))) {
		$curso = intval($_GET['curso']);
		$Curso = Curso::Por_Numero($curso);
		if($Curso === null)
			die("<h2>Curso n&atilde;o encontrado!</h2>");
		$Usr->setCurso($Curso);
		$modalidade = (strlen($_GET['modalidade']) > 0) ? substr($_GET['modalidade'], 0, 2) : null;
		$Modalidade = Modalidade::Por_Unique($Curso->getNivel(false), $Curso->getID(), $modalidade, $catalogo);
		if($Modalidade === null)
			die("<h2>Modalidade n&atilde;o encontrada!</h2>");
		$Usr->setModalidade($Modalidade);
	} else {
		$curso = $Usr->getCurso(true)->getNumero(true);
		$modalidade = $Usr->getModalidade(true)->getSigla(true);
	}
	$completa = ((isset($_GET['cp'])) && ($_GET['cp'] == 1));
	$meu = true;
} else {
	$Usr = Usuario::Load($_GET['us']);
	if($_Usuario->Posso_Ver($Usr, $Usr->getCompartilha_Arvore()) === false)
		die("<h2>Parab&eacute;ns, voc&ecirc; descobriu o segredo para ver a &Aacute;rvore de quem n&atilde;o quer compartilh&aacute;-la...</h2><h1>NOT!!!!!</h1>");
	$curso = $Usr->getCurso(true)->getNumero(true);
	$modalidade = $Usr->getModalidade(true)->getSigla(true);
	$catalogo = $Usr->getCatalogo(true);
	$completa = false;
	$meu = false;
}

$Cursos = Curso::Listar(array('G', 'T'));
$lista_cursos = "";
foreach($Cursos as $Curso)
	$lista_cursos .= "<option value=\"".$Curso->getNumero(true)."\"".(($Curso->getNumero() == $Usr->getCurso(true)->getNumero(false))?" selected=\"selected\"":null).">".$Curso->getNome(true)." (".$Curso->getNumero(true).")</option>";

$lim_cat = Dado::Limites_Catalogo();
$catalogos = "";
for($i = $lim_cat['min']; $i <= $lim_cat['max']; $i++)
	$catalogos .= "<option value='".$i."'".(($i == $Usr->getCatalogo(true))?" selected=\"selected\"":null).">".$i."</option>";

$continua = Curriculo::Existe($curso, $modalidade, $catalogo);
if($continua) {
	$start = microtime(true);
	$Arvore = new Arvore($Usr, $completa);
	if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1)) {
		$construct = microtime(true) - $start;
		echo "Construct: ".$construct."<br />";
	}
	$_SESSION['Arvore_Curriculo']['Imagem_Tmp'] = '../cache/arvores/arvore_'.Util::Code(8).'.png';
	$Arvore->Desenha($_SESSION['Arvore_Curriculo']['Imagem_Tmp']);
	if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
		echo "Desenha: ".(microtime(true) - $start - $construct)."<br />";
	list($starters, $menus) = $Arvore->RMenu($meu);
	list($inicializa_popup, $div_popup) = $Arvore->Inicializa_Mostra($meu);

} else
	$menus = $starters = $inicializa_popup = "";

?>
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.jcontext.js?<?= REVISION; ?>"></script>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function() {
		<?= $starters.$inicializa_popup."\n"; ?>
		$("a.iframe").fancybox({
			'zoomSpeedIn': 300,
			'zoomSpeedOut': 300,
			'hideOnContentClick': false,
			'width': 700,
			'height': 500
		});
		var evt = $.browser.msie ? "click" : "change";
		$("#curso").bind(evt, atualiza_modalidades);
		$("#catalogo").bind(evt, atualiza_modalidades);
		$("#ok").click(manda_form);
		$("#limpar").click(limpa_form);
		$('#select_modalidade').addClass("ac_loading");
		$('#select_modalidade').load('<?= CONFIG_URL; ?>ajax/modalidades.php?c=<?= $Usr->getCurso(true)->getNumero(true); ?>&a=<?= $Usr->getCatalogo(true); ?>&s=<?= $Usr->getModalidade(true)->getSigla(true); ?>&o=1', {}, function(){$('#select_modalidade').removeClass("ac_loading");});

		$.guaycuru.tooltip("TT_tips", "Dicas:", "<ul><li>Para eliminar uma Disciplina da &Aacute;rvore, clique nela.</li><li>Para colocar uma disciplina de volta na &Aacute;rvore, busque pela disciplina (no menu Disciplinas) e, na p&aacute;gina dela, selecione a op&ccedil;&atilde;o \"Nenhuma das Anteriores\".</li><li>Para eliminar uma Eletiva, busque pela disciplina que foi cursada como eletiva, e marque que a mesma foi cursada.</li></ul>", {});

	});
	atualiza_modalidades = function() {
		$('#select_modalidade').addClass("ac_loading");
		$('#select_modalidade').load('<?= CONFIG_URL; ?>ajax/modalidades.php?c='+$('#curso').val()+'&a='+$('#catalogo').val()+'&o=1', {}, function(){
			$('#select_modalidade').removeClass("ac_loading");
		});
	};
	manda_form = function() {
		document.location = '<?= CONFIG_URL; ?>arvore/?curso='+$("#curso").val()+'&modalidade='+$("#modalidade").val()+'&catalogo='+$("#catalogo").val()+'&cp='+$("#cp").val();
	};
	limpa_form = function() {
		document.location = '<?= CONFIG_URL; ?>arvore/';
	};
	Elimina = function(sigla, a, r) {
		$.post('<?= CONFIG_URL; ?>ajax/disciplina.php', {sigla: sigla, e: '1', a: a, r: r, v: '1'}, function() {
			history.go(0);
		});
	}
	// ]]>
</script>
<?= $menus; ?>
<div class="tip" id="arvore_tip">Dica: Para acessar diretamente sua &aacute;rvore, use <span class="link"><a href="http://gde.ir/arvore">http://gde.ir/arvore</a></span></div>
<h2>Curr&iacute;culo de <?= $Usr->getNome_Completo(true); ?></h2>
<?php if($meu) { ?>
	<form method="get" action="<?= CONFIG_URL; ?>arvore/">
		<table cellpadding="1" cellspacing="0" border="0" width="90%">
			<tr>
				<td width="10%">Cat&aacute;logo:</td>
				<td><select id="catalogo" name="catalogo"><?=$catalogos;?></select></td>
			</tr>
			<tr>
				<td width="10%">Curso:</td>
				<td><select id="curso" name="curso"><?=$lista_cursos; ?></select></td>
			</tr>
			<tr>
				<td width="10%">Modalidade:</td>
				<td id="select_modalidade"><select id="modalidade" name="modalidade"><option value="">Indiferente</option></select></td>
			</tr>
			<tr>
				<td width="10%">Completa:</td>
				<td><select id="cp" name="cp"><option value='0'<?=(($completa)?null:' selected="selected"');?>>N&atilde;o</option><option value='1'<?=(($completa)?' selected="selected"':null);?>>Sim</option></select></td>
			</tr>
		</table>
		<br />
		<table cellpadding="0" cellspacing="0" border="0" width="90%">
			<tr>
				<td colspan='2'><input type="submit" id="ok" value=" " alt="Consultar" class="botao_consultar" /> <input type="button" id="limpar" value=" " alt="Limpar" class="botao_limpar" /></td>
			</tr>
		</table>
	</form>
	<br />
	<a class="iframe" href="<?= CONFIG_URL; ?>arvore/?e=1&amp;cr=<?= $curso; ?>&amp;ct=<?= $catalogo; ?>">Curr&iacute;culo Pleno</a> | <a class="iframe" href="<?= CONFIG_URL; ?>arvore/?e=2&amp;cr=<?= $curso; ?>&amp;ct=<?= $catalogo; ?>">Sugest&otilde;es de Curr&iacute;culo</a>
	<br /><br />
<?php } else { ?>
	<table cellpadding="1" cellspacing="0" border="0" width="90%">
		<tr>
			<td width="10%">Cat&aacute;logo:</td>
			<td><?= $Usr->getCatalogo(true); ?></td>
		</tr>
		<tr>
			<td width="10%">Curso:</td>
			<td><?= $Usr->getCurso(true)->getNome(true); ?> (<?= $Usr->getCurso(true)->getNumero(true); ?>)</td>
		</tr>
		<tr>
			<td width="10%">Modalidade:</td>
			<td><?= $Usr->getModalidade(true)->getNome(true); ?></td>
		</tr>
	</table>
	<br /><br />
	<?php
}
if(($catalogo == null) || ($curso == null))
	echo "<strong>Erro:</strong> N&atilde;o &eacute; poss&iacute;vel montar a &aacute;rvore: Cat&aacute;logo ou Curso de Gradua&ccedil;&atilde;o n&atilde;o especificado no Perfil!<br />";
else if($continua === false)
	echo "<strong>Erro:</strong> N&atilde;o foi poss&iacute;vel montar a &aacute;rvore. Curso (".$curso.") / Modalidade (".$modalidade.") n&atilde;o encontrados no Cat&aacute;logo selecionado!";
else {
	//if(count($Eletivas) == 0) echo "<strong>Aten&ccedil;&atilde;o:</strong> As Eletivas ainda n&atilde;o foram configuradas para o curso selecionado!<br />";
	?>
	<div style="display: block" id="mostra">
		<a href="#" onclick="document.getElementById('integralizacao').style.display='block'; document.getElementById('mostra').style.display='none'; return false;">Mostrar Integraliza&ccedil;&atilde;o</a>
	</div>
	<div style="display: none" id="integralizacao">
		<a href="#" onclick="document.getElementById('integralizacao').style.display='none'; document.getElementById('mostra').style.display='block'; return false;">Ocultar Integraliza&ccedil;&atilde;o</a>
		<br /><div><?= $Arvore->Integralizacao(); ?></div>
	</div><br/>
	<span class="formInfo"><a href="#" id="TT_tips">Dicas</a></span>
	<br /><br />
	<?= $div_popup; ?>
	<br />Obs: Caso existam dois conjuntos de pr&eacute;-requisitos presentes integralmente no cat&aacute;logo, apenas um deles ser&aacute; exibido.<br />
	<?php
}
echo $FIM;
?>
