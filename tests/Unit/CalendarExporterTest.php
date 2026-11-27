<?php

namespace GDE\Tests\Unit;

use GDE\CalendarExporter;
use GDE\Dimensao;
use GDE\Disciplina;
use GDE\Oferecimento;
use GDE\Periodo;
use GDE\Planejado;
use GDE\PlanejadoExtra;
use GDE\Professor;
use GDE\Sala;
use PHPUnit\Framework\TestCase;

class CalendarExporterTest extends TestCase {

	private function criarOferecimento(string $sigla, string $nome, string $turma, array $slots, array $professores = array()): Oferecimento {
		$Disc = new Disciplina();
		$Disc->setSigla($sigla);
		$Disc->setNome($nome);

		$O = new Oferecimento();
		$O->setDisciplina($Disc);
		$O->setTurma($turma);

		$dimensoes = array();
		foreach ($slots as $slot) {
			list($dia, $hora, $salaNome) = $slot;
			$Sala = new Sala();
			$Sala->setNome($salaNome);

			$Dim = new Dimensao();
			$Dim->setDia($dia);
			$Dim->setHorario($hora);
			$Dim->setSala($Sala);
			$dimensoes[] = $Dim;
		}
		$O->setDimensoes($dimensoes);

		if (!empty($professores)) {
			$profs = array();
			foreach ($professores as $nomeProf) {
				$P = new Professor();
				$P->setNome($nomeProf);
				$profs[] = $P;
			}
			$O->setProfessores($profs);
		}

		return $O;
	}

	public function testEscapeIcsText() {
		$raw = "Sala CB01, Bloco A; Turma\\1 &amp; Teste\nLinha 2";
		$escaped = CalendarExporter::escapeIcsText($raw);
		$this->assertSame("Sala CB01\\, Bloco A\\; Turma\\\\1 & Teste\\nLinha 2", $escaped);
	}

	public function testFormatUtcDateTime() {
		$dt = new \DateTimeImmutable('2026-03-10 14:00:00', new \DateTimeZone('America/Sao_Paulo'));
		$this->assertSame('20260310T170000Z', CalendarExporter::formatUtcDateTime($dt));
	}

	public function testPrimeiroDiaAulaNaoMutaDataOriginal() {
		$exporter = new CalendarExporter();
		// 2026-03-02 e segunda-feira (diaAula=2)
		$inicio = new \DateTime('2026-03-02 00:00:00', new \DateTimeZone('America/Sao_Paulo'));
		$quarta = $exporter->primeiroDiaAula(4, $inicio);

		$this->assertSame('2026-03-02', $inicio->format('Y-m-d'));
		$this->assertSame('2026-03-04', $quarta->format('Y-m-d'));
	}

	public function testAgregaAulasConsecutivasEmEventoUnico() {
		$exporter = new CalendarExporter();
		$P = Periodo::Novo(2026, 1);
		$P->setData_Inicio_Aulas('2026-03-02');
		$P->setData_Fim_Aulas('2026-07-04');

		// MC851 A na segunda (2) das 14h as 16h (slots 14 e 15) na sala CC01
		$O = $this->criarOferecimento('MC851', 'Projeto em Computacao I', 'A', array(
			array(2, 14, 'CC01'),
			array(2, 15, 'CC01'),
		), array('Islene Garcia'));

		$Planejado = new Planejado();
		$Planejado->setPeriodo($P);
		$Planejado->setOferecimentos(array($O));

		$ics = $exporter->exportarPlanejado($Planejado);

		$this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
		$this->assertStringContainsString('END:VCALENDAR', $ics);
		$this->assertSame(1, substr_count($ics, 'BEGIN:VEVENT'));
		$this->assertStringContainsString('SUMMARY:MC851 A - Projeto em Computacao I', $ics);
		$this->assertStringContainsString('LOCATION:CC01', $ics);
		$this->assertStringContainsString('Professor(es): Islene Garcia', $ics);
		$this->assertStringContainsString('DTSTART;TZID=America/Sao_Paulo:20260302T140000', $ics);
		$this->assertStringContainsString('DTEND;TZID=America/Sao_Paulo:20260302T160000', $ics);
		$this->assertStringContainsString('RRULE:FREQ=WEEKLY;UNTIL=20260705T025959Z', $ics);
	}

	public function testExportarPlanejadoComExtrasECalendarioUnicamp() {
		$exporter = new CalendarExporter();
		$P = Periodo::Novo(2026, 1);
		$P->setData_Inicio_Aulas('2026-03-02');
		$P->setData_Fim_Aulas('2026-07-04');
		$P->setData_Exames_Inicio('2026-07-06');
		$P->setData_Exames_Fim('2026-07-11');

		$O = $this->criarOferecimento('MC102', 'Algoritmos', 'B', array(
			array(3, 10, 'PB12'),
		));

		$Planejado = new Planejado();
		$Planejado->setPeriodo($P);
		$Planejado->setOferecimentos(array($O));

		$Extra = PlanejadoExtra::Criar($Planejado, 'Monitoria IC', 5, '16:00:00', '18:00:00');
		$Planejado->setExtras(array($Extra));

		$ics = $exporter->exportarPlanejado($Planejado, array('incluir_calendario_unicamp' => true));

		// 1 aula + 1 extra + 1 marco (Exames) = 3 VEVENT
		$this->assertSame(3, substr_count($ics, 'BEGIN:VEVENT'));
		$this->assertStringContainsString('SUMMARY:Monitoria IC', $ics);
		$this->assertStringContainsString('DTSTART;TZID=America/Sao_Paulo:20260306T160000', $ics);
		$this->assertStringContainsString('DTEND;TZID=America/Sao_Paulo:20260306T180000', $ics);
		$this->assertStringContainsString('SUMMARY:[UNICAMP] Semana de exames', $ics);
		$this->assertStringContainsString('DTSTART;VALUE=DATE:20260706', $ics);
		$this->assertStringContainsString('DTEND;VALUE=DATE:20260712', $ics);
	}

	public function testFallbackQuandoPeriodoNaoTemDatasInicioEFim() {
		$exporter = new CalendarExporter();
		$P = Periodo::Novo(2026, 2); // 2o semestre sem datas preenchidas

		$O = $this->criarOferecimento('MC202', 'Estruturas de Dados', 'A', array(
			array(2, 8, 'CB02'),
		));

		$Planejado = new Planejado();
		$Planejado->setPeriodo($P);
		$Planejado->setOferecimentos(array($O));

		$ics = $exporter->exportarPlanejado($Planejado);
		// 2026-08-01 e sabado; primeira segunda-feira apos 01/08/2026 e 2026-08-03
		$this->assertStringContainsString('DTSTART;TZID=America/Sao_Paulo:20260803T080000', $ics);
		$this->assertStringContainsString('DTEND;TZID=America/Sao_Paulo:20260803T090000', $ics);
		$this->assertStringContainsString('RRULE:FREQ=WEEKLY;UNTIL=20261216T025959Z', $ics);
	}
}
