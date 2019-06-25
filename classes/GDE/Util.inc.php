<?php

namespace GDE;

class Util {
	/**
	 * Random
	 *
	 * Generates a random string
	 *
	 * @param integer $size The size of the random string
	 * @param string $chars Chars to be used
	 * @return string The random string
	 */
	public static function Random($size, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
		$l = strlen($chars) - 1;
		$r = '';
		while($size-- > 0)
			$r .= $chars[mt_rand(0, $l)];
		return $r;
	}

	public static function Limita($texto, $tamanho) {
		return (mb_strlen($texto) <= $tamanho) ? $texto : mb_substr($texto, 0, $tamanho-3).'...';
	}

	public static function Limpa_Busca($str) {
		//return str_replace(array("\\", "/", "'", "%", "#", "\$", "@", "+", "-", ".", "~"), null, $str);
		return preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $str);
	}

	public static function Fix_String_Aux($largeString){
		// Se tem algum link na mensagem, nao quebra nenhuma parte dela, senao ele quebra o texto do link...
		if(strpos($largeString, '<a href=') !== false)
			return $largeString;
		$maxWordSize = 30;
		$words = explode(" ", $largeString);
		$hasBigWords = false;
		foreach($words as $i => $curWord) {
			//if((substr($curWord, 0, '5') == 'href=') && (substr($words[$i-1], -2) == '<a'))
				//continue
			$wordSize = strlen($curWord);
			if($wordSize <= $maxWordSize)
				continue;
			$hasBigWords = true;
			$nWords = floor($wordSize/$maxWordSize) + 1;
			if($wordSize % $maxWordSize == 0)
				$nWords--;
			if($nWords > 0)
				$newString = '<span>'.substr($curWord,0,$maxWordSize).'</span>';
			else
				$newString = "";
			for($j = 1; $j < $nWords; $j++)
				$newString .= '<wbr></wbr><span class="word break">'.substr($curWord, ($j*$maxWordSize), $maxWordSize).'</span>';
			$words[$i] = $newString;
		}
		if(!$hasBigWords)
			return $largeString;
		return implode(" ", $words);
	}

	public static function Horarios_Livres($Horario) {
		$limpos = array();
		for($j = 7; $j < 23; $j++) {
			$conta = 0;
			for($i = 2; $i < 8; $i++) {
				if((array_key_exists($i, $Horario)) && (array_key_exists($j, $Horario[$i])))
					$conta++;
			}
			if($conta == 0)
				$limpos[] = $j;
		}
		return $limpos;
	}

	public static function Enviar_Email($para, $assunto, $msg, $from = 'GDE <gde@guaycuru.net>', $html = false) {
		$html_header = ($html) ? 'MIME-Version: 1.0'."\r\n".'Content-type: text/html; charset=utf-8'."\r\n" : '';
		return @mail(
			$para,
			$assunto,
			$msg,
			$html_header.'From: '. $from . "\r\n" .'Reply-To: ' . $from . "\r\n" . 'X-Mailer: PHP/' . phpversion()
		);
	}

	public static function Validar_Email($email) {
		return (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email) > 0);
	}

	public static function Dia_Da_Semana($d) {
		if($d < 0 || $d > 6)
			return '';
		$dias = array('Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'S&aacute;bado');
		return $dias[$d];
	}

	public static function Remover_4_Bytes_Chars($string) {
		return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $string);
	}

}
