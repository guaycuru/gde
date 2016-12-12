<?php

namespace GDE;

header("Content-Type: text/xml");

define('NO_CACHE', true);
define('NO_HTML', true);
define('NO_REDIRECT', true);
define('NO_DENIAL', true);

require_once('../common/common.inc.php');

if($_Usuario === false)
	die('<xml><deslogado>true</deslogado></xml>');

session_write_close();

$time_start = time();

$atualiza_acesso = (($time_start - $_Usuario->getUltimo_Acesso('U')) >= CONFIG_ONLINE_UPDATE);
$atualiza_chat = ((isset($_POST['ac'])) && ($_POST['ac'] == 1));
$atualiza_amigos = !$atualiza_chat;
$meu_status = (CONFIG_CHAT_ATIVO) ? $_Usuario->getChat_Status(false, true) : 'z';

if(isset($_POST['n']) && ($_POST['n'] == 0)) {
	$atualiza_acesso = true;
	$atualiza_amigos = true;
	$obter_chats_antigos = true;
} else
	$obter_chats_antigos = false;

if((($atualiza_chat) && ($obter_chats_antigos === false)) && ($meu_status != 'z')) {
	ob_implicit_flush(true); // Necessario para detectar o fim da conexao!
	ob_end_flush(); // Necessario para detectar o fim da conexao!
}

$atualiza_amigos = $atualiza_amigos && CONFIG_CHAT_ATIVO;

$atualiza_acesso_xml = "";
$atualiza_amigos_xml = "";

if($atualiza_acesso) {
	$_Usuario->setUltimo_Acesso();
	$_Usuario->Save(true);
	$atualiza_acesso_xml = "\t<usuarios_online>".Usuario::Conta_Online(false)."</usuarios_online>\n";
}

// ToDo
/*if($atualiza_amigos) {
	if(isset($_POST['n']) && ($_POST['n'] == 0)) {  // Carregar todos dados dos amigos
		$atualiza_amigos_xml .= "
			<amigo id=\"".$_Usuario->getID()."\" status=\"".$_Usuario->getChat_Status(false, false)."\" status_janela=\"c\">
				<nick><![CDATA[".$_Usuario->getApelido()."]]></nick>
				<nome><![CDATA[".$_Usuario->getNome_Completo(true)."]]></nome>
				<foto><![CDATA[".$_Usuario->getFoto(true, true)."]]></foto>
			</amigo>\n";
		$Amigos = $_Usuario->getAmigos();
		$status_janelas = ChatConversa::Listar_Status_Janelas($_Usuario->getID());
		if(count($Amigos) > 0) {
			foreach($Amigos as $Amigo) {
				$status_janela = (isset($status_janelas[$Amigo->getID_Amigo()])) ? $status_janelas[$Amigo->getID_Amigo()] : 'o';
				$atualiza_amigos_xml .= "
			<amigo id=\"".$Amigo->getID_Amigo()."\" status=\"".$Amigo->getAmigo()->getChat_Status(false, false)."\" status_janela=\"".$status_janela."\">
				<nick><![CDATA[".$Amigo->getApelido()."]]></nick>
				<nome><![CDATA[".$Amigo->getAmigo()->getNome_Completo(true)."]]></nome>
				<foto><![CDATA[".$Amigo->getAmigo()->getFoto(true, true)."]]></foto>
			</amigo>\n";
			}
		}	
	} else {  // Carregar apenas status dos amigos
		$amigos = Usuario_Amigo::Listar_Soh_Status($_Usuario, true);
		foreach($amigos as $amigo)
			$atualiza_amigos_xml .= "\t\t\t<amigo id=\"".$amigo['id_amigo']."\" status=\"".$amigo['status']."\" />\n";
	}
}*/

?>
<xml version="1.0" encoding="UTF-8">
<?php
if($atualiza_acesso)
	echo $atualiza_acesso_xml;

$xml_chat = "";
// ToDo
/*if(($atualiza_chat || $obter_chats_antigos) && ($meu_status != 'z')) {
	$meu_id = $_Usuario->getID();
	if($obter_chats_antigos === false) {
		$c = 0;
		$s = ($_Usuario->Amigos_Online() > 0) ? CONFIG_CHAT_SLEEPT : CONFIG_CHAT_SLEEPN;
		while(($c < CONFIG_TIMEOUT_CHAT) && (connection_status() == CONNECTION_NORMAL)) {
			$Lista = ChatConversa::Listar_Mensagens($_Usuario->getID(), $id_chat_minimo, $obter_chats_antigos, $my_session_id);
			if(count($Lista) > 0)
				break;
			$_GDE['DB']->Close();
			sleep($s);
			Conectar_DB($_GDE);
			$c += $s;
			echo "\n"; // Necessario para detectar o fim da conexao!
		}
	} else
		$Lista = ChatConversa::Listar_Mensagens($_Usuario->getID(), $id_chat_minimo, $obter_chats_antigos, $my_session_id);
	if(count($Lista) > 0) {
		$hoje = date('d/m/Y');
		foreach($Lista as $Mensagem) {
			if($meu_id == $Mensagem->getID_Usuario_Destino())
				$direcao = "r";  // Recebimento
			elseif($meu_id == $Mensagem->getID_Usuario_Origem())
				$direcao = "e";  // Envio
			if(($_Usuario->Amigo_ID($Mensagem->getID_Usuario_Destino()) === false) || ($_Usuario->Amigo_ID($Mensagem->getID_Usuario_Origem()) === false)) {
				$Excluido = new Usuario((($direcao == "e") ? $Mensagem->getID_Usuario_Destino() : $Mensagem->getID_Usuario_Origem()), $_GDE['DB']);
				$atualiza_amigos_xml .= "
			<amigo id=\"".$Excluido->getID()."\" status=\"".$Excluido->getChat_Status(false, false)."\" status_janela=\"".$status_janela."\">
				<nick><![CDATA[".$Excluido->getNome(true)."]]></nick>
				<nome><![CDATA[".$Excluido->getNome_Completo(true)."]]></nome>
				<foto><![CDATA[".$Excluido->getFoto(true, true)."]]></foto>
			</amigo>\n";
			}
			$formato = ($Mensagem->getData('d/m/Y') == $hoje) ? 'H:i:s' : 'd/m/Y H:i:s';
			$xml_chat .= "\t\t<mensagem id=\"".$Mensagem->getID()."\"";
			$xml_chat .= " id_usuario_origem=\"".$Mensagem->getID_Usuario_Origem()."\"";
			$xml_chat .= " id_usuario_destino=\"".$Mensagem->getID_Usuario_Destino()."\"";
			$xml_chat .= " data_envio=\"".$Mensagem->getData($formato)."\"";
			$xml_chat .= " id_chat=\"".$Mensagem->getID()."\"";
			$xml_chat .= " direcao=\"".$direcao."\">";
			$xml_chat .= "<![CDATA[".$Mensagem->getMensagem()."]]>";
			$xml_chat .= "</mensagem>\n";
		}
	}
}*/

if($atualiza_amigos) {
	echo "\t<amigos>\n";
	echo $atualiza_amigos_xml."\n";
	echo "\t</amigos>\n";
}

if(strlen($xml_chat) > 0) {
	echo "\t<chat>\n";
	echo $xml_chat."\n";
	echo "\t</chat>\n";
}

if($atualiza_chat === false) {
	$chat_status = (CONFIG_CHAT_ATIVO) ? $_Usuario->getChat_Status(false, true) : 'z';
?>
	<chat_status><?= $chat_status; ?></chat_status>
<?php } ?>
</xml>
