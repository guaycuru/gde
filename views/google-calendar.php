<?php

namespace GDE;

define('NO_HTML', true);
define('TITULO', false);

require_once('../common/common.inc.php');

if(isset($_GET['state'])) {
  $state = explode(",", $_GET['state']);
  $ra = $state[0];
  $p = $state[1];
  $n = $state[2];
} else {
  $ra = (isset($_GET['ra'])) ? intval($_GET['ra']) : -1;
  $p = (isset($_GET['p'])) ? intval($_GET['p']) : null;
  $n = (isset($_GET['n'])) ? $_GET['n'][0] : 'G';
}

$estado = $ra.','.$p.','.$n;

// Client da API
$Calendar = new GooglCalendar($estado);

// Se nao temos o codigo, pedimos um
if(!isset($_GET['code']) && empty($_GET['error'])) {
  $Calendar->setTokenAutenticacao();
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

    if(!$Periodo_Selecionado->temInicioEFim()) {
      echo "<h1>O Período escolhido não está disponível para ser adicionado ao Calendar</h1>";
    } else {
      // coloca o token na sessao para usar na insercao
      $_SESSION['token'] = $Calendar->setTokenAcesso($_GET['code']);
      // Servico da API do Calendar
      $Calendar->setServico();

      $Aluno = ($ra > 0) ? Aluno::Load($ra) : $_Usuario->getAluno(true);
      $Horario = $Aluno->Monta_Horario($Periodo_Selecionado->getPeriodo(), $n);

      $UsuarioAluno = ($Aluno->getUsuario(false) !== null) ? $Aluno->getUsuario(false) : new Usuario();
      $pode_ver = $_Usuario->Pode_Ver($UsuarioAluno, 'horario');
      if($pode_ver !== true)
      	exit;

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
            <?php $Periodo_Selecionado->getDatasImportantesHTML(); ?>
          </div>
          <br>
          <br>
          <script type="text/javascript">
            function adicionaNoCalendar() {
              let ra = <?php echo $ra; ?>;
              let periodo = <?php echo $p; ?>;
              let nivel = '<?php echo $n; ?>';

              let select = document.getElementById('select-id-calendario')
              let idCalendario = select[select.selectedIndex].value

              let datasImportantes = $('#checkbox-datas-importantes').is(":checked")

              let autorizou = true
              let nomeCalendario = $('#input-novo-calendario').val()

              if(nomeCalendario !== '') {
                autorizou = confirm('Você deseja criar um calendario novo chamado "' + nomeCalendario + '"?')
                idCalendario = ''
              }

              if(autorizou) {
                $('#overlay').show()
                let parametros = { nivel: nivel, ra: ra, nomeCalendario: nomeCalendario, idCalendario: idCalendario, periodo: periodo, datasImportantes: datasImportantes }
                $.post("<?= CONFIG_URL; ?>ajax/google_calendar.php", parametros,
                  function(data) {
                    if(data) {
                      console.log(data)
                      alert("Algo deu errado")
                    } else {
                      alert("Seu horário foi adicionado ao Calendar")
                      window.close();
                    }
                  }
                );
              } else {
                // alert('Se deseja usar um calendário já existente selecione-o sem digitar nada na caixa de texto')
                document.getElementById('input-novo-calendario').value = ''
              }
            }
          </script>

          <button type="button" onclick="adicionaNoCalendar()">Adicionar hor&aacute;rios no calend&aacute;rio</button>
        </div>
      </div>
    </div>
    <?php
    }
  }
  ?>
</body>
<html>
