<?php

namespace GDE;

define('TITULO', 'Exportar para Agenda (.ics)');
require_once('../common/common.inc.php');

$periodoId = isset($_GET['p']) ? intval($_GET['p']) : 0;
$Periodo = ($periodoId > 0) ? Periodo::Load($periodoId) : Periodo::getAtual();
$urlIcs = CONFIG_URL . 'ajax/google_calendar.php?unicamp=1' . (($Periodo && $Periodo->getID()) ? '&periodo=' . intval($Periodo->getID()) : '');
?>
<style>
	.botao_exportar_agenda {
		display: inline-block;
		padding: 3px 10px;
		font-family: Tahoma, Helvetica, Arial, sans-serif;
		font-size: 11px;
		font-weight: bold !important;
		border: 1px solid #C5DBEC;
		border-radius: 3px;
		background: #DFF6FC url(<?= CONFIG_URL; ?>web/images/ui-bg_glass_85_dff6fc_1x400.png) 50% 50% repeat-x;
		color: #005580 !important;
		text-decoration: none !important;
		cursor: pointer;
	}
	.botao_exportar_agenda:hover {
		border: 1px solid #79B7E7;
		background: #639BAC url(<?= CONFIG_URL; ?>web/images/ui-bg_glass_75_639bac_1x400.png) 50% 50% repeat-x;
		color: #FFFFFF !important;
	}
</style>
<h2>Exporta&ccedil;&atilde;o de Hor&aacute;rios para Google Calendar e Agendas (.ics)</h2>
<p>A integra&ccedil;&atilde;o legada via OAuth da API do Google foi substitu&iacute;da pelo formato universal <strong>iCalendar (.ics / RFC 5545)</strong>, compat&iacute;vel com Google Calendar, Apple Calendar, Outlook e aplicativos m&oacute;veis sem necessidade de autoriza&ccedil;&otilde;es externas.</p>
<p style="margin: 15px 0;">
	<a href="<?= htmlspecialchars($urlIcs); ?>" class="botao_exportar_agenda">Baixar Hor&aacute;rios do Per&iacute;odo Atual (.ics)</a>
	&nbsp;&nbsp;
	<a href="<?= CONFIG_URL; ?>planejador/">Ir para o Planejador</a>
</p>
<p>Para importar no Google Agenda, acesse <a href="https://calendar.google.com/calendar/u/0/r/settings/export" target="_blank" rel="noopener noreferrer">Importar e Exportar no Google Calendar</a> e envie o arquivo <code>.ics</code> baixado.</p>
<?= $FIM; ?>
