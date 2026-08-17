<?php

namespace GDE\Tests\Unit;

use GDE\Periodo;
use PHPUnit\Framework\TestCase;

class PeriodoTest extends TestCase {

	public function testNovoSemestre() {
		$P = Periodo::Novo(2024, 1);
		$this->assertSame('20241', $P->getPeriodo(false));
		$this->assertSame(2024, $P->getAno());
		$this->assertSame(1, $P->getSemestre());
		$this->assertSame('2024 - 1.&ordm; Semestre', $P->getNome());
	}

	public function testNovoFeriasDeVerao() {
		$P = Periodo::Novo(2024, Periodo::PERIODO_FERIAS_VERAO);
		$this->assertSame('20240', $P->getPeriodo(false));
		$this->assertSame(0, $P->getSemestre());
		$this->assertSame('2024 - F&eacute;rias de Ver&atilde;o', $P->getNome());
	}

	public function testNomeDAC() {
		$this->assertSame('1S2024', Periodo::Novo(2024, 1)->getNome_DAC());
		$this->assertSame('2S2023', Periodo::Novo(2023, 2)->getNome_DAC());
		$this->assertSame('VE2024', Periodo::Novo(2024, 0)->getNome_DAC());
	}

	public function testNomeDACDesconhecidoSemId() {
		$P = new Periodo();
		$this->assertSame(Periodo::PERIODO_DESCONHECIDO_DAC, $P->getNome_DAC());
	}

	public function testAnteriorA() {
		$this->assertTrue(Periodo::Novo(2023, 2)->Anterior_A(Periodo::Novo(2024, 1)));
		$this->assertTrue(Periodo::Novo(2024, 1)->Anterior_A(Periodo::Novo(2024, 2)));
		$this->assertFalse(Periodo::Novo(2024, 1)->Anterior_A(Periodo::Novo(2024, 1)));
		$this->assertFalse(Periodo::Novo(2024, 2)->Anterior_A(Periodo::Novo(2024, 1)));
	}

	public function testPosteriorA() {
		$this->assertTrue(Periodo::Novo(2024, 2)->Posterior_A(Periodo::Novo(2024, 1)));
		$this->assertTrue(Periodo::Novo(2025, 0)->Posterior_A(Periodo::Novo(2024, 2)));
		$this->assertFalse(Periodo::Novo(2024, 1)->Posterior_A(Periodo::Novo(2024, 1)));
		$this->assertFalse(Periodo::Novo(2023, 2)->Posterior_A(Periodo::Novo(2024, 1)));
	}

	public function testSomaSemestresDentroDoAno() {
		$this->assertSame('20242', Periodo::Novo(2024, 1)->Soma_Semestres(1));
		$this->assertSame('20241', Periodo::Novo(2024, 1)->Soma_Semestres(0));
	}

	public function testSomaSemestresViraOAno() {
		$this->assertSame('20251', Periodo::Novo(2024, 2)->Soma_Semestres(1));
		$this->assertSame('20252', Periodo::Novo(2024, 2)->Soma_Semestres(2));
		$this->assertSame('20261', Periodo::Novo(2024, 2)->Soma_Semestres(3));
		$this->assertSame('20251', Periodo::Novo(2023, 2)->Soma_Semestres(3));
	}

	public function testSomaSemestresNegativo() {
		$this->assertSame('20232', Periodo::Novo(2024, 1)->Soma_Semestres(-1));
		$this->assertSame('20241', Periodo::Novo(2024, 2)->Soma_Semestres(-1));
	}

	public function testSomaSemestresDeFeriasContaComoPrimeiroSemestre() {
		$this->assertSame('20241', Periodo::Novo(2024, 0)->Soma_Semestres(0));
		$this->assertSame('20242', Periodo::Novo(2024, 0)->Soma_Semestres(1));
	}

	public function testTemInicioEFim() {
		$P = Periodo::Novo(2024, 1);
		$this->assertFalse($P->Tem_Inicio_E_Fim());
		$P->setData_Inicio_Aulas('2024-03-01');
		$this->assertFalse($P->Tem_Inicio_E_Fim());
		$P->setData_Fim_Aulas('2024-07-01');
		$this->assertTrue($P->Tem_Inicio_E_Fim());
	}

	public function testTemCalendario() {
		$P = Periodo::Novo(2024, 1);
		$this->assertFalse($P->Tem_Calendario());
		$P->setData_Matricula_Inicio('2024-01-10');
		$this->assertTrue($P->Tem_Calendario());
	}
}
