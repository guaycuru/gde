<?php

namespace GDE\Tests\Unit;

use GDE\Dimensao;
use GDE\Disciplina;
use GDE\Equivalencia;
use GDE\Oferecimento;
use GDE\Periodo;
use GDE\Sala;
use GDE\Usuario;
use GDE\UsuarioEliminada;
use PHPUnit\Framework\TestCase;

/**
 * Testes dos getters/setters magicos de GDE\Base::__call, usando entidades
 * reais como veiculo
 */
class BaseTest extends TestCase {

	// ---- Campos date/datetime ----

	public function testSetDataDetectaFormatoBrasileiro() {
		$P = Periodo::Novo(2024, 1);
		$P->setData_Inicio_Aulas('01/03/2024');
		$this->assertSame('2024-03-01', $P->getData_Inicio_Aulas('Y-m-d'));
		$this->assertSame('00:00:00', $P->getData_Inicio_Aulas('H:i:s'));
	}

	public function testSetDataDetectaFormatoISO() {
		$P = Periodo::Novo(2024, 1);
		$P->setData_Inicio_Aulas('2024-03-01');
		$this->assertSame('01/03/2024', $P->getData_Inicio_Aulas('d/m/Y'));
	}

	public function testSetDataComFormatoCustomizado() {
		$P = Periodo::Novo(2024, 1);
		$P->setData_Inicio_Aulas('03-01-2024', 'm-d-Y');
		$this->assertSame('2024-03-01', $P->getData_Inicio_Aulas('Y-m-d'));
	}

	public function testSetDataInvalidaViraNull() {
		$P = Periodo::Novo(2024, 1);
		$P->setData_Inicio_Aulas('data invalida');
		$this->assertNull($P->getData_Inicio_Aulas());
	}

	public function testSetDataAceitaObjetoDateTime() {
		$P = Periodo::Novo(2024, 1);
		$P->setData_Inicio_Aulas(new \DateTime('2024-03-01'));
		$this->assertSame('2024-03-01', $P->getData_Inicio_Aulas('Y-m-d'));
	}

	public function testGetDataSemFormatoRetornaDateTime() {
		$P = Periodo::Novo(2024, 1);
		$P->setData_Inicio_Aulas('2024-03-01');
		$this->assertInstanceOf(\DateTime::class, $P->getData_Inicio_Aulas(false));
	}

	public function testSetDatetimeEGetTimestamp() {
		$U = new Usuario();
		$U->setUltimo_Acesso('2024-03-01 12:00:00');
		$this->assertSame(strval(strtotime('2024-03-01 12:00:00')), $U->getUltimo_Acesso('U'));
	}

	public function testGetDatetimeComTimezoneDeExibicao() {
		// America/Sao_Paulo = UTC-3 (sem horario de verao desde 2019)
		$U = new Usuario();
		$U->setUltimo_Acesso('2024-03-01 12:00:00');
		$this->assertSame('15:00', $U->getUltimo_Acesso('H:i', 'UTC'));
	}

	public function testSetDatetimeSemValorUsaAgora() {
		$U = new Usuario();
		$U->setData_Cadastro();
		$data = $U->getData_Cadastro(false);
		$this->assertInstanceOf(\DateTime::class, $data);
		$this->assertLessThan(5, abs(time() - intval($data->format('U'))));
	}

	// ---- Campos escalares ----

	public function testSetStringVaziaViraNull() {
		$Sala = new Sala();
		$Sala->setNome('');
		$this->assertNull($Sala->getNome());
	}

	public function testGetComHtmlEscapaCaracteresEspeciais() {
		$Sala = new Sala();
		$Sala->setNome('CB01 & "CB02"');
		$this->assertSame('CB01 & "CB02"', $Sala->getNome());
		$this->assertSame('CB01 &amp; &quot;CB02&quot;', $Sala->getNome(true));
	}

	public function testSetSemValorEmCampoStringLancaExcecao() {
		$Sala = new Sala();
		$this->expectException(\InvalidArgumentException::class);
		$Sala->setNome();
	}

	public function testGetPropriedadeInexistenteLancaExcecao() {
		$Sala = new Sala();
		$this->expectException(\InvalidArgumentException::class);
		$Sala->getPropriedade_Que_Nao_Existe();
	}

	// ---- Associacoes: add / remove / has / clear ----

	public function testAddManyToManySincronizaOsDoisLados() {
		$O = new Oferecimento();
		$O->setId_Oferecimento(7);
		$Dim = new Dimensao();
		$Dim->setId_Dimensao(5);

		$O->addDimensoes($Dim);
		$this->assertTrue($O->getDimensoes()->contains($Dim));
		$this->assertTrue($Dim->getOferecimentos()->contains($O));
	}

	public function testHasEncontraPorObjetoEPorId() {
		$O = new Oferecimento();
		$O->setId_Oferecimento(7);
		$Dim = new Dimensao();
		$Dim->setId_Dimensao(5);
		$O->addDimensoes($Dim);

		$this->assertTrue($O->hasDimensoes($Dim));
		$this->assertTrue($O->hasDimensoes(5));
		$this->assertFalse($O->hasDimensoes(99));
	}

	public function testRemoveManyToManySincronizaOsDoisLados() {
		$O = new Oferecimento();
		$O->setId_Oferecimento(7);
		$Dim = new Dimensao();
		$Dim->setId_Dimensao(5);
		$O->addDimensoes($Dim);

		$this->assertTrue($O->removeDimensoes($Dim));
		$this->assertTrue($O->getDimensoes()->isEmpty());
		$this->assertFalse($Dim->getOferecimentos()->contains($O));
	}

	public function testClearManyToManyLimpaOsDoisLados() {
		$O = new Oferecimento();
		$O->setId_Oferecimento(7);
		$DimA = new Dimensao();
		$DimA->setId_Dimensao(5);
		$DimB = new Dimensao();
		$DimB->setId_Dimensao(6);
		$O->addDimensoes($DimA);
		$O->addDimensoes($DimB);

		$O->clearDimensoes();
		$this->assertTrue($O->getDimensoes()->isEmpty());
		$this->assertFalse($DimA->getOferecimentos()->contains($O));
		$this->assertFalse($DimB->getOferecimentos()->contains($O));
	}

	public function testAddOneToManyDefineOLadoInverso() {
		$D = new Disciplina();
		$Eq = new Equivalencia();
		$D->addEquivalencias($Eq);

		$this->assertTrue($D->getEquivalencias()->contains($Eq));
		$this->assertSame($D, $Eq->getDisciplina());
	}

	public function testAddEmAssociacaoToOneLancaExcecao() {
		$UE = new UsuarioEliminada();
		$this->expectException(\InvalidArgumentException::class);
		$UE->addDisciplina(new Disciplina());
	}
}
