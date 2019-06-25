<?php

namespace GDE;

define('NO_HTML', true);
define('TITULO', false);

require_once('../common/common.inc.php');

if(!empty($_GET['state'])) {
	$state = explode(",", $_GET['state']);
	$p = (isset($state[0])) ? intval($state[0]) : '';
	$n = (isset($state[1])) ? $state[1][0] : '';
} else {
	$p = (isset($_GET['p'])) ? intval($_GET['p']) : null;
	$n = (isset($_GET['n'])) ? $_GET['n'][0] : 'G';
}

$estado = $p.','.$n;

// Client da API
$Calendar = new GoogleCalendar($estado);

// Se nao temos o codigo, pedimos um
if(empty($_GET['code']) && empty($_GET['error'])) {
	$Calendar->setTokenAutenticacao();
	exit;
}

?>
<html>
<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/gde.css?<?= REVISION; ?>" type="text/css" />
<script type="text/javascript" src="<?= CONFIG_URL; ?>web/js/jquery-1.7.2.min.js"></script>
<head>
</head>
<body style="padding: 20px;">
	<?php
	// Checa se o usuario negou as permissoes necessarias
	if(!empty($_GET['error'])) {
		echo "<h1>Não será possível criar os horários sem autorização</h1>";
	} else {

		$Periodo_Selecionado = ($p > 0) ? Periodo::Load($p) : Periodo::getAtual();

		if(!$Periodo_Selecionado->Tem_Inicio_E_Fim()) {
			echo "<h1>O Período escolhido não está disponível para ser adicionado ao Calendar</h1>";
		} else {
			// coloca o token na sessao para usar na insercao
			$token = $Calendar->setTokenAcesso($_GET['code']);
			if($token === false) {
				die('Algo deu errado. Por favor tente novamente');
			}
			$_SESSION['token'] = $token;
			// Servico da API do Calendar
			$Calendar->setServico();

			$Aluno = $_Usuario->getAluno(true);
			$Horario = $Aluno->Monta_Horario($Periodo_Selecionado->getPeriodo(), $n);

			$meu = ($_Usuario->getAluno(true)->getID() == $Aluno->getID());
			$limpos = Util::Horarios_Livres($Horario);
			?>
			<div id="overlay" style="width: 100%; height: 100%; background-color: black; opacity: 0.7; display: none; z-index: 1000; position: absolute; top: 0; left: 0;">
				<h1 style="align: center; text-align: center; vertical-align: center; font-size: 40px; user-select: none;">Processando...</h1>
			</div>

			<div style="background-color: #FFFFFF; margin: 0 auto; width: auto; text-align: left; position: absolute; top: 20;">
				<div id="content_bg">
					<div style="padding: 20px 15px 20px 15px; width:auto;">
						<h1 id="anoPeriodo"><?= $Periodo_Selecionado->getNome(false); ?></h1>
						<br>
						<h3>Selecione um Calend&aacute;rio existente ou digite algum nome para criar um novo</h3>
						<br>
						<br>
						<div>
							<div style="float: left;">
								<select id='select-id-calendario'>
									<?php
									// Pega a lista de calendarios do usuario
									$listaCalendarios = $Calendar->getCalendarios();

									// Constroi uma lista de id para nome
									foreach($listaCalendarios->getItems() as $calendarioAtual) {
										echo "<option value=" . $calendarioAtual->getId() . ">" . $calendarioAtual->getSummary() . "</option>";
									}
									?>
								</select>
							</div>
							<div style="float: left; padding-left: 30px;">
								<input id='input-novo-calendario' type="text" placeholder="Nome do calendário"/>
							</div>
						</div>

						<br><br>
						<div style="clear:both;">
							<?= $Periodo_Selecionado->Datas_Importantes_HTML(); ?>
						</div>
						<br>
						<br>
						<script type="text/javascript">
						function adicionaNoCalendar() {
							var periodo = '<?= $p; ?>';
							var nivel = '<?= $n; ?>';

							var select = document.getElementById('select-id-calendario');
							var idCalendario = select[select.selectedIndex].value;

							var datasImportantes = $('#checkbox-datas-importantes').is(":checked");

							var autorizou = true;
							var nomeCalendario = $('#input-novo-calendario').val();

							if(nomeCalendario !== '') {
								autorizou = confirm('Você deseja criar um calendario novo chamado "' + nomeCalendario + '"?');
								idCalendario = '';
							}

							if(autorizou) {
								$('#overlay').show();
								var parametros = { nivel: nivel, nomeCalendario: nomeCalendario, idCalendario: idCalendario, periodo: periodo, datasImportantes: datasImportantes }
								$.post("<?= CONFIG_URL; ?>ajax/google_calendar.php", parametros,
								function(data) {
									if(data) {
										alert("Algo deu errado");
									} else {
										alert("Seu horário foi adicionado ao Calendar");
										window.close();
									}
								}
							);
						} else {
							// alert('Se deseja usar um calendário já existente selecione-o sem digitar nada na caixa de texto')
							document.getElementById('input-novo-calendario').value = '';
						}
					}
					</script>

					<button id="botao-adicionar-calendario" type="button" onclick="adicionaNoCalendar()">Adicionar hor&aacute;rios no calend&aacute;rio</button>
				</div>
			</div>
		</div>
		<?php
	}
}
?>
</body>
<html>
