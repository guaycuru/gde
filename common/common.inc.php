<?php

namespace GDE;

define('REVISION', '20260727c');

// Composer Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Config
require_once(__DIR__.'/config.inc.php');

// Uncaught exception handler
require_once(__DIR__.'/exceptions.inc.php');

// Aumenta o tempo limite para 90 segundos
set_time_limit(90);

// Define o timezone e o encoding padrao
date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding("UTF-8");

// Ignora erros irrelevantes relativos a imagens
ini_set('gd.jpeg_ignore_warning', 1);

// Configura os parametros do cookie da sessao
session_set_cookie_params(0, parse_url(CONFIG_URL, PHP_URL_PATH), null, false, true);

// Inicia a sessao
session_name('GDES');
session_start();

if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 3))
	define('DEBUG_DB_LOGGER', true);

// Doctrine (ORM)
require_once(__DIR__.'/doctrine.inc.php');

if((defined('JSON')) && (JSON === true)) {
	define('AJAX', true);
	header('Content-type: application/json');
}
if((defined('AJAX')) && (AJAX === true)) {
	define('HTML', false);
	define('NO_CACHE', true);
	define('NO_REDIRECT', true);
}

if(defined('NO_HTML'))
	define('HTML', false);

if(!defined('HTML'))
	define('HTML', true);

if((defined('NO_CACHE')) && (NO_CACHE === TRUE)) {
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 16 Dec 2003 16:38:00 GMT");
}

if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1)) {
	$TS_INICIO = microtime(true);
	if($_SESSION['admin']['debug'] >= 2)
		error_reporting(E_ALL);
	register_shutdown_function(function ($inicio) {
		if((!defined('HTML')) || (HTML === true))
			echo "<hr />Tempo de Gera&ccedil;&atilde;o da P&aacute;gina: ".(microtime(true)-$inicio)." segundo(s)";
	}, $TS_INICIO);
}

$_Usuario = null;
if((!defined('NO_LOGIN_CHECK')) || (NO_LOGIN_CHECK === false)) {
	$_Usuario = Usuario::Ping();
	if($_Usuario->getID() == null) {
		if(!defined('NO_REDIRECT') || NO_REDIRECT === false) {
			$_SESSION['last_pg'] = $_SERVER['REQUEST_URI'];
			header("Location: ".CONFIG_URL.CONFIG_URL_LOGIN);
		}
		if(!defined('NO_DENIAL') || NO_DENIAL === false) {
			if((defined('JSON')) && (JSON === true))
				Base::Error_JSON('Login necessário.');
			exit;
		}
	} else {
		// Tenta prevenir CSRF
		Util::CSRFP((defined('AJAX')) && (AJAX === true));
	}
}
if(($_Usuario !== null) && ($_Usuario->getAdmin() === true)) {
	define('GDE_ADMIN', true);
	if(!empty($_SESSION['admin_su']))
		$_Usuario = Usuario::Load($_SESSION['admin_su']);
} else
	define('GDE_ADMIN', false);

if((defined('NO_SESSION')) && (NO_SESSION === true))
	session_write_close();

if((!defined('HTML')) || (HTML === true)) {
	if(!defined('TITULO'))
		define('TITULO', '');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php if((defined('NO_CACHE')) && (NO_CACHE == TRUE)) echo "	<meta http-equiv=\"Pragma\" content=\"no-cache\" />\r\n"; ?>
	<link rel="search" type="application/opensearchdescription+xml" href="<?= CONFIG_URL; ?>opensearch.xml" title="Procurar no GDE" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="theme-color" content="#0e4c5f" />
	<title>GDE - <?= TITULO; ?></title>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery-ui-1.8.24.min.js"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.ba-hashchange.min.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.easing.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.fancybox.js?<?= REVISION; ?>"></script>
	<script type='text/javascript' src="<?= CONFIG_URL; ?>web/js/jquery.fullcalendar.min.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.guaycuru.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.jcontext.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.jdMenu.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.jhelpertip.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.maskedinput.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.numberinput.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.popup.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.positionBy.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.tablesorter.min.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.timers.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.validate.min.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.watcherkeys.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.common.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.42.js?<?= REVISION; ?>"></script>
	<script type="text/javascript">var _gaq = _gaq || []; _gaq.push(['_setAccount', 'UA-3315545-3']); _gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })();</script>
	<?php /* CSS dos plugins primeiro; gde.css por ultimo para poder sobrescrever. */ ?>
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.datepicker.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.fancybox.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.jcontext.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.jdMenu.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.jhelpertip.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.popup.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.ui.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/fullcalendar.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.tablesorter.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/gde.css?<?= REVISION; ?>" type="text/css" />
	<link rel="apple-touch-icon" href="<?= CONFIG_URL; ?>web/images/apple-touch-icon.png" />
</head>
<body id="page_bg">
<script type="text/javascript">
// <![CDATA[
var CONFIG_URL = '<?= CONFIG_URL; ?>';
// ]]>
</script>
<?php
	if((!defined('JUST_INC')) || (JUST_INC === false)) {
		if($_Usuario !== null) {
?>
<script type="text/javascript">
// <![CDATA[
// Informacoes para o chat
<?php if(($_Usuario !== null) && (empty($_SESSION['admin_su']))) { ?>
var ID_USUARIO = '<?= $_Usuario->getID(); ?>';
var meu_status = '<?= (CONFIG_CHAT_ATIVO) ? $_Usuario->getChat_Status(false, true) : 'z'; ?>';
var minha_foto_th = '<?= $_Usuario->getFoto(true, true, true); ?>';
<?php } else { ?>
var ID_USUARIO = '';
<?php } ?>
$(document).ready(function(){
	$('body').watcherkeys({callback: $.guaycuru.changeIt});
	$("#menu_input_busca").Valor_Padrao('Buscar...', 'padrao');
	$("#form_busca_universal").submit(function() {
		if($("#menu_input_busca").val().length < 1 || $("#menu_input_busca").hasClass('padrao'))
			return false;
	});
	$("#toggle_menu_busca_avancada").click(function() {
		$("#div_menu_busca_avancada").toggle('fast', function() {
			if($(this).is(":visible"))
				$(document).click(function() { $("#div_menu_busca_avancada").hide('fast'); });
		});
		return false;
	});
<?php if(isset($_GET['gravity'])) { ?>
	$("img").removeClass("escala");
	$("img,span,input,select,h1,h2,h3,h4,h5,ul.jd_menu").addClass("box2d");
	$("body").append('<div id="canvas"></div>');
	$.getScript('<?= CONFIG_URL; ?>web/js/protoclass.js', function() {
		$.getScript('<?= CONFIG_URL; ?>web/js/box2d.js', function() {
			$.getScript('<?= CONFIG_URL; ?>web/js/gravity.js');
		});
	});
<?php } if(!empty($_SESSION['admin_su'])) { ?>
	$("#admin_unsu").click(function() {
		$.post('<?= CONFIG_URL; ?>/ajax/admin.php', {unsu: 1}, function(res) {
			if(res && res.ok) {
				alert('UnSU executado com sucesso!');
				document.location = '<?= CONFIG_URL; ?>';
			} else
				alert('Erro!');
		});
	});
<?php } ?>
});
// ]]>
</script>
<?php } ?>
	<iframe src="about:blank" width="0" height="0" frameborder="0" id="controle" name="controle"></iframe>
		<header id="top">
			<div id="mini_logo">
				<a href="<?=CONFIG_URL;?>" title="Home"><span class="logo_marca">GDE</span><span class="logo_beta">&beta;</span></a>
			</div>
	<?php
	if($_Usuario !== null) {
		$ultima_atualizacao = Dado::Pega_Dados('ultima_atualizacao')->format('d/m/Y');
	?>
			<nav id="top_menu" class="menu">
				<ul>
		<?php if(!empty($_SESSION['admin_su'])) { ?>
					<li><a href="#" id="admin_unsu">Un-SU</a></li>
		<?php } elseif($_Usuario->getAdmin() === true) { ?>
					<li><a href="#" onclick="return false;">Admin</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>admin/">Super P&aacute;gina!!!</a></li>
							<li><a href="<?= CONFIG_URL; ?>admin-acontecimento/">Novo Acontecimento</a></li>
							<li><a href="<?= CONFIG_URL; ?>admin-colaboracoes-oferecimento/">Colabora&ccedil;&otilde;es de Oferecimento</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>admin-colaboracoes-professor/">Colabora&ccedil;&otilde;es de Professor</a></li>
						</ul>
					</li>
		<?php } ?>
					<li><a href="#" onclick="return false;">Acad&ecirc;mico</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>arvore/">&Aacute;rvore / Integraliza&ccedil;&atilde;o</a></li>
							<li><a href="<?= CONFIG_URL; ?>avaliar/">Avaliar Professores</a></li>
							<li><a href="<?= CONFIG_URL; ?>eliminador/">Eliminar Disciplinas</a></li>
							<li><a href="<?= CONFIG_URL; ?>mapa/">Mapa do Campus</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>planejador/">Planejador</a></li>
							<!-- <li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>rankings/">Rankings</a></li> -->
						</ul>
					</li>
					<li><a href="#" onclick="return false;">Social</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>editar-perfil/">Editar Perfil</a></li>
							<li><a href="<?= CONFIG_URL; ?>configuracoes/">Configura&ccedil;&otilde;es da Conta</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>amigos/">Meus Amigos</a></li>
						</ul>
					</li>
					<li><a href="#" onclick="return false;">Ajuda</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>contato/">Contato</a></li>
							<li><a href="<?= CONFIG_URL; ?>estatisticas/">Estat&iacute;sticas</a></li>
							<li><a href="https://github.com/guaycuru/gde/wiki/FAQ" target="_blank">Perguntas Frequentes</a></li>
							<li><a href="<?= CONFIG_URL; ?>sobre/">Sobre o GDE</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>termos/">Termos de Uso</a></li>
						</ul>
					</li>
					<li><a href="#" onclick="Logout(); return false;">Sair</a></li>
				</ul>
			</nav>
			<form id="form_busca_universal" method="get" action="<?= CONFIG_URL; ?>busca/">
				<div id="top_busca_simples">
					<input type="text" id="menu_input_busca" name="q" />
				</div>
				<div id="top_botoes_busca">
					<input type="submit" id="botao_busca_universal" name="buscar" value="Buscar" />
					<div id="busca_avancada_wrapper">
						<a href="<?= CONFIG_URL; ?>busca/" title="Busca Avan&ccedil;ada" id="toggle_menu_busca_avancada"><span id="botao_busca_avancada" aria-hidden="true">&#43;</span></a>
						<div id="div_menu_busca_avancada">
							<a href="<?= CONFIG_URL; ?>busca/#tab_alunos">Buscar Alunos</a>
							<a href="<?= CONFIG_URL; ?>busca/#tab_professores">Buscar Professores</a>
							<a href="<?= CONFIG_URL; ?>busca/#tab_disciplinas">Buscar Disciplinas</a>
							<a href="<?= CONFIG_URL; ?>busca/#tab_oferecimentos">Buscar Oferecimentos</a>
							<a href="<?= CONFIG_URL; ?>busca/#tab_salas">Buscar Salas</a>
						</div>
					</div>
				</div>
			</form>
	<?php } else {
	$ultima_atualizacao = '-/-/-';
		echo "&nbsp;";
	} ?>
			<a href="https://github.com/guaycuru/gde" target="_blank" rel="noopener" title="Colabore com o GDE, abra seu Pull Request!" id="fork-me-ribbon">Colabore no GitHub</a>
		</header>
	<div id="wrapper" class="center">
		<div id="content_bg">
			<main id="content">
<?php if((CONFIG_CHAT_ATIVO) && ($_Usuario !== null) && ((!isset($_SESSION['admin_su'])) || ($_SESSION['admin_su'] === false))) { ?>
				<div id="chatMiguxo" class="chat_principal">
					<div id="chatAmigos" class="chat_amigos ui-corner-top">
					</div>
					<div id="chatOpcoes" class="chat_opcoes ui-corner-top ui-corner-bottom">
						<div id="chatOpcoesLink" class="chat_opcoes_link">
							<img src="<?= CONFIG_URL; ?>web/images/chat_icon.png" alt="Chat" title="Chat" /> <a href="#" id="linkChat">Chat</a>
						</div>
					</div>
				</div>
				<div id="chatStatus" class="chat_status ui-corner-all">
					<div class="chatStatusSelect">
						<a id="chat_status_<?= $_Usuario->getAdmin() ? 'x' : 'd' ?>" href="#"><img src="<?= CONFIG_URL; ?>web/images/status_<?= $_Usuario->getAdmin() ? 'x' : 'd' ?>.png" alt="Dispon&iacute;vel" title="Dispon&iacute;vel" /></a>
						<a id="chat_status_o" href="#"><img src="<?= CONFIG_URL; ?>web/images/status_o.png" alt="Ocupado" title="Ocupado" /></a>
						<a id="chat_status_i" href="#"><img src="<?= CONFIG_URL; ?>web/images/status_off.png" alt="Invisivel" title="Invisivel" /></a>
						<a id="chat_status_z" href="#"><img src="<?= CONFIG_URL; ?>web/images/status_z.png" alt="Desconectado" title="Desconectado" /></a>
					</div>
				</div>

<?php
}
	$versao_tag = "";
	if(($_Usuario !== null) && ($_Usuario->getAdmin() === true) && CONFIG_APP_VERSION) {
		$versao_tag = " (tag: ".CONFIG_APP_VERSION.")";
	}
	$FIM = "
			</main>
			<footer id=\"rodape\">
				<div class=\"rodape_meta\">
					<span id=\"usuarios_online\"><span>Usu&aacute;rios online</span>: <span id=\"contador_usuarios_online\">".Usuario::Conta_Online(false)."</span></span>
					<span>&Uacute;ltima atualiza&ccedil;&atilde;o: ".$ultima_atualizacao."</span>
					<span>Vers&atilde;o 2.6".$versao_tag."</span>
				</div>
				<span class=\"rodape_aviso\"><strong>Aviso:</strong> O GDE n&atilde;o &eacute; um sistema oficial da Unicamp, e tem por &uacute;nico objetivo auxiliar alunos na vida acad&ecirc;mica.</span>
				<span class=\"rodape_quote\">".Quote::Qualquer_Uma(true)."</span>
				<span class=\"rodape_credito\">
					<a href=\"http://www.guaycuru.net\" title=\"Desenvolvido por Felipe Guaycuru de C. B. Franco\" target=\"_blank\" rel=\"noopener\">Desenvolvido por Felipe Guaycuru de C. B. Franco</a>
				</span>
			</footer>
		</div>
	</div>
</body>
</html>";
	} else {
		$FIM = "
</body>
</html>";
	}
} else {
	$FIM = null;
}
