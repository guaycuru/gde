<?php
 namespace GDE;
 define('NO_HTML', true);
define('TITULO', false);
 require_once('../common/common.inc.php');
 $ra = (isset($_GET['ra'])) ? intval($_GET['ra']) : -1;
$p = (isset($_GET['p'])) ? intval($_GET['p']) : null;
$n = (isset($_GET['n'])) ? $_GET['n'][0] : 'G';

$Calendar = new GooglCalendar;

// Se nao temos o codigo, pedimos um
if (!isset($_GET['code']) && empty($_GET['error'])) {
  $Calendar->setTokenAutenticacao();
}

 ?>
<html>
<link rel="stylesheet" href="<?= CONFIG_URL; ?>web/css/gde.css?<?= REVISION; ?>" type="text/css" />
<head>
</head>
<body style="padding: 20px;">
  <?php
  // Checa se o usuario negou as permissoes necessarias
  if (!empty($_GET['error'])){
    echo "<h1>Não será possível criar os horários sem autorização</h1>";
  } else {

    $Calendar->setTokenAcesso($_GET['code']);

    // Servico da API do Calendar
    $Calendar->setServico();

    $Periodo_Selecionado = ($p > 0) ? Periodo::Load($p) : Periodo::getAtual();

    $Aluno = ($ra > 0) ? Aluno::Load($ra) : $_Usuario->getAluno(true);
    $Horario = $Aluno->Monta_Horario($Periodo_Selecionado->getPeriodo(), $n);

    $UsuarioAluno = ($Aluno->getUsuario(false) !== null) ? $Aluno->getUsuario(false) : new Usuario();
    $pode_ver = $_Usuario->Pode_Ver($UsuarioAluno, 'horario');
    if($pode_ver !== true)
    	exit;

    $meu = ($_Usuario->getAluno(true)->getID() == $Aluno->getID());
    $limpos = Util::Horarios_Livres($Horario);
  ?>
  <div style="background-color: #FFFFFF; margin: 0 auto; width: auto; text-align: left;">
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
              foreach ($listaCalendarios->getItems() as $calendarioAtual) {
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
          <input type="checkbox" id="checkbox-datas-importantes">Adicionar datas do calendário da UNICAMP</input>
          <ul id='calendario-unicamp'>
            <?php $Periodo_Selecionado->getDatasImportantesHTML(); ?>
          </ul>
        </div>
        <br>
        <br>
        <script type="text/javascript">
          function adicionaNoCalendar() {
            alert("Clicou")
          }
        </script>

        <button type="button" onclick="adicionaNoCalendar()">Adicionar hor&aacute;rios no calend&aacute;rio</button>
      </div>
    </div>
  </div>
<?php } ?>
</body>
<html>
