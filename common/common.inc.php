<?php

namespace GDE;

define('REVISION', 1);

// Composer Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Doctrine (ORM)
require_once(__DIR__.'/doctrine.inc.php');

// Util
// ToDo: Why you no autoload?
require_once(__DIR__.'/../classes/GDE/Util.inc.php');

// Define o timezone e o encoding padrao
date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding("UTF-8");

// Password hashing
if(version_compare(PHP_VERSION, '5.5.0') < 0)
	require_once(__DIR__.'/password/password.php');

// ToDo: Remover isto, usado soh para dev
error_reporting(E_ALL & ~E_STRICT);

/*session_name('GDE');
session_start();*/

if((defined('JSON')) && (JSON === true)) {
	define('AJAX', true);
	header('Content-type: application/json');
}
if((defined('AJAX')) && (AJAX === true)) {
	define('HTML', false);
	define('SEM_CACHE', true);
	define('LOGIN_REDIRECIONAR', false);
}

if(defined('NO_HTML'))
	define('HTML', false);

if(!defined('HTML'))
	define('HTML', true);

if((defined('SEM_CACHE')) && (SEM_CACHE === TRUE)) {
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 16 Dec 2003 16:38:00 GMT");
}

if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1)) {
	$TS_INICIO = microtime(true);
	if($_SESSION['admin']['debug'] >= 2)
		error_reporting(E_ALL);
	function Tempo_Geracao($inicio) {
		if((!defined('NO_HTML')) || (NO_HTML === false))
			echo "<hr />Tempo de Gera&ccedil;&atilde;o da P&aacute;gina: ".(microtime(true)-$inicio)." segundo(s)";
	}
	register_shutdown_function("Tempo_Geracao", $TS_INICIO);
}

$_Usuario = false;

//if((isset($_GET['w3c'])) && ($_GET['w3c'] == '17239853'))
	//$_SESSION['id_usuario'] = 1;

if((!defined('NO_LOGIN_CHECK')) || (NO_LOGIN_CHECK === false)) {
	$_Usuario = Usuario::Ping();
	if($_Usuario->getID() == null) {
		if(!defined('NO_REDIRECT') || NO_REDIRECT === false) {
			$_SESSION['last_pg'] = $_SERVER['REQUEST_URI'];
			header("Location: ".CONFIG_URL.CONFIG_URL_LOGIN);
		}
		if(!defined('NO_DENIAL') || NO_DENIAL === false)
			Sem_Permissao();
	}
}

if((defined('NO_SESSION')) && (NO_SESSION === true))
	session_write_close();

/*if(($_Usuario == null) || ($_Usuario->getAdmin() === false))
	die("O GDE est&aacute; em Manuten&ccedil;&atilde;o, volte mais tarde...");*/

//<meta name="robots" content="noindex, nofollow" />

if((!defined('HTML')) || (HTML === true)) {
	if(!defined('TITULO'))
		define('TITULO', '');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br" >
<head>
<?php if((defined('NO_CACHE')) && (NO_CACHE == TRUE)) echo "	<meta http-equiv=\"Pragma\" content=\"no-cache\" />\r\n"; ?>
	<link rel="search" type="application/opensearchdescription+xml" href="<?= CONFIG_URL; ?>opensearch.xml" title="Procurar no GDE" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
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
    <?php if(($_Usuario !== false) && ((!isset($_SESSION['admin_su'])) || ($_SESSION['admin_su'] === false))) { ?><script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.chat.js?<?= REVISION; ?>"></script><?php } ?>
	<script type="text/javascript">var _gaq = _gaq || []; _gaq.push(['_setAccount', 'UA-3315545-3']); _gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })();</script>
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/dropdown_menu.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/gde.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/tabs.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.datepicker.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.fancybox.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.jcontext.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.jdMenu.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.jhelpertip.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.popup.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.ui.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/fullcalendar.css?<?= REVISION; ?>" type="text/css" />
	<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/jquery.tablesorter.css?<?= REVISION; ?>" type="text/css" />
	<link rel="apple-touch-icon" href="<?= CONFIG_URL; ?>web/images/apple-touch-icon.png" />
</head>
<body id="page_bg">
<?php
	if((!defined('JUST_INC')) || (JUST_INC === false)) {
		if($_Usuario !== false) {
?>
<script type="text/javascript">
// <![CDATA[
var CONFIG_URL = '<?= CONFIG_URL; ?>';
// Informacoes para o chat
<?php if(($_Usuario !== false) && (((!isset($_SESSION['admin_su'])) || ($_SESSION['admin_su'] === false)))) { ?>
var meu_id = '<?= $_Usuario->getID(); ?>';
var meu_status = '<?= (CONFIG_CHAT_ATIVO) ? $_Usuario->getChat_Status(false, true) : 'z'; ?>';
var minha_foto_th = '<?= $_Usuario->getFoto(true, true); ?>';
<?php }
 if((isset($_SESSION['admin_su'])) && ($_SESSION['admin_su'] !== false)) { ?>
var Un_SU = function() {
	$.guaycuru.abreControlador('ControlAdmin.php?unsu');
}
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
<?php } ?>
});
// ]]>
</script>
<?php } ?>
	<iframe src="about:blank" width="0" height="0" frameborder="0" id="controle" name="controle"></iframe>
		<div id="top">
			<div id="mini_logo">
				<a href="<?=CONFIG_URL;?>index/" title="Home"><img src="<?= CONFIG_URL; ?>web/images/mini_logo.gif" alt="GDE" width="128" height="38" /></a>
			</div> 
	<?php
	if($_Usuario != null) {
		$ultima_atualizacao = '-';//$_GDE['DB']->UserDate($_GDE['DB']->Execute("SELECT ultima_atualizacao FROM gde_dados WHERE id = 1")->fields['ultima_atualizacao'], 'd/m/Y');
	?>
			<div id="top_menu" class="menu ui-corner-bottom">
				<ul>
		<?php if($_Usuario->getAdmin() === true) { ?>
					<li><a href="#" onclick="return false;">Admin</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdmin.php">Super P&aacute;gina!!!</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdminCadastros.php">Usu&aacute;rios Cadastrados</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdminCadastros.php?inativos">Usu&aacute;rios Inativos</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdminAluno.php">Novo Aluno</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdminUsuario.php">Novo Usu&aacute;rio</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdminDisciplina.php">Nova Disciplina</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdminOferecimento.php">Novo Oferecimento</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/VisaoAdminAcontecimento.php">Novo Acontecimento</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/AdminAutorizarGrupos.php">Autorizar Grupos</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/AdminAutorizarColaboracoes.php">Autorizar Colabora&ccedil;&otilde;es</a></li>
							<li><a href="<?= CONFIG_URL; ?>stats/awstats.gde.html">Estat&iacute;sticas</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/desenha_grafico_cadastros.php?dt=2011-01-1">Gr&aacute;fico Cadastros</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>visoes/desenha_grafico_cadastros2.php?dt=2011-01-1">Gr&aacute;fico dCadastros/dt</a></li>
						</ul>
					</li>
		<?php } elseif((isset($_SESSION['admin_su'])) && ($_SESSION['admin_su'] !== false)) { ?>
					<li><a href="#" onclick="Un_SU(); return false;">Un-SU</a></li>
		<?php } ?>
					<li><a href="#" onclick="return false;">Acad&ecirc;mico</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>visoes/Arvore.php">&Aacute;rvore / Integraliza&ccedil;&atilde;o</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/Avaliar.php">Avaliar Professores</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/Eliminador.php">Eliminar Disciplinas</a></li>
							<li><a href="<?= CONFIG_URL; ?>mapa/">Mapa do Campus</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/Planejador.php">Planejador</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>visoes/Rankings.php">Rankings</a></li>
						</ul>
					</li>
					<li><a href="#" onclick="return false;">Social</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>visoes/EditarPerfil.php">Editar Perfil</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/Configuracoes.php">Configura&ccedil;&otilde;es da Conta</a></li>
							<li><a href="<?= CONFIG_URL; ?>visoes/Amigos.php">Meus Amigos</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>recomendar/">Convidar um Amigo</a></li>
							<!-- <li><a href="<?= CONFIG_URL; ?>visoes/CadastroGrupo.php">Criar Grupo</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>visoes/Grupos.php">Meus Grupos</a></li> -->
						</ul>
					</li>
					<li><a href="#" onclick="return false;">Ajuda</a>
						<ul>
							<li><a href="<?= CONFIG_URL; ?>contato/">Contato</a></li>
							<li><a href="<?= CONFIG_URL; ?>estatisticas/">Estat&iacute;sticas</a></li>
							<li><a href="<?= CONFIG_URL; ?>faq/">Perguntas Frequentes</a></li>
							<li><a href="<?= CONFIG_URL; ?>sobre/">Sobre o GDE</a></li>
							<li><a class="ui-corner-bottom" href="<?= CONFIG_URL; ?>termos/">Termos de Uso</a></li>
						</ul>
					</li>
					<li><a href="#" onclick="Logout(); return false;">Sair</a></li>
				</ul>
			</div>
			<form id="form_busca_universal" method="get" action="Busca.php">
				<div id="top_busca_simples">
					<input type="text" id="menu_input_busca" name="q" />
				</div>
				<div id="top_botoes_busca">
					<input type="submit" id="botao_busca_universal" name="buscar" value="OK" />
					<div id="busca_avancada_wrapper">
						<a href="Busca.php" title="Busca Avan&ccedil;ada" id="toggle_menu_busca_avancada"><img id="botao_busca_avancada" src="<?= CONFIG_URL; ?>web/images/menu-avancado.png" alt="+" width="11" height="38" /></a>
						<div id="div_menu_busca_avancada">
							<a href="Busca.php#tab_alunos">Buscar Alunos</a>
							<a href="Busca.php#tab_professores">Buscar Professores</a>
							<a href="Busca.php#tab_disciplinas">Buscar Disciplinas</a>
							<a href="Busca.php#tab_oferecimentos">Buscar Oferecimentos</a>
							<a href="Busca.php#tab_salas">Buscar Salas</a>
							<a href="Busca.php#tab_grupos">Buscar Grupos</a>
						</div>
					</div>
				</div>
			</form>
	<?php } else {
	$ultima_atualizacao = '-/-/-';
		echo "&nbsp;";
	} ?>
		</div>
	<div id="wrapper" class="center">
		<div id="content_bg">
			<div id="content">
<?php if((CONFIG_CHAT_ATIVO) && ($_Usuario !== false) && ((!isset($_SESSION['admin_su'])) || ($_SESSION['admin_su'] === false))) { ?>
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
	$FIM = "
				<div id=\"rodape\">
					<hr style=\"color: #B2B2B2; background-color: #B2B2B2; height: 1px; margin: 10px 0 10px 0; border: 0 none\" />
					<div id=\"usuarios_online\"><span>Usu&aacute;rios online</span>: <span id=\"contador_usuarios_online\">".Usuario::Conta_Online(false)."</span></div>
					<br />&Uacute;ltima Atualiza&ccedil;&atilde;o do sistema: ".$ultima_atualizacao." - Vers&atilde;o 2.5
					<br /><br /><strong>Aviso:</strong> O GDE n&atilde;o &eacute; um sistema oficial da Unicamp, e tem por &uacute;nico objetivo auxiliar alunos na vida acad&ecirc;mica.
					<br /><br /><i>"./* ToDo Quote() */''."</i>
					<hr style=\"color: #B2B2B2; background-color: #B2B2B2; height: 1px; margin: 10px 0 10px 0; border: 0 none\" />
					<div style=\"width: 210px; height: 42px; margin: auto;\">
						<a href=\"http://www.guaycuru.net\" title=\"Desenvolvido por Felipe Guaycuru de C. B. Franco\" target=\"_blank\" style=\"float: left; margin-top: 10px;\"><img src=\"".CONFIG_URL."web/images/logo_guaycuru.gif\" border=\"0\" alt=\"Desenvolvido por Felipe Guaycuru de C. B. Franco\" width=\"100\" height=\"21\" /></a>
					</div>
					<!-- <br /><div style=\"width: 125px; margin: auto;\"><a href=\"http://validator.w3.org/check?uri=referer\" target=\"_blank\"><img src=\"".CONFIG_URL."web/images/w3c_xhtml.gif\" border=\"0\" alt=\"Valid XHTML 1.0 Transitional !\" width=\"60\" height=\"21\" /></a> <a href=\"http://jigsaw.w3.org/css-validator/check/referer\" target=\"_blank\"><img src=\"".CONFIG_URL."web/images/w3c_css.gif\" alt=\"Valid CSS !\" width=\"60\" height=\"21\" /></a></div>-->
				</div>
			</div>
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

