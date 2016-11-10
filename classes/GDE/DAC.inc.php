<?php

namespace GDE;

class DAC {

	/**
	 * Validar_Token_DAC
	 *
	 * Valida o token fornecido pela DAC
	 *
	 * @param string $token O token fornecido pela DAC
	 * @param boolean $verificar_horario (Opcional) Se for false, nao ira verificar o horario do token (nao recomendado)
	 * @return array
	 */
	public static function Validar_Token($token, $verificar_horario = true) {
		// Retorna sempre false
		return array(false, 0, 0);
	}
}
