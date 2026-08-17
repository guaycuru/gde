<?php

namespace GDE\Tests\Unit;

use GDE\Planejado;
use GDE\PlanejadoExtra;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlanejadoExtraTest extends TestCase {

	private function novoPlanejado($id) {
		$P = new Planejado();
		$P->setId_Planejado($id);
		return $P;
	}

	private function novoExtra($dia, $inicio, $fim) {
		$PE = new PlanejadoExtra();
		$PE->setNome('Estudo');
		$PE->setDia($dia);
		$PE->setInicio($inicio);
		$PE->setFim($fim);
		return $PE;
	}

	public function testMoverDiasEMinutos() {
		$PE = $this->novoExtra(2, '08:00:00', '10:00:00');
		$PE->Mover(1, 30, true);
		$this->assertSame(3, $PE->getDia());
		$this->assertSame('08:30:00', $PE->getInicio('H:i:s'));
		$this->assertSame('10:30:00', $PE->getFim('H:i:s'));
	}

	public function testMoverMaisDeUmaHora() {
		$PE = $this->novoExtra(2, '08:00:00', '10:00:00');
		$PE->Mover(0, 90, true);
		$this->assertSame('09:30:00', $PE->getInicio('H:i:s'));
		$this->assertSame('11:30:00', $PE->getFim('H:i:s'));
	}

	public function testMoverSomenteFim() {
		$PE = $this->novoExtra(2, '08:00:00', '10:00:00');
		$PE->Mover(0, 60, false);
		$this->assertSame('08:00:00', $PE->getInicio('H:i:s'));
		$this->assertSame('11:00:00', $PE->getFim('H:i:s'));
	}

	public function testMoverNaoPassaDas7ParaTras() {
		$PE = $this->novoExtra(2, '07:00:00', '08:00:00');
		$PE->Mover(0, -60, true);
		$this->assertSame('07:00:00', $PE->getInicio('H:i:s'));
		$this->assertSame('07:00:00', $PE->getFim('H:i:s'));
	}

	public function testMoverComEstouroDeMinutos() {
		$PE = $this->novoExtra(2, '08:30:00', '10:30:00');
		$PE->Mover(0, 45, true);
		$this->assertSame('09:15:00', $PE->getInicio('H:i:s'));
		$this->assertSame('11:15:00', $PE->getFim('H:i:s'));
	}

	public function testMoverParaTrasComResto() {
		$PE = $this->novoExtra(2, '09:15:00', '11:15:00');
		$PE->Mover(0, -30, true);
		$this->assertSame('08:45:00', $PE->getInicio('H:i:s'));
		$this->assertSame('10:45:00', $PE->getFim('H:i:s'));
	}

	public function testMoverParaTrasClampMantemMinutos() {
		$PE = $this->novoExtra(2, '08:15:00', '10:15:00');
		$PE->Mover(0, -120, true);
		$this->assertSame('07:15:00', $PE->getInicio('H:i:s'));
		$this->assertSame('08:15:00', $PE->getFim('H:i:s'));
	}

	public function testEvento() {
		$PE = $this->novoExtra(3, '08:00:00', '10:00:00');
		$evento = $PE->Evento('#604F99');
		$this->assertSame('Estudo', $evento['title']);
		$this->assertSame('2003-12-03T08:00:00-03:00', $evento['start']);
		$this->assertSame('2003-12-03T10:00:00-03:00', $evento['end']);
		$this->assertSame('#604F99', $evento['color']);
		$this->assertTrue($evento['editable']);
	}

	public function testEventoSemCorNaoDefineCor() {
		$PE = $this->novoExtra(3, '08:00:00', '10:00:00');
		$this->assertArrayNotHasKey('color', $PE->Evento());
	}

	public function testCriarComDadosValidos() {
		$P = $this->novoPlanejado(7);
		$erros = null;
		$Extra = PlanejadoExtra::Criar($P, 'Academia', 3, '08:00:00', '09:30:00', $erros);

		$this->assertSame(array(), $erros);
		$this->assertSame('Academia', $Extra->getNome());
		$this->assertSame(3, $Extra->getDia());
		$this->assertSame('08:00:00', $Extra->getInicio('H:i:s'));
		$this->assertSame('09:30:00', $Extra->getFim('H:i:s'));
		$this->assertSame($P, $Extra->getPlanejado(false));
	}

	public function testCriarAceitaHoraComUmDigito() {
		$erros = null;
		PlanejadoExtra::Criar($this->novoPlanejado(1), 'Almoco', 1, '8:00:00', '9:00:00', $erros);
		$this->assertSame(array(), $erros);
	}

	#[DataProvider('diasInvalidos')]
	public function testCriarRecusaDiaForaDaSemana($dia) {
		$erros = null;
		PlanejadoExtra::Criar($this->novoPlanejado(1), 'Academia', $dia, '08:00:00', '09:00:00', $erros);
		$this->assertSame(array('O dia informado &eacute; inv&aacute;lido.'), $erros);
	}

	public static function diasInvalidos() {
		return array(array(0), array(-1), array(8));
	}

	public function testCriarRecusaNomeCurto() {
		$erros = null;
		PlanejadoExtra::Criar($this->novoPlanejado(1), 'A', 3, '08:00:00', '09:00:00', $erros);
		$this->assertSame(array('O Nome informado &eacute; inv&aacute;lido.'), $erros);
	}

	public function testCriarRecusaHorariosInvalidos() {
		$erros = null;
		PlanejadoExtra::Criar($this->novoPlanejado(1), 'Academia', 3, '8h', '25:00', $erros);
		$this->assertSame(array(
			'O in&iacute;cio informado &eacute; inv&aacute;lido.',
			'O fim informado &eacute; inv&aacute;lido.'
		), $erros);
	}

	public function testCriarAcumulaTodosOsErros() {
		$erros = null;
		PlanejadoExtra::Criar($this->novoPlanejado(1), '', 9, 'x', 'y', $erros);
		$this->assertCount(4, $erros);
	}

	// O objeto e criado mesmo com dados invalidos - cabe a quem chama olhar $erros
	public function testCriarRetornaObjetoMesmoComErros() {
		$erros = null;
		$Extra = PlanejadoExtra::Criar($this->novoPlanejado(1), 'A', 9, 'x', 'y', $erros);
		$this->assertInstanceOf(PlanejadoExtra::class, $Extra);
		$this->assertSame('A', $Extra->getNome());
		$this->assertNull($Extra->getInicio());
	}

	public function testPertenceAoProprioPlanejado() {
		$P = $this->novoPlanejado(7);
		$Extra = $this->novoExtra(3, '08:00:00', '10:00:00');
		$Extra->setId_Planejado_Extra(42);
		$Extra->setPlanejado($P);

		$this->assertTrue($Extra->Pertence_A($P));
	}

	public function testNaoPertenceAOutroPlanejado() {
		$Extra = $this->novoExtra(3, '08:00:00', '10:00:00');
		$Extra->setId_Planejado_Extra(42);
		$Extra->setPlanejado($this->novoPlanejado(7));

		$this->assertFalse($Extra->Pertence_A($this->novoPlanejado(8)));
	}

	// Load() devolve um objeto vazio quando nao encontra: id nulo => nao pertence
	public function testExtraInexistenteNaoPertence() {
		$Extra = new PlanejadoExtra();
		$this->assertFalse($Extra->Pertence_A($this->novoPlanejado(7)));
	}

	public function testExtraSemPlanejadoNaoPertence() {
		$Extra = $this->novoExtra(3, '08:00:00', '10:00:00');
		$Extra->setId_Planejado_Extra(42);
		$this->assertFalse($Extra->Pertence_A($this->novoPlanejado(7)));
	}
}
