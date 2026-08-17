<?php

namespace GDE\Tests\Unit;

use GDE\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase {

	public function testRandomRespeitaTamanhoECaracteres() {
		$this->assertSame(10, strlen(Util::Random(10)));
		$this->assertSame('aaaaa', Util::Random(5, 'a'));
		$this->assertSame('', Util::Random(0));
	}

	public function testLimitaNaoAlteraTextoCurto() {
		$this->assertSame('abcdef', Util::Limita('abcdef', 10));
		$this->assertSame('abcdef', Util::Limita('abcdef', 6));
	}

	public function testLimitaCortaComReticencias() {
		$this->assertSame('abcde...', Util::Limita('abcdefghijk', 8));
	}

	public function testLimitaEhMultibyteSafe() {
		$this->assertSame('calças', Util::Limita('calças', 6));
	}

	public function testTruncar() {
		$this->assertNull(Util::Truncar(null, 5));
		$this->assertSame('abc', Util::Truncar('abc', 5));
		$this->assertSame('abcd', Util::Truncar('abcdef', 4));
		// Multibyte: corta em caracteres, nao em bytes
		$this->assertSame('ção', Util::Truncar('ção teste', 3));
	}

	public function testLimpaBuscaSubstituiPontuacaoPorEspaco() {
		$this->assertSame('cálculo I a b c', Util::Limpa_Busca('cálculo I - a.b/c'));
		$this->assertSame('com_underscore', Util::Limpa_Busca('com_underscore'));
	}

	public function testStringFTSPrefixaTermosLongos() {
		$this->assertSame('+fisica* +geral*', Util::String_FTS('fisica geral'));
	}

	public function testStringFTSNaoPrefixaTermosCurtos() {
		// Termos menores que CONFIG_FT_MIN_LENGTH (3) ficam sem operador
		$this->assertSame('ma', Util::String_FTS('ma'));
	}

	public function testStringFTSRemoveOperadoresBoolean() {
		$this->assertSame('+cmais*', Util::String_FTS('c+mais'));
	}

	public function testStringFTSRemoveEmoji() {
		$this->assertSame('+calculo* ', Util::String_FTS("calculo \u{1F600}"));
	}

	public function testStringFTSSemTermosValidosUsaPlaceholder() {
		$this->assertSame('+xxxsemresultadosxxx*', Util::String_FTS("\u{1F600}"));
	}

	public function testHorariosLivresComGradeVazia() {
		$this->assertSame(range(7, 22), Util::Horarios_Livres(array()));
	}

	public function testHorariosLivresExcluiHorasOcupadas() {
		$horario = array(2 => array(8 => array('x')), 7 => array(19 => array('y')));
		$livres = Util::Horarios_Livres($horario);
		$this->assertNotContains(8, $livres);
		$this->assertNotContains(19, $livres);
		$this->assertContains(7, $livres);
		$this->assertContains(22, $livres);
		$this->assertCount(14, $livres);
	}

	public function testValidarEmail() {
		$this->assertTrue(Util::Validar_Email('aluno@unicamp.br'));
		$this->assertTrue(Util::Validar_Email('a.b+c@dominio.info'));
		$this->assertFalse(Util::Validar_Email('sem-arroba.com'));
		$this->assertFalse(Util::Validar_Email('a@sem-tld'));
		$this->assertFalse(Util::Validar_Email('a b@dominio.com'));
		// TLDs com mais de 4 letras tambem sao validos
		$this->assertTrue(Util::Validar_Email('a@dominio.museum'));
		$this->assertTrue(Util::Validar_Email('a@loja.online'));
	}

	public function testDiaDaSemana() {
		$this->assertSame('Domingo', Util::Dia_Da_Semana(0));
		$this->assertSame('Quarta-feira', Util::Dia_Da_Semana(3));
		$this->assertSame('S&aacute;bado', Util::Dia_Da_Semana(6));
		$this->assertSame('', Util::Dia_Da_Semana(7));
		$this->assertSame('', Util::Dia_Da_Semana(-1));
	}

	public function testRemover4BytesChars() {
		$this->assertSame("a\u{FFFD}b", Util::Remover_4_Bytes_Chars("a\u{1F600}b"));
		$this->assertSame('sem emoji', Util::Remover_4_Bytes_Chars('sem emoji'));
	}

	public function testFixStringAuxNaoMexeEmTextoComLink() {
		$texto = 'veja <a href="x">'.str_repeat('a', 50).'</a>';
		$this->assertSame($texto, Util::Fix_String_Aux($texto));
	}

	public function testFixStringAuxNaoMexeEmPalavrasCurtas() {
		$this->assertSame('palavras normais aqui', Util::Fix_String_Aux('palavras normais aqui'));
	}

	public function testFixStringAuxQuebraPalavrasLongas() {
		$resultado = Util::Fix_String_Aux(str_repeat('a', 35));
		$this->assertSame(
			'<span>'.str_repeat('a', 30).'</span><wbr></wbr><span class="word break">'.str_repeat('a', 5).'</span>',
			$resultado
		);
	}
}
