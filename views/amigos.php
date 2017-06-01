<?php

namespace GDE;

define('TITULO', 'Amigos');

require_once('../common/common.inc.php');

$Quase_Amigos = $_Usuario->getQuase_Amigos();
$Autorizacoes = $_Usuario->getAmigos_Pendentes();
$Amigos = UsuarioAmigo::Ordenar_Por_Nome($_Usuario->Amigos());

?>
<script type="text/javascript">
	// <![CDATA[
	var remover_amigo = function(id) {
		$.guaycuru.simnao("Tem certeza que deseja remover este Usu&aacute;rio da sua lista de Amigos?", function() {
			$.post(CONFIG_URL + 'ajax/amigo.php', {i: id, tipo: 'r'}, function(data) {
				if(data == '1') {
					$.guaycuru.confirmacao("Usu&aacute;rio n&atilde;o est&aacute; mais na sua lista de amigos!", null);
					history.go(0);
				}
				else if(data == '2')
					$.guaycuru.confirmacao("N&atilde;o foi poss&iacute;vel remover o usu&aacute;rio da sua lista de amigos.");
			});
		}, {});
	};

	var autorizar_amigo = function(id) {
		$.post(CONFIG_URL + 'ajax/amigo.php', {i: id, tipo: 'h'}, function(data) {
			if(data == '1') {
				$.guaycuru.confirmacao("O Pedido de Amizade foi aceito com sucesso!");
				history.go(0);
			}
			else if(data == '2')
				$.guaycuru.confirmacao("N&atilde;o foi poss&iacute;vel aceitar o Pedido de Amizade.");
		});
	};

	Carrega = function(tp) {
		if(tp == 0) {
			$("#tabela_amigos_foto").show();
			$("#tabela_amigos_lista").hide();
		} else if(tp == 1){
			$("#tabela_amigos_foto").hide();
			$("#tabela_amigos_lista").show();
		} else if(tp == 2){
			$("#tabela_quaseAmigos_lista").hide();
			$("#tabela_quaseAmigos_foto").show();
		} else if(tp == 3){
			$("#tabela_quaseAmigos_foto").hide();
			$("#tabela_quaseAmigos_lista").show();
		} else if(tp == 4){
			$("#tabela_pendentes_foto").show();
			$("#tabela_pendentes_lista").hide();
		} else {
			$("#tabela_pendentes_foto").hide();
			$("#tabela_pendentes_lista").show();
		}
	};
	$(document).ready(function() {
		Carrega(0);
		Carrega(2);
		Carrega(4);
	});

	// ]]>
</script>
<?php

if(count($Autorizacoes) > 0) { ?>
<h2>Autoriza&ccedil;&otilde;es Pendentes</h2>(<a href="#" id="link_amigos_foto" onclick="Carrega(4); return false;">Fotos</a> | <a href="#" id="link_amigos_lista" onclick="Carrega(5); return false;">Lista</a>)
<table border="0" id="tabela_pendentes_foto" >
	<tr>
<?php
$i = 0;
foreach($Autorizacoes as $Auth) {
	$Aluno = $Auth->getUsuario(true)->getAluno(true);
	?>
	<td width="50%"><table border="1" width="100%">
			<tr>
				<td width="128" height="150" align="center" rowspan="6"><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Auth->getUsuario(true)->getLogin(true); ?>"><img src="<?= $Auth->getUsuario(true)->getFoto(true); ?>" border="0" alt="<?= $Auth->getUsuario(true)->getNome(true); ?>" /></a></td>
				<td width="20%"><strong>Nome:</strong></td>
				<td><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Auth->getUsuario(true)->getLogin(true); ?>"><?= $Auth->getUsuario(true)->getNome_Completo(true); ?></a></td>
			</tr>
			<tr>
				<td width="20%"><strong>RA:</strong></td>
				<td><?= $Aluno->getRA(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>N&iacute;vel:</strong></td>
				<td><?= $Aluno->getNivel(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Curso:</strong></td>
				<td><?= $Aluno->getCurso(true)->getNome(true)." (".$Aluno->getCurso(true)->getNumero(true).")"; ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Modalidade:</strong></td>
				<td><?= $Aluno->getModalidade(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>A&ccedil;&atilde;o:</strong></td>
				<td><a href="#" onclick="autorizar_amigo('<?= $Auth->getUsuario()->getID(); ?>'); return false;">Aceitar</a> | <a href="#" onclick="remover_amigo('<?= $Auth->getUsuario()->getID(); ?>'); return false;">Recusar</a></td></tr>
		</table></td>
	<?php
	if($i++ % 2 == 1)
		echo "</tr><tr>";
}
$i = 0;
?>
	</tr>
</table>

<table border="1" width="100%" class="tabela_busca" id="tabela_pendentes_lista" >
	<tr>
		<th>Nome</th>
		<th>RA</th>
		<th>N&iacute;vel</th>
		<th>Curso</th>
		<th>Modalidade</th>
		<th>A&ccedil;&atilde;o</th>
	</tr>
	<?php
	foreach($Autorizacoes as $Auth) {
		$Aluno = $Auth->getUsuario()->getAluno();
		?>
		<tr>
			<td><a href="Perfil.php?l=<?= $Auth->getUsuario()->getLogin(); ?>"><?= $Auth->getUsuario(true)->getNome_Completo(true); ?></a></td>
			<td><?= $Aluno->getRA(true); ?></td>
			<td><?= $Aluno->getNivel(true); ?></td>
			<td><?= $Aluno->getCurso(true)->getNome(true)." (".$Aluno->getCurso(true)->getNumero(true).")"; ?></td>
			<td><?= $Aluno->getModalidade(true); ?></td>
			<td><a href="#" onclick="autorizar_amigo('<?= $Auth->getUsuario(true)->getID(); ?>'); return false;">Autorizar</a> | <a href="#" onclick="remover_amigo('<?= $Auth->getUsuario(true)->getID(); ?>'); return false;">Recusar</a></td>
		</tr>
		<?php
	}
	?>
	</tr>
</table>

<?php }

if(count($Amigos) > 0) { ?>
<h2>Amigos</h2>(<a href="#" id="link_amigos_foto" onclick="Carrega(0); return false;">Fotos</a> | <a href="#" id="link_amigos_lista" onclick="Carrega(1); return false;">Lista</a>)
<table border="0" id="tabela_amigos_foto" ><tr>
<?php
$i = 0;
foreach($Amigos as $Amigo) {
	$Aluno = $Amigo->getAmigo(true)->getAluno(true);
	?>
	<td width="50%"><table border="1" width="100%">
			<tr>
				<td width="128" height="150" align="center" rowspan="6"><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo(true)->getLogin(true); ?>"><img src="<?= $Amigo->getAmigo(true)->getFoto(true); ?>" border="0" alt="<?= $Amigo->getAmigo(true)->getNome(true); ?>" /></a></td>
				<td width="20%"><strong>Nome:</strong></td>
				<td><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo(true)->getLogin(true); ?>"><?= $Amigo->getAmigo()->getNome_Completo(true); ?></a></td>
			</tr>
			<tr>
				<td width="20%"><strong>RA:</strong></td>
				<td><?= $Aluno->getRA(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>N&iacute;vel:</strong></td>
				<td><?= $Aluno->getNivel(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Curso:</strong></td>
				<td><?= $Aluno->getCurso(true)->getNome(true)." (".$Aluno->getCurso(true)->getNumero(true).")"; ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Modalidade:</strong></td>
				<td><?= $Aluno->getModalidade(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Remover:</strong></td>
				<td><a href="#" onclick="remover_amigo('<?= $Amigo->getAmigo(true)->getID(); ?>'); return false;">Remover</a></td>
			</tr>
		</table></td>
	<?php
	if($i++ % 2 == 1)
		echo "</tr><tr>";
} ?>
		<td>&nbsp;</td>
	</tr>
</table>

<table border="1" width="100%" class="tabela_busca" id="tabela_amigos_lista" >
	<tr>
		<th>RA</th>
		<th>Nome</th>
		<th>N&iacute;vel</th>
		<th>Curso</th>
		<th>Modalidade</th>
		<th>Remover</th>
	</tr>
	<?php
	foreach($Amigos as $Amigo) {
		$Aluno = $Amigo->getAmigo()->getAluno();
		?>
		<tr>
			<td><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Aluno->getRA(true); ?>"><?= $Aluno->getRA(true); ?></a></td>
			<td><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo(true)->getLogin(true); ?>"><?= $Amigo->getAmigo(true)->getNome_Completo(true); ?></a></td>
			<td><?= $Aluno->getNivel(true); ?></td>
			<td><?= $Aluno->getCurso(true)->getNome(true)." (".$Aluno->getCurso(true)->getNumero(true).")"; ?></td>
			<td><?= $Aluno->getModalidade(true); ?></td>
			<td><a href="#" onclick="remover_amigo('<?= $Amigo->getAmigo(true)->getID(); ?>'); return false;">Remover</a></td>
		</tr>
		<?php
	}
	?>
</table>
<?php
}

if(count($Quase_Amigos) > 0) { ?>
<h2>Amigos - Aguardando Autoriza&ccedil;&atilde;o</h2>(<a href="#" onclick="Carrega(2); return false;" target="false" id="link_fotos">Fotos</a> | <a href="#" onclick="Carrega(3); return false;" target="false" id="link_lista">Lista</a>)
<table border="0" class="tabela_bonyta_branca" id="tabela_quaseAmigos_foto" ><tr>
<?php
$i = 0;
foreach($Quase_Amigos as $Amigo) {
	$Aluno = $Amigo->getAmigo()->getAluno(true);
	?>
	<td width="50%"><table border="1" width="100%">
			<tr>
				<td width="128" height="150" align="center" rowspan="6"><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo(true)->getLogin(true); ?>"><img src="<?= $Amigo->getAmigo(true)->getFoto(true); ?>" border="0" alt="<?= $Amigo->getAmigo(true)->getNome(true); ?>" /></a></td>
				<td width="20%"><strong>Nome:</strong></td>
				<td><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo(true)->getLogin(true); ?>"><?= $Amigo->getAmigo(true)->getNome_Completo(true); ?></a></td>
			</tr>
			<tr>
				<td width="20%"><strong>RA:</strong></td>
				<td><?= $Aluno->getRA(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>N&iacute;vel:</strong></td>
				<td><?= $Aluno->getNivel(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Curso:</strong></td>
				<td><?= $Aluno->getCurso(true)->getNome(true)." (".$Aluno->getCurso(true)->getNumero(true).")"; ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Modalidade:</strong></td>
				<td><?= $Aluno->getModalidade(true); ?></td>
			</tr>
			<tr>
				<td width="20%"><strong>Remover:</strong></td>
				<td><a href="#" onclick="remover_amigo('<?= $Amigo->getAmigo(true)->getID(); ?>'); return false;">Remover</a> <i>(Aguardando Autoriza&ccedil;&atilde;o)</i></td>
			</tr>
		</table></td>
	<?php
	if($i++ % 2 == 1)
		echo "</tr><tr>";
} ?>
		<td>&nbsp;</td>
	</tr>
</table>

<table border="1" width="100%" class="tabela_busca" id="tabela_quaseAmigos_lista" >
	<tr>
		<th>RA</th>
		<th>Nome</th>
		<th>N&iacute;vel</th>
		<th>Curso</th>
		<th>Modalidade</th>
		<th>Remover</th>
	</tr>
	<?php
	foreach($Quase_Amigos as $Amigo) {
		$Aluno = $Amigo->getAmigo()->getAluno();
		?>
		<tr>
			<td><a href="<?= CONFIG_URL; ?>perfil/?aluno=<?= $Aluno->getRA(true); ?>"><?= $Aluno->getRA(true); ?></a></td>
			<td><a href="<?= CONFIG_URL; ?>perfil/?usuario=<?= $Amigo->getAmigo(true)->getLogin(true); ?>"><?= $Amigo->getAmigo(true)->getNome(true).' '.$Amigo->getAmigo(true)->getSobrenome(true); ?></a></td>
			<td><?= $Aluno->getNivel(true); ?></td>
			<td><?= $Aluno->getCurso(true)->getNome(true)." (".$Aluno->getCurso(true)->getNumero(true).")"; ?></td>
			<td><?= $Aluno->getModalidade(true); ?></td>
			<td><a href="#" onclick="remover_amigo('<?= $Amigo->getAmigo(true)->getID(); ?>'); return false;">Remover</a></td>
		</tr>
		<?php
	}
	?>
</table>
<?php
}
echo $FIM;
