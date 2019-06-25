<?php

define('TITULO', 'Login');
define('NO_REDIRECT', true);
define('NO_DENIAL', true);

if(isset($_GET['login_dac']))
	define('NO_HTML', true);

require_once('../common/common.inc.php');

if($_Usuario->getID() != null)
	die("Voc&ecirc; j&aacute; est&aacute; logado!<script>document.location = '".CONFIG_URL."'</script>".$FIM);

if(isset($_GET['login_dac'])) {
	header('Location: '.CONFIG_URL_LOGIN_DAC);
	exit();
}

if(isset($_GET['token'])) { ?>
<h1>Aguarde...</h1>
<form name="formulario" id="form_login" method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/login.php" data-destino="<?= CONFIG_URL; ?>" data-sucesso=" ">
	<input type="hidden" name="token" id="token" value="<?php if(!empty($_GET['token'])) echo htmlspecialchars($_GET['token']); ?>" />
</form>
<script type="text/javascript">
	$.guaycuru.aguarde();
	<?php if(isset($_SESSION['trocar_senha'])) { ?>
	document.location = '<?= CONFIG_URL; ?>cadastro/?token=' + $("#token").val();
	<?php } else { ?>
	$(document).ready(function() {
		$("#form_login").submit();
	});
	<?php } ?>
</script>
<?php
} elseif((isset($_GET['old'])) || (CONFIG_OLD_LOGIN === true)) {

?>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function(){
	$.guaycuru.tooltip("TT_senha", "Senha:", "<ul><li>Esta &eacute; sua senha do GDE, que n&atilde;o &eacute; necessariamente igual &agrave; da DAC.</li><li>Se voc&ecirc; n&atilde;o tem ou esqueceu sua senha, clique em \"Esqueci minha senha / N&atilde;o tenho senha\".</li></ul>", {});
	$("#alterar_senha").click(function() {
		$.post("<?= CONFIG_URL; ?>ajax/cadastro.php", {alterar: 1}, function() {
			$.guaycuru.confirmacao("Para trocar sua senha, primeiro fa&ccedil;a login pelo site da DAC!", "<?= CONFIG_URL_LOGIN_DAC; ?>");
		});
		return false;
	});
	$("#login").focus();
});

// ]]>
</script>

<div id="coluna_esquerda_wrapper">
	<div id="coluna_esquerda">
		<form name="formulario" id="form_login" method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/login.php" data-destino="<?= CONFIG_URL; ?>" data-sucesso=" ">
		<input type="hidden" name="old" value="1" />
		<input type="hidden" name="token" value="<?php if(!empty($_GET['token'])) echo htmlspecialchars($_GET['token']); ?>" />
		<table>
			<tr>
				<td><strong>RA / Email / Login:</strong></td>
				<td><input type="text" id="login" name="login" value="<?= (isset($_GET['login'])) ? htmlspecialchars($_GET['login']) : ''; ?>" class="required" /></td>
			</tr>
			<tr>
				<td><strong>Senha:</strong></td>
				<td><input type="password" name="senha" class="required" /> <span class="formInfo"><a href="#" id="TT_senha">?</a></span></td>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="lembrar" value="t" id="lembrar_t" /><label for="lembrar_t">Permanecer logado</label></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" name="OK" value=" " alt="OK" class="botao_ok" /></td>
			</tr>
			<tr>
				<td colspan="2"><a href="<?= CONFIG_URL_LOGIN_DAC; ?>">Fazer login pelo site da DAC</a></td>
			</tr>
			<tr>
				<td colspan="2"><a href="#" id="alterar_senha">Esqueci minha senha / N&atilde;o tenho senha</a></td>
			</tr>
		</table>
		</form>
	</div>
</div>

<?php

} elseif(!isset($_GET['token'])) {
	echo "TOKEN Inv&aacute;lido!";
}

echo $FIM;
