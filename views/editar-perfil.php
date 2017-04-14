<?php

namespace GDE;

define('TITULO', 'Meus Dados');

require_once('../common/common.inc.php');

$Cursos = Curso::Listar(array('G', 'T'), "curso ASC");
$lista_cursos = "<option value=\"0\">-</option>";
foreach($Cursos as $Curso)
	$lista_cursos .= "<option value=\"".$Curso->getNumero(true)."\"".(($_Usuario->getCurso()->getID() == $Curso->getID())?" selected=\"selected\"":null).">".$Curso->getNome(true)." (".$Curso->getNumero(true).")</option>";

$lim_cat = Dado::Limites_Catalogo();

$catalogos = "";
for($i = $lim_cat['max']; $i >= $lim_cat['min']; $i--)
	$catalogos .= "<option value=\"".$i."\"".(($i == $_Usuario->getCatalogo(true))?" selected=\"selected\"":null).">".$i."</option>";

$ingressos = "";
for($i = $lim_cat['max']; $i >= 2000; $i--)
	$ingressos .= "<option value=\"".$i."\"".(($i == $_Usuario->getIngresso(true))?" selected=\"selected\"":null).">".$i."</option>";

$estados_civis = "";
foreach(Usuario::Listar_Estados_Civis() as $n => $e)
	$estados_civis .= "<option value=\"".$n."\"".(($_Usuario->getEstado_Civil(false) == $n)?" selected=\"selected\"":null).">".$e."</option>";

?>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function(){
		<?php if ((!isset($_SESSION['validaEmail'])) || ($_SESSION['validaEmail'] === false)) { ?>
		$("#validar_email").click(function(){
			if($("#validar_email").hasClass("clickable")) {
				$.post('<?= CONFIG_URL; ?>ajax/validar.php', {}, function(data){
					$("#texto_validar").text("Aguardando confirma\u00E7\u00E3o.");
				});
			}
			$("#validar_email").removeClass("clickable");
			return false;
		});
		<?php } ?>
		$('#curso').change(atualiza_modalidades);
		$('#catalogo').change(atualiza_modalidades);
		atualiza_modalidades();
		$('#data_nascimento').mask("99/99/9999",{placeholder:"_"});
		$.guaycuru.tooltip("TT_senha", "Senha:", "<ul><li>A senha n&atilde;o precisa ser a mesma da DAC, e ser&aacute; usada para acessar o GDE pelo endere&ccedil;o http://gde.guaycuru.net</li><li>Deixe em branco para manter a senha atual.</li></ul>", {});
		$.guaycuru.tooltip("TT_email", "Email:", "Utilize seu email principal, pois o email da DAC nem sempre funciona direito!", {});
		$("input.emprego_nome").each(function() { $(this).Valor_Padrao('Nome', 'padrao'); } );
		$("input.emprego_cargo").each(function() { $(this).Valor_Padrao('Cargo', 'padrao'); } );
		$("input.emprego_data_inicio").each(function() { $(this).Valor_Padrao('Data Inicio', 'padrao'); } );
		$("input.emprego_data_fim").each(function() { $(this).Valor_Padrao('Data Fim', 'padrao'); } );
		$("input.emprego_site").each(function() { $(this).Valor_Padrao('Site', 'padrao'); } );
		$("#adicionar_emprego").click(function() {
			var tr = $(this).parent().parent();
			var indice = $('input[id^="emprego_inicio_"]').length;
			$('<tr><td width="20%"><select name="emprego_tipo_'+indice+'" id="emprego_tipo_'+indice+'" onchange="checaIC('+indice+')" ><option value="S">Est&aacute;gio</option><option value="E">Emprego</option><option value="I">Inicia&ccedil;&atilde;o Cient&iacute;fica</option></select></td><td><input type="text" id="emprego_'+indice+'" name="emprego_'+indice+'" value="Nome" /><input type="checkbox" id="emprego_remover_'+indice+'" onclick="remover_emprego('+indice+')"; /><label for="emprego_remover_'+indice+'">Remover este emprego?</label></td></tr><tr><td><strong>Cargo:</strong></td><td><input type="text" name="emprego_cargo_'+indice+'" id="emprego_cargo_'+indice+'" value="Cargo" /></td></tr><tr><td><strong>Per&iacute;odo:</strong></td><td><input type="text" id="emprego_inicio_'+indice+'" name="emprego_inicio_'+indice+'" value="Data Inicio" /> - <input type="text" id="emprego_fim_'+indice+'" name="emprego_fim_'+indice+'" value="Data Fim" /><input type="checkbox" name="emprego_atual_'+indice+'" id="emprego_atual_t_'+indice+'" value="t" onclick="emprego_atual(this)" /><label for="emprego_atual_t_'+indice+'">&Eacute; seu emprego atual?</label></td></tr><tr><td><strong>Site:</strong></td><td><input type="text" id="emprego_site_'+indice+'" name="emprego_site_'+indice+'" value="Site" class="emprego_site" /></td></tr>)').insertBefore(tr);
			var nEmp = parseInt($('#num_empregos').val());
			nEmp = nEmp + 1;
			$('#num_empregos').val(nEmp);
			$('#emprego_'+indice).Valor_Padrao('Nome', 'padrao');
			$('#emprego_cargo_'+indice).Valor_Padrao('Cargo', 'padrao');
			$('#emprego_inicio_'+indice).Valor_Padrao('Data Inicio', 'padrao');
			$('#emprego_fim_'+indice).Valor_Padrao('Data Fim', 'padrao');
			$('#emprego_inicio_'+indice).datepicker({
				beforeShow: function(input, inst) {
					var newclass = 'gde_jquery_ui';
					if (!inst.dpDiv.parent().hasClass('gde_jquery_ui-base')){
						inst.dpDiv.wrap('<div class="'+newclass+'"></div>')
					}
				}
			});
			$('#emprego_fim_'+indice).datepicker({
				beforeShow: function(input, inst) {
					var newclass = 'gde_jquery_ui';
					if (!inst.dpDiv.parent().hasClass('gde_jquery_ui-base')){
						inst.dpDiv.wrap('<div class="'+newclass+'"></div>')
					}
				}
			});
			$('#emprego_site_'+indice).Valor_Padrao('Site', 'padrao');
			return false;
		});
		$("#tabs").tabs();
		Tamanho_Abas('tabs');
		$(window).resize(function() { Tamanho_Abas('tabs'); });
	});
	atualiza_modalidades = function() {
		$('#select_modalidade').addClass("ac_loading");
		$('#select_modalidade').load('<?= CONFIG_URL; ?>ajax/modalidades.php?c='+$('#curso').val()+'&a='+$('#catalogo').val()+'&s=<?= $_Usuario->getModalidade(true)->getSigla(true); ?>&o=1', {}, function(){$('#select_modalidade').removeClass("ac_loading");});
	};
	checaIC = function(indice){
		if($('#emprego_tipo_'+indice).val() == "I"){
			$('#emprego_cargo_'+indice).attr('disabled',true);
		}else{
			if($('#emprego_cargo_'+indice).length == 0)
				$('<tr><td><strong>Cargo:</strong></td><td><input type="text" name="emprego_cargo_'+indice+'" id="emprego_cargo_'+indice+'" value="Cargo" /></td></tr>').insertAfter($('#emprego_'+indice).parent().parent());
			$('#emprego_cargo_'+indice).attr('disabled',false);
		}
	};
	emprego_atual = function(obj){
		var emprego_data_fim = $(obj).parent().find('input[id^="emprego_fim_"]');
		if($(obj).attr('checked') == true){
			$(emprego_data_fim).hide();
		}else{
			$(emprego_data_fim).show();
		}
	};
	remover_emprego = function(indice){
		if($('#emprego_remover_'+indice).attr('checked') == true){
			$('#emprego_'+indice).attr('disabled',true);
			$('#emprego_inicio_'+indice).attr('disabled',true);
			$('#emprego_fim_'+indice).attr('disabled',true);
			$('#emprego_cargo_'+indice).attr('disabled',true);
			$('#emprego_site_'+indice).attr('disabled',true);
			$('#emprego_tipo_'+indice).attr('disabled',true);
			$('#emprego_atual_t_'+indice).attr('disabled',true);
		}else{
			$('#emprego_'+indice).attr('disabled',false);
			$('#emprego_inicio_'+indice).attr('disabled',false);
			$('#emprego_fim_'+indice).attr('disabled',false);
			$('#emprego_cargo_'+indice).attr('disabled',false);
			$('#emprego_site_'+indice).attr('disabled',false);
			$('#emprego_tipo_'+indice).attr('disabled',false);
			$('#emprego_atual_t_'+indice).attr('disabled',false);
		}
	};
	$(function() {
		$('input[id^="emprego_fim_"]').datepicker({
			beforeShow: function(input, inst) {
				var newclass = 'gde_jquery_ui';
				if (!inst.dpDiv.parent().hasClass('gde_jquery_ui-base')){
					inst.dpDiv.wrap('<div class="'+newclass+'"></div>')
				}
			}
		});
		$('input[id^="emprego_inicio_"]').datepicker({
			beforeShow: function(input, inst) {
				var newclass = 'gde_jquery_ui';
				if (!inst.dpDiv.parent().hasClass('gde_jquery_ui')){
					inst.dpDiv.wrap('<div class="'+newclass+'"></div>')
				}
			}
		});
	});
	// ]]>
</script>
<h2>Cadastro</h2>
<strong>Importante:</strong> Ao se cadastrar no GDE voc&ecirc; concorda com o fato de que todos os dados fornecidos ser&atilde;o armazenados no banco de dados do site.<br />No entanto voc&ecirc; n&atilde;o &eacute; obrigado(a) a fornecer nenhuma informa&ccedil;&atilde;o.<br /><br />
<form method="post" class="auto-form" action="<?= CONFIG_URL; ?>ajax/editar.php" enctype="multipart/form-data" data-destino="<?= CONFIG_URL; ?>editar-perfil/">
	<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
	<input type="hidden" id="acao" name="acao" value="salvar" />
	<div id="tabs">
		<ul>
			<li><a href="#tab_pessoal">Pessoal</a></li>
			<li><a href="#tab_social">Social</a></li>
			<li><a href="#tab_academico" class="ativo">Acad&ecirc;mico</a></li>
			<li><a href="#tab_profissional">Profissional</a></li>
		</ul>
		<div id="tab_pessoal" class="tab_content">
			<table cellspacing="0" class="tabela_bonyta_branca">
				<tr>
					<td width="20%"><strong>Nome:</strong></td>
					<td><input type="text" name="nome" value="<?=$_Usuario->getNome(true);?>" size="40" /></td>
				</tr>
				<tr>
					<td><strong>Sobrenome:</strong></td>
					<td><input type="text" name="sobrenome" value="<?=$_Usuario->getSobrenome(true);?>" size="40" /></td>
				</tr>
				<tr>
					<td><strong>Email:</strong></td>
					<td>
						<input type="text" name="email" value="<?=$_Usuario->getEmail();?>" size="40" /> <span class="formInfo"><a href="#" id="TT_email">?</a></span>
						<?php if ($_Usuario->getEmail_Validado() == false) { ?>
							<a href="#" id="validar_email" class="clickable" ><label id="texto_validar"><?= ((isset($_SESSION['validaEmail'])) && ($_SESSION['validaEmail'] === true)) ? 'Aguardando confirma&ccedil;&atilde;o' : 'Valide seu email' ?></label></a>
						<?php } else { ?>
							<label>Seu email j&aacute; foi validado</label>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td><strong>RA:</strong></td>
					<td><input type="text" name="ra" value="<?= $_Usuario->getAluno(true)->getRA(true); ?>" readonly="readonly" /></td>
				</tr>
				<tr>
					<td><strong>Senha:</strong><br /><i>Deixe em branco<br />para manter a atual</i></td>
					<td><input type="password" name="senha" id="senha" size="40" /> <span class="formInfo"><a href="#" id="TT_senha">?</a></span></td>
				</tr>
				<tr>
					<td><strong>Re-digite a senha:</strong></td>
					<td><input type="password" name="conf_senha" id="conf_senha" size="40" /></td>
				</tr>
				<tr>
					<td><strong>Sexo:</strong></td>
					<td>
						<input type="radio" name="sexo" value="m" id="sexo_m"<?= (($_Usuario->getSexo() != 'f') && ($_Usuario->getSexo() != 'o'))?' checked="checked"':null; ?> /><label for="sexo_m">Masculino</label>
						<input type="radio" name="sexo" value="f" id="sexo_f"<?= (($_Usuario->getSexo() == 'f'))?' checked="checked"':null; ?> /><label for="sexo_f">Feminino</label>
						<input type="radio" name="sexo" value="o" id="sexo_o"<?= (($_Usuario->getSexo() == 'o'))?' checked="checked"':null; ?> /><label for="sexo_o">Outro</label>
					</td>
				</tr>
				<tr>
					<td><strong>Foto:</strong> <i>(JPG, GIF ou PNG)<br />M&aacute;ximo de 5MB</i></td>
					<td><img src="<?= $_Usuario->getFoto(true); ?>" alt="Foto" /><br /><input type="checkbox" name="excluir_foto" id="excluir_foto_t" value="t" /><label for="excluir_foto_t">Excluir Foto</label><br /><input type="file" name="foto" /></td>
				</tr>
				<tr>
					<td><strong>Data de Nascimento:</strong></td>
					<td><input type="text" id="data_nascimento" name="data_nascimento" value="<?=$_Usuario->getData_Nascimento('d/m/Y');?>" /></td>
				</tr>

				<tr>
					<td><strong>Relacionamento:</strong></td>
					<td><select name="estado_civil"><?= $estados_civis; ?></select></td>
				</tr>
				<tr>
					<td><strong>Cidade:</strong></td>
					<td><input type="text" name="cidade" value="<?=$_Usuario->getCidade(true);?>" /></td>
				</tr>
				<tr>
					<td><strong>Estado:</strong></td>
					<td><input type="text" name="estado" value="<?=$_Usuario->getEstado(true);?>" maxlength="2" /></td>
				</tr>
			</table>
		</div>
		<div id="tab_social" class="tab_content">
			<table cellspacing="0" class="tabela_bonyta_branca">
				<tr>
					<td width="20%"><strong>Apelido:</strong></td>
					<td><input type="text" name="apelido" value="<?=$_Usuario->getApelido(true);?>" size="40" /></td>
				</tr>
				<tr>
					<td><strong>Site / Blog:</strong></td>
					<td><input type="text" name="blog" value="<?=($_Usuario->getBlog() == null)?'http://':$_Usuario->getBlog(true);?>" size="40" /></td>
				</tr>
				<tr>
					<td><strong>Facebook:</strong></td>
					<td><input type="text" name="facebook" value="<?=($_Usuario->getFacebook() == null)?'http://':$_Usuario->getFacebook(true);?>" size="40" /></td>
				</tr>
				<tr>
					<td><strong>Twitter:</strong></td>
					<td><input type="text" name="twitter_foo" size="14" value="http://twitter.com/" readonly="readonly" /><input type="text" name="twitter" value="<?=$_Usuario->getTwitter(true);?>" size="40" /></td>
				</tr>
				<tr>
					<td><strong>Outras Informa&ccedil;&otilde;es:</strong></td>
					<td><textarea name="mais" rows="3" cols="30"><?=$_Usuario->getMais(true);?></textarea></td>
				</tr>
			</table>
		</div>
		<div id="tab_academico" class="tab_content">
			<table cellspacing="0" class="tabela_bonyta_branca">
				<tr>
					<td width="20%"><strong>Cat&aacute;logo:</strong></td>
					<td><select id="catalogo" name="catalogo"><?=$catalogos;?></select></td>
				</tr>
				<tr>
					<td><strong>Curso:</strong></td>
					<td><select id="curso" name="curso"><?=$lista_cursos; ?></select></td>
				</tr>
				<tr>
					<td><strong>Modalidade:</strong></td>
					<td id="select_modalidade"><select id="modalidade" name="modalidade"><option value="">-</option></select></td>
				</tr>
				<tr>
					<td><strong>Ingresso:</strong></td>
					<td><select name="ingresso"><?=$ingressos;?></select></td>
				</tr>
				<tr>
					<td><strong>Compartilhar &Aacute;rvore:</strong></td>
					<td><input type="radio" name="compartilha_arvore" value="t"<?=(($_Usuario->getCompartilha_Arvore() == 't')?' checked="checked"':null);?> id="comp_t" /> <label for="comp_t">Com Todos</label> <input type="radio" name="compartilha_arvore" value="a"<?=(($_Usuario->getCompartilha_Arvore() == 'a')?' checked="checked"':null);?> id="comp_a" /> <label for="comp_a">Com Amigos</label> <input type="radio" name="compartilha_arvore" value="f"<?=(($_Usuario->getCompartilha_Arvore() == 'f')?' checked="checked"':null);?> id="comp_f" /> <label for="comp_f">Com Ningu&eacute;m</label></td>
				</tr>
				<tr>
					<td><strong>Compartilhar Hor&aacute;rio:</strong></td>
					<td><input type="radio" name="compartilha_horario" value="t"<?=(($_Usuario->getCompartilha('horario') == 't')?' checked="checked"':null);?> id="comph_t" /> <label for="comph_t">Com Todos</label> <input type="radio" name="compartilha_horario" value="a"<?=(($_Usuario->getCompartilha('horario') == 'a')?' checked="checked"':null).((!$_Usuario->Pode_Mudar_Compartilha_Horario('a'))?' disabled="disabled"':null);?> id="comph_a" /> <label for="comph_a">Com Amigos</label> <input type="radio" name="compartilha_horario" value="f"<?=(($_Usuario->getCompartilha('horario') == 'f')?' checked="checked"':null).((!$_Usuario->Pode_Mudar_Compartilha_Horario('f'))?' disabled="disabled"':null);?> id="comph_f" /> <label for="comph_f">Com Ningu&eacute;m</label></td>
				</tr>
				<tr>
					<td colspan="2"><strong>Sobre o compartilhamento:</strong><br />Se escolher compartilhar "Com Ningu&eacute;m", voc&ecirc; n&atilde;o poder&aacute; ver o Hor&aacute;rio de ningu&eacute;m.<br />Se escolher compartilhar "Com Amigos", voc&ecirc; s&oacute; poder&aacute; ver o Hor&aacute;rio de seus amigos.<br /><strong>Aten&ccedil;&atilde;o:</strong> Voc&ecirc; s&oacute; poder&aacute; mudar para uma op&ccedil;&atilde;o mais restritiva uma vez a cada 6 meses!<br />No entanto voc&ecirc; pode mudar para uma op&ccedil;&atilde;o mais aberta a qualquer momento.<br /><strong>&Uacute;ltima Mudan&ccedil;a:</strong> <?= $_Usuario->getMudanca_Horario('d/m/Y', true); ?></td>
				</tr>
			</table>
		</div>
		<div id="tab_profissional" class="tab_content">
			<table cellspacing="0" class="tabela_bonyta_branca">
				<?php //ToDo: Reativar isto?
				/*$curDate = getdate();
				$today = $curDate['mday'].'/'.$curDate['mon'].'/'.$curDate['year'];
				$empregos = $_Usuario->getEmpregos();
				$numEmpregos = 1;
				foreach ($empregos as $indice => $emprego) {
					?>
					<tr>
						<td width="20%"><select name="emprego_tipo_<?=$indice;?>" id="emprego_tipo_<?=$indice;?>" <?='onchange="checaIC('.$indice.')"';?> ><option value="S"<?=($emprego->getTipo() == 'S') ? " selected=\"selected\"" : "";?>>Est&aacute;gio</option><option value="E"<?=($emprego->getTipo() == 'E') ? " selected=\"selected\"" : "";?>>Emprego</option><option value="I"<?=($emprego->getTipo() == 'I') ? " selected=\"selected\"" : "";?>>Inicia&ccedil;&atilde;o Cient&iacute;fica</option></select></td>
						<td><input type="text" id="emprego_<?=$indice;?>" name="emprego_<?=$indice;?>" value="<?=$emprego->getNome()?>" class="emprego_nome" /><input type="checkbox" name="emprego_remover_<?=$indice;?>" id="emprego_remover_<?=$indice;?>" onclick="remover_emprego('<?= $indice ?>')" value="t" /><label for="emprego_remover_<?=$indice;?>">Remover este emprego?</label></td>
					</tr>
					<?php
					if($emprego->getCargo() != null){
						?>
						<tr>
							<td><strong>Cargo</strong></td>
							<td><input type="text" id="emprego_cargo_<?=$indice;?>" name="emprego_cargo_<?=$indice;?>" value="<?=$emprego->getCargo();?>" class="emprego_cargo" /></td>
						</tr>
					<?php } ?>
					<tr>
						<td><strong>Per&iacute;odo:</strong></td>
						<td><input type="text" id="emprego_inicio_<?=$indice;?>" name="emprego_inicio_<?=$indice;?>" value="<?=$emprego->getInicio('d/m/Y');?>" class="emprego_inicio" /> - <input type="text" id="emprego_fim_<?=$indice;?>" name="emprego_fim_<?=$indice;?>" value="<?=($emprego->getAtual()=='t') ? ($emprego->getInicio('d/m/Y').'" style="display:none') : $emprego->getFim('d/m/Y')?>" class="emprego_fim" /><input type="checkbox" name="emprego_atual_<?=$indice;?>" id="emprego_atual_t_<?=$indice;?>" value="t" onclick="emprego_atual(this)"<?=($emprego->getAtual() == 't')?' checked="checked"':'';?> /><label for="emprego_atual_t_<?=$indice;?>">&Eacute; seu emprego atual?</label></td>
					</tr>
					<tr>
						<td><strong>Site:</strong></td>
						<td><input type="text" id="emprego_site_<?=$indice;?>" name="emprego_site_<?=$indice;?>" value="<?= $emprego->getSite(); ?>" class="emprego_site" /></td>
					</tr>
					<?php
				}
				if(isset($indice))
					$numEmpregos += $indice + 1;
				?>
				<tr>
					<td width="20%"><select name="emprego_tipo_<?=($numEmpregos-1);?>" id="emprego_tipo_<?=($numEmpregos-1);?>" <?='onchange="checaIC('.($numEmpregos-1).')"';?> ><option value="S">Est&aacute;gio</option><option value="E">Emprego</option><option value="I">Inicia&ccedil;&atilde;o Cient&iacute;fica</option></select></td>
					<td><input type="text" id="emprego_<?=($numEmpregos-1);?>" name="emprego_<?=($numEmpregos-1);?>" class="emprego_nome" /><input type="checkbox" id="emprego_remover_<?=($numEmpregos-1);?>"<?=' onclick="remover_emprego('.($numEmpregos-1).');"'?> value="t" /><label for="emprego_remover_<?=($numEmpregos-1);?>">Remover este emprego?</label></td>
				</tr>
				<tr>
					<td><strong>Cargo:</strong></td>
					<td><input type="text" id="emprego_cargo_<?=($numEmpregos-1);?>" name="emprego_cargo_<?=($numEmpregos-1);?>" class="emprego_cargo" /></td>
				</tr>
				<tr>
					<td><strong>Per&iacute;odo:</strong></td>
					<td><input type="text" id="emprego_inicio_<?=($numEmpregos-1);?>" name="emprego_inicio_<?=($numEmpregos-1);?>" class="emprego_data_inicio" /> - <input type="text" id="emprego_fim_<?=($numEmpregos-1);?>" name="emprego_fim_<?=($numEmpregos-1);?>" class="emprego_data_fim" /><input type="checkbox" name="emprego_atual_<?=($numEmpregos-1);?>" id="emprego_atual_t_<?=($numEmpregos-1);?>" value="t" onclick="emprego_atual(this)" /><label for="emprego_atual_t_<?=($numEmpregos-1);?>">&Eacute; seu emprego atual?</label></td>
				</tr>
				<tr>
					<td><strong>Site:</strong></td>
					<td><input type="text" id="emprego_site_<?=($numEmpregos-1);?>" name="emprego_site_<?=($numEmpregos-1);?>" class="emprego_site" /></td>
				</tr>
				<tr>
					<td><a href="#" id="adicionar_emprego">Adicionar outro Emprego</a></td>
				</tr> <input type="hidden" id="num_empregos" name="num_empregos" value="<?= $numEmpregos; ?>" />*/
				?>
				<tr>
					<td><strong>Procurando Emprego?</strong></td>
					<td>
						<select name="procurando_emprego" id="procurando_emprego" >
							<option value="t"<?=($_Usuario->getProcurando_Emprego() == 't') ? " selected=\"selected\"" : "";?>>Sim</option>
							<option value="f"<?=($_Usuario->getProcurando_Emprego() == 'f') ? " selected=\"selected\"" : "";?>>N&atilde;o</option>
							<option value="-"<?=($_Usuario->getProcurando_Emprego() == null) ? " selected=\"selected\"" : "";?>>Ocultar</option>
						</select>
					</td>
				</tr>
				<tr>
					<td><strong>Experi&ecirc;ncias profissionais:</strong></td>
					<td><textarea name="exp_profissionais" rows="3" cols="30"><?=$_Usuario->getExp_Profissionais(true);?></textarea></td>
				</tr>
				<tr>
					<td><strong>Habilidades Pessoais:</strong></td>
					<td><textarea name="hab_pessoais" rows="3" cols="30"><?=$_Usuario->getHab_Pessoais(true);?></textarea></td>
				</tr>
				<tr>
					<td><strong>Especialidades T&eacute;cnicas:</strong></td>
					<td><textarea name="esp_tecnicas" rows="3" cols="30"><?=$_Usuario->getEsp_Tecnicas(true);?></textarea></td>
				</tr>
				<tr>
					<td><strong>Outras informa&ccedil;&otilde;es relevantes:</strong></td>
					<td><textarea name="info_profissional" rows="3" cols="30"><?=$_Usuario->getInfo_Profissional(true);?></textarea></td>
				</tr>

			</table>
		</div>
		<input type="submit" id="salvar" name="salvar" class="botao_salvar" value=" " alt="Salvar" />
	</div>
</form>
<?= $FIM; ?>
