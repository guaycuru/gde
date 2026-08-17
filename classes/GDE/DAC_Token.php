<?php

namespace GDE;

/**
 * DAC_Token
 *
 * Algoritmo de geracao e validacao dos tokens emitidos pela DAC.
 *
 * O segredo compartilhado com a DAC fica em CONFIG_DAC_TOKEN_SALT (nunca
 * hardcoded aqui): quem tiver o salt forja um token valido para qualquer
 * matricula. Sem o salt configurado, Gerar() devolve null e Validar()
 * recusa tudo - falha fechada de proposito, para que um deploy com a
 * variavel faltando derrube o login da DAC em vez de aceitar tokens
 * calculados com salt vazio (que qualquer um reproduziria a partir deste
 * arquivo, que e publico).
 */
class DAC_Token {
	/**
	 * @param $time
	 * @param $matricula
	 * @param $tipo
	 * @param bool|false $completo
	 * @return string|null
	 */
	public static function Gerar($time, $matricula, $tipo, $completo = false) {
		if((!defined('CONFIG_DAC_TOKEN_SALT')) || (CONFIG_DAC_TOKEN_SALT === ''))
			return null;
		$tipo = substr((string)$tipo, 0, 1);
		$num = bcpowmod($matricula * 16, 3, 38161203) + $time;
		return sha1(CONFIG_DAC_TOKEN_SALT.$num.'#'.$tipo.'&').(($completo) ? sprintf("%010d", intval($time)).sprintf("%010d", intval($matricula)).$tipo : null);
	}

	/**
	 * Valida o token fornecido pela DAC
	 *
	 * @param string $token O token fornecido pela DAC
	 * @return array
	 */
	public static function Validar($token) {
		if((!is_string($token)) || (strlen($token) < 61))
			return array(false, 0, 0);
		$auth = substr($token, 0, 40);
		$time = intval(substr($token, 40, 10));
		$matricula = intval(substr($token, 50, 10));
		$tipo = $token[60];
		$valido = (time() - $time <= DAC::VALIDADE_TOKEN);
		$esperado = self::Gerar($time, $matricula, $tipo, false);
		// $esperado === null significa CONFIG_DAC_TOKEN_SALT ausente: recusa
		// explicitamente, sem cair na comparacao (null == '' e true em PHP)
		if(($esperado === null) || (!$valido) || (!hash_equals($esperado, strtolower($auth))))
			return array(false, 0, 0);
		return array(true, $matricula, $tipo);
	}
}
