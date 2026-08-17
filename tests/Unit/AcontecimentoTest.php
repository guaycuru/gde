<?php

namespace GDE\Tests\Unit;

use GDE\Acontecimento;
use PHPUnit\Framework\TestCase;

class AcontecimentoTest extends TestCase {

	public function testTrataURLComHttpViraLink() {
		$this->assertSame(
			'<a href="http://example.com" target="_blank">http://example.com</a>',
			Acontecimento::TrataURL('http://example.com')
		);
	}

	public function testTrataURLSemProtocoloGanhaHttp() {
		$this->assertSame(
			'<a href="http://example.com" target="_blank">example.com</a>',
			Acontecimento::TrataURL('example.com')
		);
	}

	public function testTrataURLEmailViraMailto() {
		$this->assertSame(
			'<a href="mailto:aluno@unicamp.br">aluno@unicamp.br</a>',
			Acontecimento::TrataURL('aluno@unicamp.br')
		);
	}

	public function testTrataURLYoutubeViraThumbnail() {
		$resultado = Acontecimento::TrataURL('http://www.youtube.com/watch?v=dQw4w9WgXcQ');
		$this->assertStringContainsString('id="youtube_dQw4w9WgXcQ"', $resultado);
		$this->assertStringContainsString('class="video_youtube"', $resultado);
		$this->assertStringContainsString(CONFIG_URL.'web/images/play_video.png', $resultado);
	}

	public function testTrataURLYoutubeCurtoViraThumbnail() {
		$resultado = Acontecimento::TrataURL('http://youtu.be/dQw4w9WgXcQ');
		$this->assertStringContainsString('id="youtube_dQw4w9WgXcQ"', $resultado);
	}

	public function testTrataURLComArrayUsaPrimeiroElemento() {
		$this->assertSame(
			'<a href="http://example.com" target="_blank">http://example.com</a>',
			Acontecimento::TrataURL(array('http://example.com'))
		);
	}

	public function testProcessarLinkificaURLsNoTexto() {
		$resultado = Acontecimento::Processar('veja http://example.com hoje');
		$this->assertStringContainsString('<a href="http://example.com" target="_blank">http://example.com</a>', $resultado);
		$this->assertStringStartsWith('veja ', $resultado);
		$this->assertStringEndsWith(' hoje', $resultado);
	}

	public function testProcessarNaoAlteraTextoSemURL() {
		$this->assertSame('texto simples sem nada', Acontecimento::Processar('texto simples sem nada'));
	}

	public function testTrataURLHttpsViraLink() {
		$this->assertSame(
			'<a href="https://example.com" target="_blank">https://example.com</a>',
			Acontecimento::TrataURL('https://example.com')
		);
	}

	public function testTrataURLYoutubeHttpsViraThumbnail() {
		$resultado = Acontecimento::TrataURL('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
		$this->assertStringContainsString('id="youtube_dQw4w9WgXcQ"', $resultado);
		$this->assertStringNotContainsString('https://', $resultado);
	}

	public function testProcessarLinkificaURLHttpsNoTexto() {
		$resultado = Acontecimento::Processar('veja https://example.com hoje');
		$this->assertStringContainsString('<a href="https://example.com" target="_blank">https://example.com</a>', $resultado);
	}

	public function testProcessarLinkificaURLComCaminhoEQuery() {
		$resultado = Acontecimento::Processar('veja http://example.com/pasta?x=1 hoje');
		$this->assertStringContainsString('<a href="http://example.com/pasta?x=1" target="_blank">', $resultado);
	}

	public function testProcessarLinkificaEmailNoTexto() {
		$resultado = Acontecimento::Processar('escreva para aluno@unicamp.br hoje');
		$this->assertStringContainsString('<a href="mailto:aluno@unicamp.br">aluno@unicamp.br</a>', $resultado);
	}

	public function testProcessarLinkificaDominioComWWW() {
		$resultado = Acontecimento::Processar('acesse www.example.com agora');
		$this->assertStringContainsString('<a href="http://www.example.com" target="_blank">www.example.com</a>', $resultado);
	}
}
