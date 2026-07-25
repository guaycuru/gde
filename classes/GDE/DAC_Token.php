<?php

namespace GDE;

/**
 * DAC_Token
 *
 * O algoritmo de geracao/validacao de token da DAC nao esta disponivel
 * nesta copia publica do repositorio.
 */
class DAC_Token {
	public static function Gerar($time, $matricula, $tipo, $completo = false) {
		return null;
	}

	public static function Validar($token) {
		return array(false, 0, 0);
	}
}
