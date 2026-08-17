<?php

namespace GDE\Tests\Unit;

use GDE\Curso;
use GDE\Dimensao;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\OferecimentoReserva;
use GDE\Sala;
use GDE\Usuario;
use PHPUnit\Framework\TestCase;

class OferecimentoTest extends TestCase {

	// $dimensoes: lista de [dia, horario, nome_da_sala]
	private function oferecimento($id, $sigla, $turma, array $dimensoes) {
		$D = new Disciplina();
		$D->setId_Disciplina(99);
		$D->setSigla($sigla);

		$O = new Oferecimento();
		$O->setId_Oferecimento($id);
		$O->setDisciplina($D);
		$O->setTurma($turma);

		$lista = array();
		foreach($dimensoes as $dim) {
			$Sala = new Sala();
			$Sala->setNome($dim[2]);
			$Dim = new Dimensao();
			$Dim->setDia($dim[0]);
			$Dim->setHorario($dim[1]);
			$Dim->setSala($Sala);
			$lista[] = $Dim;
		}
		$O->setDimensoes($lista);
		return $O;
	}

	public function testMontaHorario() {
		$O = $this->oferecimento(77, 'MC102', 'A', array(
			array(2, 8, 'CB01'),
			array(2, 9, 'CB01'),
			array(4, 10, 'CB02'),
		));
		$this->assertSame(
			array(2 => array(8 => 'CB01', 9 => 'CB01'), 4 => array(10 => 'CB02')),
			$O->Monta_Horario()
		);
	}

	public function testEventosAgrupaHorasContiguasNaMesmaSala() {
		$O = $this->oferecimento(77, 'MC102', 'A', array(
			array(2, 8, 'CB01'),
			array(2, 9, 'CB01'),
		));
		$this->assertSame(array(
			array(
				'id' => 77,
				'title' => 'MC102 A CB01',
				'start' => '2003-12-01T08:00:00-03:00',
				'end' => '2003-12-01T10:00:00-03:00',
			),
		), $O->Eventos());
	}

	public function testEventosSeparaHorasNaoContiguas() {
		$O = $this->oferecimento(77, 'MC102', 'A', array(
			array(2, 8, 'CB01'),
			array(2, 10, 'CB01'),
		));
		$eventos = $O->Eventos();
		$this->assertCount(2, $eventos);
		$this->assertSame('2003-12-01T08:00:00-03:00', $eventos[0]['start']);
		$this->assertSame('2003-12-01T09:00:00-03:00', $eventos[0]['end']);
		$this->assertSame('2003-12-01T10:00:00-03:00', $eventos[1]['start']);
		$this->assertSame('2003-12-01T11:00:00-03:00', $eventos[1]['end']);
	}

	public function testEventosSeparaSalasDiferentesSemAgrupar() {
		$O = $this->oferecimento(77, 'MC102', 'A', array(
			array(2, 8, 'CB01'),
			array(2, 9, 'CB02'),
		));
		$eventos = $O->Eventos(false);
		$this->assertCount(2, $eventos);
		$this->assertSame('MC102 A CB01', $eventos[0]['title']);
		$this->assertSame('2003-12-01T08:00:00-03:00', $eventos[0]['start']);
		$this->assertSame('2003-12-01T09:00:00-03:00', $eventos[0]['end']);
		$this->assertSame('MC102 A CB02', $eventos[1]['title']);
		$this->assertSame('2003-12-01T09:00:00-03:00', $eventos[1]['start']);
		$this->assertSame('2003-12-01T10:00:00-03:00', $eventos[1]['end']);
	}

	public function testEventosAgrupandoJuntaSalasComBarra() {
		$O = $this->oferecimento(77, 'MC102', 'A', array(
			array(2, 8, 'CB01'),
			array(2, 9, 'CB02'),
		));
		$this->assertSame(array(
			array(
				'id' => 77,
				'title' => 'MC102 A CB01/CB02',
				'start' => '2003-12-01T08:00:00-03:00',
				'end' => '2003-12-01T10:00:00-03:00',
			),
		), $O->Eventos(true));
	}

	public function testFormataHorario() {
		$Horario = array(2 => array(8 => 'CB01', 9 => null, 10 => Oferecimento::SALA_DESCONHECIDA));
		$this->assertSame(
			'<a href="'.CONFIG_URL.'sala/CB01/">CB01</a>',
			Oferecimento::Formata_Horario($Horario, 2, 8)
		);
		$this->assertSame(Oferecimento::SALA_DESCONHECIDA, Oferecimento::Formata_Horario($Horario, 2, 9));
		$this->assertSame(Oferecimento::SALA_DESCONHECIDA, Oferecimento::Formata_Horario($Horario, 2, 10));
		$this->assertSame('-', Oferecimento::Formata_Horario($Horario, 2, 11));
		$this->assertSame('-', Oferecimento::Formata_Horario($Horario, 3, 8));
	}

	public function testListaHorarios() {
		$O = $this->oferecimento(77, 'MC102', 'A', array(
			array(2, 8, 'CB01'),
		));
		$this->assertSame(array(array(2, 8, 'CB01')), $O->Lista_Horarios());
		$this->assertSame(array('208'), $O->Lista_Horarios(true));
	}

	// ---- Viola_Reserva ----

	private function usuario($cursoId, $catalogo) {
		$U = new Usuario();
		if($cursoId !== null) {
			$C = new Curso();
			$C->setId_Curso($cursoId);
			$U->setCurso($C);
		}
		$U->setCatalogo($catalogo);
		return $U;
	}

	private function comReservas(Oferecimento $O, array $reservas) {
		$lista = array();
		foreach($reservas as $r) { // [cursoId|null, catalogo|null]
			$R = new OferecimentoReserva();
			if($r[0] !== null) {
				$C = new Curso();
				$C->setId_Curso($r[0]);
				$R->setCurso($C);
			}
			$R->setCatalogo($r[1]);
			$lista[] = $R;
		}
		$O->setReservas($lista);
		return $O;
	}

	public function testViolaReservaSemReservasNaoViola() {
		$O = $this->oferecimento(77, 'MC102', 'A', array());
		$this->assertFalse($O->Viola_Reserva($this->usuario(5, 2020)));
	}

	public function testViolaReservaAbertaNaoViola() {
		// Reserva sem curso e considerada aberta a todos
		$O = $this->comReservas($this->oferecimento(77, 'MC102', 'A', array()), array(array(null, null)));
		$this->assertFalse($O->Viola_Reserva($this->usuario(5, 2020)));
	}

	public function testViolaReservaDoMesmoCursoNaoViola() {
		$O = $this->comReservas($this->oferecimento(77, 'MC102', 'A', array()), array(array(5, null)));
		$this->assertFalse($O->Viola_Reserva($this->usuario(5, 2020)));

		$O = $this->comReservas($this->oferecimento(78, 'MC102', 'A', array()), array(array(5, 2020)));
		$this->assertFalse($O->Viola_Reserva($this->usuario(5, 2020)));
	}

	public function testViolaReservaDeOutroCurso() {
		$O = $this->comReservas($this->oferecimento(77, 'MC102', 'A', array()), array(array(9, null)));
		$this->assertTrue($O->Viola_Reserva($this->usuario(5, 2020)));
	}

	public function testViolaReservaDeOutroCatalogo() {
		$O = $this->comReservas($this->oferecimento(77, 'MC102', 'A', array()), array(array(5, 2015)));
		$this->assertTrue($O->Viola_Reserva($this->usuario(5, 2020)));
	}

	public function testEventosDiasDiferentesGeramEventosSeparados() {
		$O = $this->oferecimento(77, 'MC102', 'A', array(
			array(4, 8, 'CB01'),
			array(2, 8, 'CB01'),
		));
		$eventos = $O->Eventos();
		$this->assertCount(2, $eventos);
		// Dias sao ordenados; dia N vira 2003-12-0(N-1)
		$this->assertSame('2003-12-01T08:00:00-03:00', $eventos[0]['start']);
		$this->assertSame('2003-12-03T08:00:00-03:00', $eventos[1]['start']);
	}
}
