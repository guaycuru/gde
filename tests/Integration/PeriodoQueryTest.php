<?php

namespace GDE\Tests\Integration;

use GDE\Base;
use GDE\Periodo;

class PeriodoQueryTest extends IntegrationTestCase {

	private function seed(...$ids) {
		foreach($ids as $id) {
			$P = Periodo::Novo(substr($id, 0, 4), substr($id, 4, 1));
			$P->Save(false);
		}
		Base::_EM_Flush();
	}

	public function testAnteriorEProximo() {
		$this->seed('20951', '20952', '20961');
		$P = Periodo::Load('20952');
		$this->assertSame('20951', $P->Anterior()->getId());
		$this->assertSame('20961', $P->Proximo()->getId());
	}

	public function testAnteriorSemResultadoRetornaNull() {
		$this->seed('20951');
		$this->assertNull(Periodo::Load('20951')->Anterior());
	}

	public function testGetAtual() {
		$this->seed('20951', '20952');
		$this->assertNull(Periodo::getAtual());

		$P = Periodo::Load('20952');
		$P->setTipo(Periodo::TIPO_ATUAL);
		$P->Save();
		$this->assertSame('20952', Periodo::getAtual()->getId());
	}

	public function testListarOrdenaDoMaisRecente() {
		$this->seed('20951', '20961', '20952');
		$ids = array_map(function($P) {
			return $P->getId();
		}, Periodo::Listar());
		$this->assertSame(array('20961', '20952', '20951'), $ids);
	}

	public function testAdicionarSemestresCarregaOPeriodoCerto() {
		$this->seed('20952', '20961');
		$Novo = Periodo::Load('20952')->Adicionar_Semestres(1);
		$this->assertSame('20961', $Novo->getId());
	}
}
