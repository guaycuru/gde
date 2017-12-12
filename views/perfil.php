<?php

namespace GDE;

// ToDo: Titulo mais informativo
define('TITULO', 'Perfil');
require_once('../common/common.inc.php');

// ToDo: Remover backward compatibility
if(!empty($_GET['ra']))
	$_GET['aluno'] = $_GET['ra'];
elseif(!empty($_GET['p']))
	$_GET['professor'] = $_GET['p'];
elseif(!empty($_GET['l']))
	$_GET['usuario'] = $_GET['l'];

$Aluno = $Professor = null;
if(!empty($_GET['aluno'])) {
	$_tipo = 'A';
	$Usr = Usuario::Por_RA($_GET['aluno']);
	$Aluno = ($Usr !== null) ? $Usr->getAluno(true) : Aluno::Load($_GET['aluno']);
	if($Aluno->getID() == null)
		die('Aluno n&atilde;o encontrado...'.$FIM);
	$_matricula = $Aluno->getRA();
} elseif(!empty($_GET['professor'])) {
	$_tipo = 'P';
	$Professor = Professor::Load($_GET['professor']);
	if($Professor->getID() == null)
		die('Professor n&atilde;o encontrado...'.$FIM);
	$Usr = $Professor->getUsuario(false);
	$_matricula = $Professor->getID();
} else {
	$Usr = (!empty($_GET['usuario'])) ? Usuario::Por_Login($_GET['usuario']) : $_Usuario;
	if($Usr === null)
		die('Usu&aacute;rio n&atilde;o encontrado...'.$FIM);
	if($Usr->getAluno(false) !== null) {
		$_tipo = 'A';
		$Aluno = $Usr->getAluno();
		$_matricula = $Aluno->getRA();
	} elseif($Usr->getProfessor(false) !== null) {
		$_tipo = 'P';
		$Professor = $Usr->getProfessor();
		$_matricula = $Professor->getID();
	} else {
		$_tipo = 'E';
		$_matricula = null;
	}
}
if(($Usr !== null) && ($Usr->getAtivo() === false)) // Usuario desativou a conta
	$Usr = null;

if($Usr !== null) {
	$Amigos = $Usr->Amigos(true);
	$total_amigos = 0;
	$Em_Comum = $_Usuario->Amigos_Em_Comum($Usr, true, $total_amigos);
	$_sou_eu = ($Usr->getID() == $_Usuario->getID());
	$Meu_Amigo = $_Usuario->Amigo($Usr);
} else {
	$_sou_eu = false;
	$Meu_Amigo = false;
}

if($_tipo == 'A') {
	$planejador_periodo_proximo = Dado::Pega_Dados('planejador_periodo_proximo');
	$Planejado_Proximo = ((($planejador_periodo_proximo != null)) && (($Meu_Amigo !== false) || ($_Usuario->getAdmin() === true))) ? Periodo::Load($planejador_periodo_proximo) : false;
} elseif($_tipo == 'P') {
	$Planejado_Proximo = false;

	$Oferecimentos = $Professor->getOferecimentos(null, false);

	$siglas = array();
	foreach($Oferecimentos as $Oferecimento) {
		if(!in_array($Oferecimento->getSigla(), $siglas)) {
			$siglas[] = $Oferecimento->getSigla(true);
			$nomes[$Oferecimento->getSigla(true)] = $Oferecimento->getDisciplina(true)->getNome(true);
		}
	}
	natsort($siglas);

	$ofs = "";
	foreach($siglas as $sigla)
		$ofs .= "<option value=\"".$sigla."\">".$sigla." - ".$nomes[$sigla]."</option>";

	$Professor_Instituto = $Professor->getInstituto(false);
	$professor_sala = $Professor->getSala();
	$professor_email = $Professor->getEmail();
	$professor_pagina = $Professor->getPagina();
	$professor_lattes = $Professor->getLattes();

	$html_professor_instituto = "";
	$html_professor_sala = "";
	$html_professor_email = "";
	$html_professor_pagina = "";
	$html_professor_lattes = "";

	if($Professor->getInstituto(false) === null) {
		if(ColaboracaoProfessor::Existe_Colaboracao($_matricula, 'instituto') == false) {  // Ninguem colaborou
			$Institutos = Instituto::FindBy();
			$html_professor_instituto = '<select id="instituto_valor">';
			$html_professor_instituto .= '<option value="0">Colabore aqui</option>';
			foreach($Institutos as $instituto) {
				$html_professor_instituto .= '<option value="'.$instituto->getID().'">'.$instituto->getNome().'</option>';
			}
			$html_professor_instituto .= '</select>';
			$html_professor_instituto .= '<a href="#" id="instituto_colaborar" class="link_colaborar">Colaborar</a>';
		} else {  // Ja existe colaboracao pendente
			$html_professor_instituto = 'Colabora&ccedil;&atilde;o pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.';
		}
	} else  // Ja existe colaboracao autorizada
		$html_professor_instituto = $Professor_Instituto->getNome(); //.' <a href="#" id="instituto_reclamar" class="link_reclamar">Reclamar</a>';

	if($professor_sala == null) {
		if(ColaboracaoProfessor::Existe_Colaboracao($_matricula, 'sala') == false) {
			$html_professor_sala = '<input id="sala_valor" type="text" class="valor_colaborar"><a href="#" id="sala_colaborar" class="link_colaborar">Colaborar</a>';
		} else {
			$html_professor_sala = 'Colaboracao pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.';
		}
	} else
		$html_professor_sala = $professor_sala; //.' <a href="#" id="sala_reclamar" class="link_reclamar">Reclamar</a>';

	if($professor_email == null) {
		if(ColaboracaoProfessor::Existe_Colaboracao($_matricula, 'email') == false) {
			$html_professor_email = '<input id="email_valor" type="text" class="valor_colaborar"><a href="#" id="email_colaborar" class="link_colaborar">Colaborar</a>';
		} else {
			$html_professor_email = 'Colaboracao pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.';
		}
	} else
		$html_professor_email = $professor_email; //.' <a href="#" id="email_reclamar" class="link_reclamar">Reclamar</a>';

	if($professor_pagina == null) {
		if(ColaboracaoProfessor::Existe_Colaboracao($_matricula, 'pagina') == false) {
			$html_professor_pagina = '<input id="pagina_valor" type="text" class="valor_colaborar"><a href="#" id="pagina_colaborar" class="link_colaborar">Colaborar</a>';
		} else {
			$html_professor_pagina = 'Colaboracao pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.';
		}
	} else
		$html_professor_pagina = '<a href="'.$professor_pagina.'" target="_blank">'.$professor_pagina.'</a>'; //.' <a href="#" id="pagina_reclamar" class="link_reclamar">Reclamar</a>';

	if($professor_lattes == null) {
		if(ColaboracaoProfessor::Existe_Colaboracao($_matricula, 'lattes') == false) {
			$html_professor_lattes = '<input id="lattes_valor" type="text" class="valor_colaborar"><a href="#" id="lattes_colaborar" class="link_colaborar">Colaborar</a>';
		} else {
			$html_professor_lattes = 'Colaboracao pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.';
		}
	} else
		$html_professor_lattes = '<a href="'.$professor_lattes.'" target="_blank">'.$professor_lattes.'</a>'; //.' <a href="#" id="pagina_reclamar" class="link_reclamar">Reclamar</a>';
} else
	$Planejado_Proximo = false;

?>
<?php if ($Usr !== null) { ?>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.amizades.js?<?= REVISION; ?>"></script>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.atualizacoes.js?<?= REVISION; ?>"></script>
<?php } if ($_tipo == 'P') { ?>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.avaliacao.js?<?= REVISION; ?>"></script>
<?php } if ($_tipo == 'A') { ?>
	<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/gde.favoritos.js?<?= REVISION; ?>"></script>
<?php } ?>
<script type="text/javascript">
	// <![CDATA[
	var carregou_horario = false;
	var carregou_avaliacao = false;
	var atualizacao_id = '<?= ($Usr !== null) ? $Usr->getID() : null; ?>';
	var atualizacao_tp = 'u';
	var mensagem_padrao = 'Enviar uma mensagem...';
	var colaborar_padrao = 'Colabore aqui';
	var procurando_amigo = [];
	<?= ($_tipo == 'P') ? "var id_professor = '".$Professor->getID()."';" : "" ?>

	<?php if($Planejado_Proximo !== false) { ?>
	var carrega_planejado = function(id) {
		$(".TabView .Tabs a").removeClass("ativo");
		$("#opcao_"+id).addClass("ativo");
		esconde = $.guaycuru.aguarde();
		$("#planejado_compartilhado").load("<?= CONFIG_URL; ?>ajax/planejado_compartilhado.php", { id: id }, function() { esconde(); } );
		return false;
	};
	<?php } ?>
	var Atualizar_Horario = function(matricula, tipo, periodo, nivel) {
		if(!periodo)
			periodo = '';
		if(!nivel)
			nivel = '';
		$("#tab_horario").Carregando('Carregando Matr&iacute;culas e Hor&aacute;rio...');
		var params = (tipo == 'A') ? {ra: matricula, p: periodo, n: nivel} : {professor: matricula, p: periodo, n: nivel};
		$.post('<?= CONFIG_URL; ?>ajax/horario.php', params, function(data) {
			if(data)
				$("#tab_horario").html(data);
		});
	};
	<?php if($Usr !== null) { ?>
	var Enviar_Mensagem = function() {
		var mensagem = $("#mensagem").val();
		if((mensagem == mensagem_padrao) || (mensagem == ''))
			return false;
		$("#enviar_mensagem").hide();
		$("#mensagem").addClass('mensagem_enviando');
		$.post('<?= CONFIG_URL; ?>ajax/acontecimento.php', {tp: 'um', i: '<?= $Usr->getID(); ?>', txt: mensagem}, function(data) {
			if(data && data.ok) {
				$("#mensagem").Padrao();
				Adicionar_Atualizacao('<?= $Usr->getID(); ?>', data.id);
			}
			$("#mensagem").removeClass('mensagem_enviando');
			$("#enviar_mensagem").show();
		});
		return false;
	};
	<?php } ?>
	$(document).ready(function() {
		<?php if(($Usr !== null) && ($Usr->Quase_Amigo($_Usuario)) !== false) { ?>
		$("#amizade_aceitar_<?= $Usr->getID() ?>").click(Amizade_Aceitar);
		$("#amizade_ignorar_<?= $Usr->getID() ?>").click(Amizade_Ignorar);
		<?php } ?>
		$("#menuAccordion").accordion({
			autoHeight: false,
			navigation: true,
			collapsible: true
		});
		$("#buscar_amigos1").bind({
			click: function() {
				$("#menuAccordion").accordion('activate', $("#accordion_amigos"));
			},
			focus: function() {
				$('#menuAccordion').accordion('option', 'collapsible', false);
			},
			blur: function() {
				$('#menuAccordion').accordion('option', 'collapsible', true);
			},
			keydown: function(e) {
				if(e.keyCode == 32) {
					$(this).val($(this).val()+' ');
					return false;
				}
			}
		});
		$("span.ui-icon").css({'display': 'none'});
		<?php if ($Meu_Amigo !== false) { ?>
		$("#nome_amigo").mouseover(function(){
			$("#nome_amigo").css({
				'border': '1px',
				'border-style': 'solid',
				'border-color': '#C5DBEC'
			});
		});
		$("#nome_amigo").mouseout(function(){
			$("#nome_amigo").css({
				'border': '0px',
				'border-color': 'transparent'
			});
		});
		var salvando_apelido = false;
		var Salvar_Apelido = function(blur) {
			$("#nome_amigo").css({
				'border': '0px',
				'border-color': 'transparent'
			});
			if($.trim($("#nome_amigo").val()) == '' || $.trim($("#nome_amigo").val()) == '<?= $Usr->getNome_Completo(true) ?>') {
				$("#nome_amigo").val('<?= $Usr->getNome_Completo(true) ?>');
				salvando_apelido = true;
				$.post('<?= CONFIG_URL; ?>ajax/apelido.php', {id: '<?= $Meu_Amigo->getID() ?>', nome: ""}, function(data){
					if(data && data.ok) {
						$("#salvando_nome_amigo").remove();
						salvando_apelido = false;
						if(!blur)
							$("#nome_amigo").blur();
					}
				});
			} else {
				if(salvando_apelido)
					return;
				$("#nome_amigo").after('<img id="salvando_nome_amigo" src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." />');
				salvando_apelido = true;
				$.post('<?= CONFIG_URL; ?>ajax/apelido.php', {id: '<?= $Meu_Amigo->getID() ?>', nome: $("#nome_amigo").val()}, function(data){
					if(data && data.ok) {
						$("#salvando_nome_amigo").remove();
						salvando_apelido = false;
						if(!blur)
							$("#nome_amigo").blur();
					}
				});
			}
		}
		$("#nome_amigo").bind("keypress", function(e) {
			if(e.which == 13 && !e.shiftKey) { // Enter
				Salvar_Apelido(false);
				return false;
			}
		});
		$("#nome_amigo").blur(function() { Salvar_Apelido(true); });
		<?php
		}
		if($_tipo == 'A') {
		if($_Usuario->Favorito($Aluno)) {
		?>
		$('#link_favorito').click(function() { Remover_Favorito('<?= $Aluno->getRA(); ?>'); return false; });
		<?php
		} else {
		?>
		$('#link_favorito').click(function() { Adicionar_Favorito('<?= $Aluno->getRA(); ?>'); return false; });
		<?php
		}
		}
		?>
		$('#periodo_horario').live('change', function() {
			if($('#tab_horario input[type="radio"][name="n"]').length > 0)
				nivel = $('#tab_horario input[type="radio"][name="n"]:checked').val();
			else
				nivel = null;
			Atualizar_Horario('<?= $_matricula; ?>', '<?= $_tipo; ?>', $(this).val(), nivel);
		});
		$('#tab_horario input[type="radio"][name="n"]').live('click', function() {
			Atualizar_Horario('<?= $_matricula; ?>', '<?= $_tipo; ?>', $('#periodo_horario').val(), $(this).val());
		});
		$(window).resize(Tamanho_Abas);
		<?php if($Usr !== null) { ?>
		$('#buscar_amigos1').Procura_Amigo('lista_amigos1');
		<?php if(($Meu_Amigo === false) && ($_Usuario->Quase_Amigo($Usr) === false)) { ?>
		$('#link_amigo').click(function() { Adicionar_Amigo('<?= $Usr->getID(); ?>'); return false; });
		<?php } else { ?>
		$('#link_amigo').click(function() { Remover_Amigo('<?= $Usr->getID(); ?>'); return false; });
		<?php } ?>
		$("#mensagem").Valor_Padrao(mensagem_padrao, 'padrao');
		$("#mensagem").keyup(function(e) {
			if(e.which == 13 && !e.shiftKey) {
				Enviar_Mensagem();
				return false;
			}
			if($(this).scrollTop() > 0) {
				var tamanho = $(this).scrollTop() + $(this).height();
				$(this).height((tamanho < 108) ? tamanho : 108);
			}
			if($(this).val().length > 255)
				$(this).val($(this).val().substr(0, 255));
		});
		$("#enviar_mensagem").click(Enviar_Mensagem);
		<?php } if($_tipo == 'P') { ?>
		$("#selectoferecimento_<?= $Professor->getID(); ?>_0").change(Carregar_Avaliacoes);
		$("div.nota_slider").each(function() {
			Criar_Slider($(this));
		});
		$("div.nota_slider_fixo").each(function() {
			Criar_Slider_Fixo($(this));
		});
		if($("#sala_valor").length > 0)
			$("#sala_valor").Valor_Padrao(colaborar_padrao, "padrao");
		if($("#email_valor").length > 0)
			$("#email_valor").Valor_Padrao(colaborar_padrao, "padrao");
		if($("#pagina_valor").length > 0)
			$("#pagina_valor").val("http://");
		if($("#lattes_valor").length > 0)
			$("#lattes_valor").val("http://");
		$("a.link_colaborar").click(function() {
			var campo = $(this).attr("id").split("_")[0];
			var valor = $.trim($("#" + campo + "_valor").val().replace(colaborar_padrao, ""));

			if(valor == "" || valor == 0)
				$.guaycuru.confirmacao("Por favor envie um valor v&aacute;lido.");
			else {
				$("#" + campo + "_valor").attr('disabled', 'disabled');
				$("#" + campo + "_colaborar").hide();
				$.post("<?= CONFIG_URL; ?>ajax/colaboracao_professor.php", {campo: campo, valor: valor, id_professor: id_professor}, function(data) {
					if(data && data.ok) {
						$.guaycuru.confirmacao("Colabora&ccedil;&atilde;o enviada. Aguarde autoriza&ccedil;&atilde;o.");
						$("#" + campo + "_valor").hide();
						$("#" + campo + "_valor").after("<label>Colabora&ccedil;&atilde;o pendente j&aacute; existe. Aguardando autoriza&ccedil;&atilde;o.</label>");
						$("#" + campo + "_colaborar").hide();
					}
					else if(data.error)
						$.guaycuru.confirmacao(data.error, "<?= CONFIG_URL; ?>perfil/?professor=<?= $Professor->getID(); ?>");
					else {
						$("#" + campo + "_valor").removeAttr('disabled');
						$("#" + campo + "_colaborar").show();
						$.guaycuru.confirmacao("Houve um problema com o seu pedido. Tente novamente mais tarde.");
					}
				});
			}

			return false;
		});
		$("a.link_reclamar").click(function() {
		});
		<?php } ?>
		$("#tabs").tabs({
			show: function(event, ui) {
				if((ui.panel.id == 'tab_horario') && (!carregou_horario)) {
					Atualizar_Horario('<?= $_matricula; ?>', '<?= $_tipo; ?>');
					carregou_horario = true;
				}
				<?php if ($Usr !== null) { ?>
				else if(ui.panel.id == 'tab_atualizacoes') {
					Atualizar_Atualizacoes('<?= $Usr->getID(); ?>');
				}
				<?php } if($_tipo == 'P') { ?>
				else if((ui.panel.id == 'tab_avaliacao') && (!carregou_avaliacao)) {
					$("#selectoferecimento_<?= $Professor->getID(); ?>_0").val('');
					$("a.link_votar").live('click', function() {
						var ids = $(this).attr('id').split('_');
						if(ids[3])
							Enviar_Avaliacao($(this), ids[1], ids[2], ids[3]);
						else
							Enviar_Avaliacao($(this), ids[1], ids[2]);
						return false;
					});
					carregou_avaliacao = true;
				}
				<?php } ?>
			},
			select: function(event, ui) {
				window.location.hash = ui.tab.hash;
			}
		});
		Tamanho_Abas('tabs');
	});
	// ]]>
</script>
<?php
if($Usr !== null) {
	$_Foto = $Usr->getFoto(true);
	$_nome = $Usr->getNome_Completo(true);
	$_status = $Usr->getStatus();

	if($_Usuario->Pode_Ver($Usr, $Usr->getCompartilha_Arvore()) === true)
		$link_arvore = '<a href="'.CONFIG_URL.'arvore/?us='.$Usr->getLogin().'">Ver</a>';
	else
		$link_arvore = '&Aacute;rvore n&atilde;o compartilhada...';
	if($Usr->getLogin() == $_Usuario->getLogin())
		$link_pessoal = '<a href="'.CONFIG_URL.'editar-perfil/">Editar Perfil</a>';
	elseif($Meu_Amigo !== false) {
		$link_pessoal = '<a href="#" id="link_amigo" style="font-size: 10px;">Excluir Amigo</a>';
		if($Meu_Amigo->getApelido(false) != null)
			$_nome = $Meu_Amigo->getApelido(true);
	} elseif($_Usuario->Quase_Amigo($Usr) !== false) // Eu to esperando autorizacao dele
		$link_pessoal = '<span style=\"font-size: 10px;\">Aguardando Autoriza&ccedil;&atilde;o...</span>';
	elseif($Usr->Quase_Amigo($_Usuario) !== false) // Ele ta esperando a minha autorizacao
		$link_pessoal = '<a href="#" id="amizade_aceitar_'.$Usr->getID().'" class="amizade_aceitar" ><i>Aceitar</i></a> <a href="#" class="amizade_ignorar" id="amizade_ignorar_'.$Usr->getID().'"><i>Recusar</i></a>';
	else
		$link_pessoal = '<a href="#" id="link_amigo" style="font-size: 10px;">Solicitar Amizade</a>';
} else {
	$_Foto = Usuario::getFoto_Padrao();
	$_nome = ($_tipo == 'A') ? $Aluno->getNome(true) : $Professor->getNome(true);
	$_status = null;
	$link_arvore = "";
	$link_pessoal = ($_tipo == 'A') ? '<a href="Recomendar.php?ra='.$Aluno->getRA().'" style="font-size: 10px;">Recomende o GDE!</a>' : null;
}
?>
<div id="coluna_esquerda_wrapper">
	<div id="coluna_esquerda">
		<div id="perfil_cabecalho">
			<div id="perfil_foto">
				<img src="<?= $_Foto; ?>" alt="<?= $_nome; ?>" style="margin-bottom: 5px;" /><br /><?= $link_pessoal; ?>
			</div>
			<div id="perfil_mensagem_botoes">
				<div id="perfil_cabecalho_nome">
					<?php if($_tipo == 'A') echo ($_Usuario->Favorito($Aluno)) ? '<a href="#" id="link_favorito" title="Remover dos Favoritos"><img id="link_favorito_img" src="'.CONFIG_URL.'web/images/star_on.gif" alt="X" /></a>' : '<a href="#" id="link_favorito" title="Adicionar aos Favoritos"><img id="link_favorito_img" src="'.CONFIG_URL.'web/images/star_off.gif" alt="X" /></a>'; ?>
					<?php if($Meu_Amigo !== false) { echo $Usr->getChat_Status(true); ?>
						<input id="nome_amigo" type="text" value="<?= $_nome; ?>" style="border:none; border-color: transparent; width: 400px; font-size: 18px; font-weight: bold" title="<?= $Usr->getNome_Completo(true)?>"/>
					<?php } else { echo $_nome; } ?>
				</div>

				<?php if($Usr !== null) { ?>
					<div id="perfil_cabecalho_status"><?= $_status; ?></div>
					<div id="perfil_mensagem">
						<textarea id="mensagem" name="mensagem" rows="1" cols="50"></textarea> <a href="#" id="enviar_mensagem" class="perfil_link_enviar">Enviar</a>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php if($_tipo == 'E') { ?>
			<div class="tip" id="perfil_tip">Dica: Para acessar diretamente a p&aacute;gina deste usu&aacute;rio use <span class="link">http://gde.ir/u/<?= $Usr->getLogin(); ?></span></div>
		<?php } else { ?>
			<div class="tip" id="perfil_tip">Dica: Para acessar diretamente a p&aacute;gina deste <?= ($_tipo == 'A') ? 'aluno' : 'professor'; ?> use <span class="link">http://gde.ir/<?= strtolower($_tipo); ?>/<?= $_matricula; ?></span><?php if($Usr !== null) { ?> ou <span class="link">http://gde.ir/u/<?= $Usr->getLogin(); ?></span><?php } ?></div>
		<?php } ?>
		<div id="perfil_abas">
			<div id="tabs">
				<ul>
					<?php if($Usr !== null) { ?>
						<li><a href="#tab_atualizacoes" class="ativo">Atualiza&ccedil;&otilde;es</a></li>
						<li><a href="#tab_pessoal">Pessoal</a></li>
						<li><a href="#tab_social">Social</a></li>
						<li><a href="#tab_profissional">Profissional</a></li>
					<?php } if(($_tipo == 'A') || ($_tipo == 'P')) { ?>
						<li><a href="#tab_academico" class="ativo">Acad&ecirc;mico</a></li>
						<li><a href="#tab_horario"><?= ($_tipo == 'A') ? 'Matr&iacute;culas' : 'Hor&aacute;rio'; ?></a></li>
						<?php if(($Usr !== null) && ($Planejado_Proximo !== false)) { ?>
							<li><a href="#tab_planejamento">Planejamento</a></li>
						<?php } if($_tipo == 'P') { ?>
							<li><a href="#tab_avaliacao">Avalia&ccedil;&atilde;o</a></li>
						<?php } } ?>
				</ul>
				<?php if($Usr !== null) { ?>
					<div id="tab_atualizacoes" class="tab_content">
						<div id="tab_atualizacoes_conteudo">
							<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Atualiza&ccedil;&otilde;es...
						</div>
					</div>
					<div id="tab_pessoal" class="tab_content">
						<table cellspacing="0" class="tabela_bonyta_branca">
							<tr>
								<td width="30%"><strong>Nome:</strong></td>
								<td><?= $Usr->getNome_Completo(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Apelido:</strong></td>
								<td><?= $Usr->getApelido(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Cidade:</strong></td>
								<td><?= $Usr->getCidade(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Estado:</strong></td>
								<td><?= $Usr->getEstado(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Data de Nascimento:</strong></td>
								<td><?= $Usr->getData_Nascimento('d/m/Y'); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Relacionamento:</strong></td>
								<td><?= $Usr->getEstado_Civil(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Sexo:</strong></td>
								<td><?= $Usr->getSexo(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Outras Informa&ccedil;&otilde;es:</strong></td>
								<td><?= $Usr->getMais(true); ?></td>
							</tr>
							<?php if($_Usuario->getAdmin() === true) { ?>
								<tr>
									<td width="30%"><strong>Admin:</strong></td>
									<td><a href="VisaoAdminUsuario.php?login=<?=$Usr->getLogin(); ?>">Editar Usu&aacute;rio</a></td>
								</tr>
							<?php } ?>
						</table>
					</div>
					<div id="tab_social" class="tab_content">
						<table cellspacing="0" class="tabela_bonyta_branca">
							<?php if($Usr->getLogin() != $_Usuario->getLogin()) { ?>
								<tr>
									<td width="30%"><strong>Liga&ccedil;&atilde;o:</strong></td>
									<td>
										<?php
										$Relacionamento = $_Usuario->Relacionamento($Usr);
										if($Relacionamento === false)
											echo "Desconhecida...";
										elseif($Usr->getID() == $Relacionamento->getID())
											echo "Voc&ecirc; -> ".$Usr->getNome_Completo(true);
										else
											echo "Voc&ecirc; -> <a href=\"".CONFIG_URL."perfil/?usuario=".$Relacionamento->getLogin(true)."\">".$Relacionamento->getNome_Completo(true)."</a> -> ".$Usr->getNome(true);
										?>
									</td>
								</tr>
							<?php } ?>
							<tr>
								<td width="30%"><strong>Twitter:</strong></td>
								<td><?= ($Usr->getTwitter() != null)?'<a href="http://twitter.com/'.$Usr->getTwitter(true).'" target="_blank">Twitter</a>':null; ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Site / Blog:</strong></td>
								<td><?= ($Usr->getBlog() != null)?'<a href="'.$Usr->getBlog(true).'" target="_blank">'.$Usr->getBlog(true).'</a>':null; ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Facebook:</strong></td>
								<td><?= ($Usr->getFacebook() != null)?'<a href="'.$Usr->getFacebook(true).'" target="_blank">Perfil</a>':null; ?></td>
							</tr>
						</table>
					</div>
					<div id="tab_profissional" class="tab_content">
						<table cellspacing="0" class="tabela_bonyta_branca">
							<?php
							$today = date("d/m/Y");
							foreach($Usr->getEmpregos() as $ind => $emprego) { ?>
								<tr>
									<td width="30%"><strong><?= $emprego->getTipo(true); ?>:</strong></td>
									<td><?=$emprego->getNome(true); ?><?=($emprego->getAtual() == 't')?' - &Eacute; seu emprego atual':'';?></td>
								</tr>
								<?php if($emprego->getCargo() != null){ ?>
									<tr>
										<td width="30%"><strong>Cargo:</strong></td>
										<td><?=$emprego->getCargo(true); ?></td>
									</tr>
								<?php } ?>
								<tr>
									<td width="30%"><strong>Per&iacute;odo:</strong></td>
									<td><?=$emprego->getInicio('d/m/Y');?> - <?=($emprego->getAtual() == 't')?$today:$emprego->getFim('d/m/Y');?></td>
								</tr>
								<tr>
									<td width="30%"><strong>Site:</strong></td>
									<td><?=$emprego->getSite(); ?></td>
								</tr>
							<?php }	if($Usr->getProcurando_Emprego() != null) { ?>
								<tr>
									<td><strong>Est&aacute; procurando emprego?</strong></td>
									<td><?= $Usr->getProcurando_Emprego(true); ?></td>
								</tr>
							<?php } ?>
							<tr>
								<td width="30%"><strong>Experi&ecirc;ncias Profissionais:</strong></td>
								<td><?= $Usr->getExp_Profissionais(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Habilidades Pessoais:</strong></td>
								<td><?= $Usr->getHab_Pessoais(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Especialidades T&eacute;cnicas:</strong></td>
								<td><?= $Usr->getEsp_Tecnicas(true); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Outras informa&ccedil;&otilde;es relevantes:</strong></td>
								<td><?= $Usr->getInfo_Profissional(true); ?></td>
							</tr>
						</table>
					</div>
				<?php } if($_tipo == 'A') { ?>
					<div id="tab_academico" class="tab_content">
						<table cellspacing="0" class="tabela_bonyta_branca">
							<tr>
								<td width="30%"><strong>Nome:</strong></td>
								<td><?= $Aluno->getNome(); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>RA:</strong></td>
								<td><?=$Aluno->getRA(true); ?></td>
							</tr>
							<?php if($Aluno->getNivel() != null) { ?>
								<tr>
									<td width="30%"><strong>N&iacute;vel:</strong></td>
									<td><?= $Aluno->getNivel(true); ?></td>
								</tr>
								<tr>
									<td width="30%"><strong>Curso:</strong></td>
									<td><?= ($Aluno->getCurso(false) !== null) ? $Aluno->getCurso()->getNome(true) : '-'; ?> (<?= ($Aluno->getCurso(false) !== null) ? $Aluno->getCurso()->getNumero(true) : '?'; ?>)</td>
								</tr>
								<tr>
									<td width="30%"><strong>Modalidade:</strong></td>
									<td><?= $Aluno->getModalidade(true); ?></td>
								</tr>
								<tr>
									<td width="30%"><strong>&Aacute;rvore / Integraliza&ccedil;&atilde;o:</strong></td>
									<td><?=$link_arvore; ?></td>
								</tr>
							<?php } if($Aluno->getNivel_Pos() != null) { ?>
								<tr>
									<td width="30%"><strong>N&iacute;vel (P&oacute;s):</strong></td>
									<td><?= $Aluno->getNivel_Pos(true); ?></td>
								</tr>
								<tr>
									<td width="30%"><strong>Curso (P&oacute;s):</strong></td>
									<td><?= ($Aluno->getCurso_Pos(false) !== null) ? $Aluno->getCurso_Pos()->getNome(true) : '-'; ?> (<?= ($Aluno->getCurso_Pos(false) !== null) ? $Aluno->getCurso_Pos()->getNumero(true) : '?'; ?>)</td>
								</tr>
								<tr>
									<td width="30%"><strong>Modalidade (P&oacute;s):</strong></td>
									<td><?= $Aluno->getModalidade_Pos(true); ?></td>
								</tr>
							<?php } if($_Usuario->getAdmin() === true) { ?>
								<tr>
									<td width="30%"><strong>Admin:</strong></td>
									<td><a href="VisaoAdminAluno.php?ra=<?=$Aluno->getRA(); ?>">Editar Aluno</a></td>
								</tr>
							<?php } ?>
						</table>
					</div>
					<div id="tab_horario" class="tab_content">
						<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Matr&iacute;culas e Hor&aacute;rio...
					</div>
				<?php } elseif($_tipo == 'P') { ?>
					<div id="tab_academico" class="tab_content">
						<table cellspacing="0" class="tabela_bonyta_branca">
							<tr>
								<td width="30%"><strong>Nome:</strong></td>
								<td><?= $Professor->getNome(); ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Instituto:</strong></td>
								<td><?= $html_professor_instituto; ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Sala:</strong></td>
								<td><?= $html_professor_sala; ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>E-mail:</strong></td>
								<td><?= $html_professor_email; ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>P&aacute;gina:</strong></td>
								<td><?= $html_professor_pagina; ?></td>
							</tr>
							<tr>
								<td width="30%"><strong>Curr&iacute;culo Lattes:</strong></td>
								<td><?= $html_professor_lattes; ?></td>
							</tr>
						</table>
					</div>
					<div id="tab_horario" class="tab_content">
						<img src="<?= CONFIG_URL; ?>web/images/loading.gif" alt="..." /> Carregando Hor&aacute;rio...
					</div>
				<?php } if(($Usr !== null) && ($Planejado_Proximo !== false)) { ?>
					<div id="tab_planejamento" class="tab_content">
						<table border="0" cellspacing="0" class="tabela_bonyta_branca">
							<tr>
								<td>
									<div id="planejado_compartilhado"></div>
									<div class="TabView" id="TabView">
										<div class="Tabs">
											<?php
											$Planejados = Planejado::Por_Usuario($Usr, $Planejado_Proximo, (!$_Usuario->getAdmin()));
											if(count($Planejados) == 0)
												echo "N&atilde;o existe nenhum Planejado compartilhado para ser exibido.";
											else
												foreach($Planejados as $p => $Pln)
													echo " <a href=\"#\" id=\"opcao_".$Pln->getID()."\" onClick=\"return carrega_planejado('".$Pln->getID()."');\">Op&ccedil;&atilde;o ".++$p."</a> ";
											?>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</div>
				<?php } if($_tipo == 'P') { ?>
					<div id="tab_avaliacao" class="tab_content">
						<div class="gde_jquery_ui">
							<h2>Como Professor(a)</h2>
							<?php
							foreach(AvaliacaoPergunta::Listar('p') as $Pergunta) {
								$Media = $Pergunta->getMedia($Professor->getID());
								echo "<strong>Pergunta: ".$Pergunta->getPergunta()."</strong><br />";
								if($Media['v'] < CONFIG_AVALIACAO_MINIMO)
									echo "Ainda n&atilde;o foi atingido o n&uacute;mero m&iacute;nimo de votos.<br /><br />";
								else {
									//echo "Pontua&ccedil;&atilde;o: <span id=\"span_fixo_".$Pergunta->getID()."_".$Professor->getID()."\" style=\"font-weight: bold;\">".number_format($Media['w'], 2, ',', '.')."</span> (".$Media['v']." votos) - Ranking: <strong>".$Pergunta->Ranking($Professor, null)."/".$Pergunta->Max_Ranking(null)."</strong><div id=\"fixo_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"nota_slider_fixo\"></div><br />";
									echo "Pontua&ccedil;&atilde;o: <span id=\"span_fixo_".$Pergunta->getID()."_".$Professor->getID()."\" style=\"font-weight: bold;\">".number_format($Media['w'], 2, ',', '.')."</span> (".$Media['v']." votos)";
									if($_Usuario->getAdmin() === true)
										echo " - Ranking: <strong>".$Pergunta->Ranking($Professor, null)."/".$Pergunta->Max_Ranking(null)."</strong><div id=\"fixo_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"nota_slider_fixo\"></div>";
									echo "<br />";
								}
								$pode = $Pergunta->Pode_Votar($_Usuario, $Professor, null);
								if($pode === true)
									echo "<div id=\"votar_nota_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"seu_voto\">Seu voto: <span id=\"span_nota_".$Pergunta->getID()."_".$Professor->getID()."\"></span><div id=\"nota_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"nota_slider\"></div><a href=\"#\" id=\"votar_".$Pergunta->getID()."_".$Professor->getID()."\" class=\"link_votar\">Votar</a></div>";
								elseif($pode == AvaliacaoPergunta::ERRO_JA_VOTOU)
									echo "Voc&ecirc; j&aacute; votou nesta pergunta! Seu voto: ".$Pergunta->Meu_Voto($_Usuario, $Professor)."<br />";
								elseif($pode == AvaliacaoPergunta::ERRO_NAO_CURSOU)
									echo "Voc&ecirc; n&atilde;o pode votar pois ainda n&atilde;o cursou nenhuma Disciplina com ".$Professor->getNome().".";
								elseif($pode == AvaliacaoPergunta::ERRO_NAO_ALUNO)
									echo "Voc&ecirc; n&atilde;o pode votar pois apenas Alunos podem avaliar Professores.";
								echo "<br /><br />";
							}
							?>
							<h2>Como Professor(a) em <select id="selectoferecimento_<?= $Professor->getID(); ?>_0" class="avaliacao_oferecimento" name="id_oferecimento"><option value="" selected="selected">Selecione</option><?= $ofs; ?></select></h2>
							<div id="div_avaliacoes_<?= $Professor->getID(); ?>_0"></div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php if($Usr !== null) { ?>
	<div id="coluna_direita" class="gde_jquery_ui">
		<div id="menuAccordion">
			<h3 id="accordion_amigos" style="padding: 5px">Amigos (<span id="span_numero_amigos1"><?= count($Amigos); ?></span>): <input type="text" id="buscar_amigos1" /></h3>
			<div id="lista_amigos1" >
				<div id="amigos_on_1"></div>
				<div id="amigos_off_1"></div>
				<?php
				if(count($Amigos) == 0)
					echo '<div style="margin: 10px 10px">'.$Usr->getNome(true).' ainda n&atilde;o possui nenhum Amigo...</div>';
				else {
					foreach($Amigos as $Amigo) { ?>
						<div class="amigo" id="amigo_<?= $Amigo->getAmigo()->getID() ?>">
							<div class="amigo_foto">
								<a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo()->getLogin() ?>" class="link_sem_decoracao" title="<?= $Amigo->getAmigo()->getNome_Completo(true) ?>">
									<img src="<?= $Amigo->getAmigo()->getFoto(true, true) ?>" border="0" alt="<?= $Amigo->getAmigo()->getNome(true) ?>" />
								</a>
							</div>
							<div class="amigo_nome">
								<img src="<?= CONFIG_URL; ?>web/images/status_vs.png" class="status_icone status_icone_<?= $Amigo->getAmigo()->getID(); ?>" alt="?" />
								<a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo()->getLogin() ?>" class="amigo" title="<?= $Amigo->getAmigo()->getNome_Completo(true) ?>"><?= ($_sou_eu) ? substr($Amigo->getApelido(true), 0, 10) : $Amigo->getAmigo()->getNome() ?></a>
							</div>
						</div>
					<?php } } ?>
			</div>
			<?php if($Usr->getID() != $_Usuario->getID() && (count($Em_Comum) > 0)) { ?>
				<h3 id="accordion_em_comum" style="padding: 5px">Amigos Em Comum (<span id="span_numero_amigos3"><?= count($Em_Comum); ?></span>):</h3>
				<div id="lista_amigos3">
					<div id="amigos_on_3"></div>
					<div id="amigos_off_3"></div>
					<?php foreach($Em_Comum as $Amigo) { ?>
						<div class="amigo" id="amigocomum_<?= $Amigo->getAmigo()->getID(); ?>">
							<div class="amigo_foto">
								<a href="<?= CONFIG_URL;?>perfil/?usuario=<?= $Amigo->getAmigo()->getLogin(true) ?>" class="link_sem_decoracao" title="<?= $Amigo->getAmigo()->getNome_Completo(true) ?>">
									<img src="<?= $Amigo->getAmigo()->getFoto(true, true) ?>" border="0" alt="<?= $Amigo->getAmigo()->getNome(true) ?>" />
								</a>
							</div>
							<div class="amigo_nome">
								<img src="<?= CONFIG_URL; ?>web/images/status_vs.png" class="status_icone status_icone_<?= $Amigo->getAmigo()->getID(); ?>" alt="?" />
								<a href="<?= CONFIG_URL;?>perfil/?usuario=<?= $Amigo->getAmigo()->getLogin(true); ?>" class="amigo" title="<?= $Amigo->getAmigo()->getNome_Completo(true); ?>"><?= substr($Amigo->getApelido(true), 0, 10); ?></a>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>
<?= $FIM; ?>
