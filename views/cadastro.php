<?php

namespace GDE;

define('TITULO', 'Cadastro');
define('NO_LOGIN_CHECK', true);

require_once('../common/common.inc.php');

if(empty($_GET['token']))
	die('TOKEN Inv&aacute;lido!');

list($resultado, $identificador, $tipo) = DAC::Validar_Token($_GET['token']);

if($resultado === false)
	die('TOKEN Inv&aacute;lido!');
elseif($tipo == 'A')
	$campo = 'ra';
elseif($tipo == 'D')
	$campo = 'matricula';
else
	die('Infelizmente voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar o GDE!');

$Usuario = Usuario::Por_Unique($identificador, $campo, true);

if($Usuario !== null) {
	if((!isset($_SESSION['trocar_senha'])) && (!isset($_GET['ns'])))
		die("Erro, usu&aacute;rio j&aacute; cadastrado!");
	else {

		?>
		<script type="text/javascript">
			// <![CDATA[
			$(document).ready(function(){
				$.guaycuru.tooltip("TT_senha", "Senha:", "<ul><li>A senha n&atilde;o precisa ser a mesma da DAC, e ser&aacute; usada para acessar o GDE pelo endere&ccedil;o http://gde.guaycuru.net</li></ul>", {});
			});
			// ]]>
		</script>
		Defina sua nova senha no formul&aacute;rio abaixo:<br /><br />
		<form method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/cadastro.php" data-destino="<?= CONFIG_URL.CONFIG_URL_LOGIN; ?>?token=#ID#">
			<input type="hidden" id="token" name="token" value="<?= htmlspecialchars($_GET['token']); ?>" />
			<input type="hidden" name="trocar_senha" value="1" />
			<table border="0">
				<tr>
					<td><strong><?= (($tipo == 'A') ? 'RA' : 'Matr&iacute;cula'); ?>:</strong></td>
					<td><input type="text" name="matricula" id="matricula" maxlength="6" value="<?= intval($identificador); ?>" readonly="readonly" /></td>
				</tr>
				<tr>
					<td><strong>Senha:</strong></td>
					<td><input type="password" name="senha" id="senha" /> <span class="formInfo"><a href="#" id="TT_senha">?</a></span></td>
				</tr>
				<tr>
					<td><strong>Re-digite a senha:</strong></td>
					<td><input type="password" name="conf_senha" id="conf_senha" /></td>
				</tr>
				<tr>
					<td colspan="2"><input type="submit" name="enviar" value=" " alt="Enviar" class="botao_enviar" /></td>
				</tr>
			</table>
		</form>
		<?php
	}
} else {
	$Usr = Usuario::Por_Unique($identificador, $campo);
	if($Usr === null)
		$Usr = new Usuario();
	if($tipo == 'A') {
		$Aluno = Aluno::Por_RA($identificador);
		if($Aluno === null)
			$Aluno = new Aluno();

		$tem_grad = ($Aluno->getNivel(false) != null);
		$tem_pos = ($Aluno->getNivel_Pos(false) != null);

		$niveis = Aluno::Listar_Niveis_Grad();
		$lista_niveis = "<option value=\"\"".(($Aluno->getNivel() == null)?" selected=\"selected\"":null).">Nenhum (-)</option>";
		foreach($niveis as $c => $d)
			$lista_niveis .= "<option value=\"".$c."\"".((($Aluno->getNivel() != '') && ($Aluno->getNivel() == $c))?" selected=\"selected\"":null).">".$d." (".$c.")</option>";

		$niveis_pos = Aluno::Listar_Niveis_Pos();
		$lista_niveis_pos = "<option value=\"\"".(($Aluno->getNivel_Pos() == null)?" selected=\"selected\"":null).">Nenhum (-)</option>";
		foreach($niveis_pos as $c => $d)
			$lista_niveis_pos .= "<option value=\"".$c."\"".((($Aluno->getNivel_Pos() != null) && ($Aluno->getNivel_Pos() == $c))?" selected=\"selected\"":null).">".$d." (".$c.")</option>";

		$Cursos = Curso::Listar(Curso::NIVEIS_GRAD);
		$lista_cursos = "<option value=\"\">-</option>";
		foreach($Cursos as $Curso)
			$lista_cursos .= "<option value=\"".$Curso->getNumero(true)."\"".(($Aluno->getCurso(true)->getID() == $Curso->getID())?" selected=\"selected\"":null).">".$Curso->getNome(true)." (".$Curso->getNumero(true).")</option>";

		$Cursos_pos = Curso::Listar(Curso::NIVEIS_POS);
		$lista_cursos_pos = "<option value=\"\">-</option>";
		foreach($Cursos_pos as $Curso_Pos)
			$lista_cursos_pos .= "<option value=\"".$Curso_Pos->getNumero(true)."\"".(($Aluno->getCurso_Pos(true)->getID() == $Curso_Pos->getID())?" selected=\"selected\"":null).">".$Curso_Pos->getNome(true)." (".$Curso_Pos->getNumero(true).")</option>";

		$lim_cat = Dado::Limites_Catalogo();

		$catalogos = "";
		for($i = $lim_cat['max']; $i >= $lim_cat['min']; $i--)
			$catalogos .= "<option value='".$i."'>".$i."</option>";

		$ingressos = "";
		for($i = date('Y'); $i >= 2000; $i--)
			$ingressos .= "<option value='".$i."'>".$i."</option>";

		if($Aluno->getNome(false) != null) {
			$espaco = strpos($Aluno->getNome(false), ' ');
			$nome = substr($Aluno->getNome(false), 0, $espaco);
			$sobrenome = substr($Aluno->getNome(false), $espaco+1);
		} else
			$nome = $sobrenome = null;
	} elseif($tipo == 'D') {
		$Professor = Professor::Por_Matricula($identificador);
		if($Professor === null)
			$Professor = new Professor();
		if($Professor->getNome(false) != null) {
			$espaco = strpos($Professor->getNome(false), ' ');
			$nome = substr($Professor->getNome(false), 0, $espaco);
			$sobrenome = substr($Professor->getNome(false), $espaco+1);
		} else
			$nome = $sobrenome = null;
	}

	?>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery.jhelpertip.js"></script>
	<script type="text/javascript">
		// <![CDATA[
		$(document).ready(function(){
			$.guaycuru.tooltip("TT_login", "Login:", "<ul><li>No m&iacute;nimo 3 e no m&aacute;ximo 16 caracteres.</li><li>Use apenas letras, n&uacute;meros e '_'.</li></ul>", {});
			$.guaycuru.tooltip("TT_senha", "Senha:", "<ul><li>A senha n&atilde;o precisa ser a mesma da DAC, e ser&aacute; usada para acessar o GDE pelo endere&ccedil;o http://gde.guaycuru.net</li></ul>", {});
			$.guaycuru.tooltip("TT_email", "Email:", "<ul><li>Use seu email principal, n&atilde;o o da DAC</li></ul>", {});
			<?php if($tipo == 'A') { ?>
			$.guaycuru.tooltip("TT_catalogo", "Cat&aacute;logo:", "<ul><li>O cat&aacute;logo no qual voc&ecirc; est&aacute; matriculado.</li><li>Geralmente &eacute; o mesmo do seu ano de ingresso.</li></ul>", {});
			$.guaycuru.tooltip("TT_ingresso", "Ingresso:", "<ul><li>O ano no qual voc&ecirc; ingressou na Unicamp.</li></ul>", {});
			$('#curso').change(atualiza_modalidades);
			atualiza_modalidades();
		});
		atualiza_modalidades = function() {
			$('#select_modalidade').addClass("ac_loading");
			$('#select_modalidade').load('<?= CONFIG_URL; ?>ajax/modalidades.php?c='+$('#curso').val()+'&s=<?= $Aluno->getModalidade(true); ?>&o=1', {}, function(){$('#select_modalidade').removeClass("ac_loading");});
		};
		<?php } else { ?>
		$("#tr_nome").hide();
		$("#tr_sobrenome").hide();
		$.guaycuru.tooltip("TT_nome_professor", "Nome Completo:", "<ul><li>Selecione seu nome da lista.</li></ul>", {});
		$("#professor").Autocompletar({
			json: '<?= CONFIG_URL; ?>ajax/professores.php',
			data: {token: $("#token").val()},
			delay: 100,
			idField: 'id',
			valField: 'nome',
			hiddenField: 'id_professor',
			highlight: true,
			instantaneo: true,
			obrigatorio: false,
			maxHeight: '360px',
			select: function(event, ui) {
				var nome = ui.item.value;
				$("#sobrenome").val(nome.substr(nome.indexOf(' ')+1));
				$("#tr_sobrenome").show();
				$("#nome").val(nome.substr(0, nome.indexOf(' ')));
				$("#tr_nome").show();
			},
			create: function(event, ui) { $("ul.ui-autocomplete").not("div.gde_jquery_ui > ul").wrap('<div class="gde_jquery_ui" />'); }
		});
		<?php } ?>
		// ]]>
	</script>
	Ol&aacute;, bem-vindo(a) ao GDE!<br />
	Como este &eacute; o seu primeiro acesso, &eacute; necess&aacute;rio que voc&ecirc; preencha alguns dados.<br /><br />
	<strong>Importante:</strong> Ao se cadastrar no GDE voc&ecirc; concorda com o fato de que todos os dados fornecidos ser&atilde;o armazenados no banco de dados do site.<br />No entanto voc&ecirc; n&atilde;o &eacute; obrigado(a) a fornecer nenhuma informa&ccedil;&atilde;o.<br /><br />
	<form method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/cadastro.php" data-destino="<?= CONFIG_URL.CONFIG_URL_LOGIN; ?>?token=#ID#">
		<input type="hidden" id="token" name="token" value="<?= htmlspecialchars($_GET['token']); ?>" />
		<table border="0">
			<tr>
				<td><strong><?= (($tipo == 'A') ? 'RA' : 'Matr&iacute;cula'); ?>:</strong></td>
				<td><input type="text" name="ra" id="ra" maxlength="6" value="<?= $identificador; ?>" disabled="disabled" /></td>
			</tr>
			<tr>
				<td><strong>Login:</strong></td>
				<td><input type="text" id="login" name="login" <?php if($Usr->getID() != null) echo 'readonly="readonly"'; ?> value="<?= $Usr->getLogin(true); ?>" /> <span class="formInfo"><a href="#" id="TT_login">?</a></span></td>
			</tr>
			<tr>
				<td><strong>Senha:</strong></td>
				<td><input type="password" name="senha" id="senha" /> <span class="formInfo"><a href="#" id="TT_senha">?</a></span></td>
			</tr>
			<tr>
				<td><strong>Re-digite a senha:</strong></td>
				<td><input type="password" name="conf_senha" id="conf_senha" /></td>
			</tr>
			<?php if($tipo == 'D') { ?>
				<tr>
					<td><strong>Nome Completo:</strong></td>
					<td><input type="hidden" id="id_professor" name="id_professor" value="<?= ($Professor->getID() != null) ? $Professor->getID() : '0'; ?>" />
						<input type="text" id="professor" name="professor" style="width: 300px;" value="<?php if($Professor->getID() != null) echo $Professor->getNome(true); ?>" <?php if($Professor->getID() != null) echo 'disabled="disabled"'; ?> /> <span class="formInfo"><a href="#" id="TT_nome_professor">?</a></span></td>
				</tr>
			<?php } ?>
			<tr id="tr_nome">
				<td><strong>Nome:</strong></td>
				<td><input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome); ?>" /></td>
			</tr>
			<tr id="tr_sobrenome">
				<td><strong>Sobrenome:</strong></td>
				<td><input type="text" id="sobrenome" name="sobrenome" value="<?= htmlspecialchars($sobrenome); ?>" /></td>
			</tr>
			<?php
			if($tipo == 'A') {
				if($Aluno->getID() == null) {
					?>
					<tr>
						<td><strong>Ingresso:</strong></td>
						<td><select name="ingresso"><?= $ingressos; ?></select> <span class="formInfo"><a href="#" id="TT_ingresso">?</a></span></td>
					</tr>
				<?php } if(($Aluno->getID() == null) || ($tem_grad)) { ?>
					<tr>
						<td><strong>N&iacute;vel da Gradua&ccedil;&atilde;o:</strong></td>
						<td><select id="nivel" name="nivel"><?= $lista_niveis; ?></select></td>
					</tr>
					<tr>
						<td><strong>Cat&aacute;logo da Gradua&ccedil;&atilde;o:</strong></td>
						<td><select name="catalogo"><?= $catalogos; ?></select> <span class="formInfo"><a href="#" id="TT_catalogo">?</a></span></td>
					</tr>
					<tr>
						<td><strong>Curso da Gradua&ccedil;&atilde;o:</strong></td>
						<td><select id="curso" name="curso"><?= $lista_cursos; ?></select></td>
					</tr>
					<tr>
						<td><strong>Modalidade da Gradua&ccedil;&atilde;:</strong></td>
						<td id="select_modalidade"><select id="modalidade" name="modalidade"><option value="">-</option></select></td>
					</tr>
				<?php } if(($Aluno->getID() == null) || ($tem_pos)) { ?>
					<tr>
						<td><strong>N&iacute;vel da P&oacute;s-Gradua&ccedil;&atilde;o:</strong></td>
						<td><select id="nivel_pos" name="nivel_pos"><?= $lista_niveis_pos; ?></select></td>
					</tr>
					<tr>
						<td><strong>Curso da P&oacute;s-Gradua&ccedil;&atilde;o:</strong></td>
						<td><select id="curso_pos" name="curso_pos"><?= $lista_cursos_pos; ?></select></td>
					</tr>
					<tr>
						<td><strong>Modalidade da P&oacute;s-Gradua&ccedil;&atilde;o:</strong></td>
						<td><input name="modalidade_pos" value="<?= $Aluno->getModalidade_Pos(true); ?>" /></td>
					</tr>
				<?php } ?>
			<?php } ?>
			<tr>
				<td><strong>Email Pessoal:</strong></td>
				<td><input type="text" name="email" /> <span class="formInfo"><a href="#" id="TT_email">?</a></span></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" name="enviar" value=" " alt="Enviar" class="botao_enviar" /></td>
			</tr>
		</table>
	</form>
	<?php
}
echo $FIM;
?>

