<?php

namespace GDE;

/**
 * CalendarExporter
 *
 * Gera arquivos e feeds no padrao universal iCalendar (RFC 5545)
 * para importacao no Google Calendar, Apple Calendar, Outlook e afins,
 * sem depender de credenciais OAuth da API do Google.
 */
class CalendarExporter {
	const PRODID = '-//GDE Unicamp//Planejador Academico//PT';
	const TIMEZONE = 'America/Sao_Paulo';

	/**
	 * Exporta a grade e atividades extras de um Planejado especifico para .ics
	 *
	 * @param Planejado $Planejado
	 * @param array $opcoes ['incluir_calendario_unicamp' => bool]
	 * @return string
	 */
	public function exportarPlanejado(Planejado $Planejado, array $opcoes = array()): string {
		$Periodo = $Planejado->getPeriodo(false);
		if ($Periodo === null) {
			$Periodo = Periodo::getAtual();
		}
		$Horario = $Planejado->Monta_Horario();
		$Extras = array();
		$extrasCol = $Planejado->getExtras();
		if ($extrasCol !== null) {
			foreach ($extrasCol as $Extra) {
				$Extras[] = $Extra;
			}
		}
		return $this->gerarIcsDeHorario($Horario, $Periodo, $opcoes, $Extras);
	}

	/**
	 * Exporta o horario de um Aluno em um Periodo para .ics
	 *
	 * @param Aluno $Aluno
	 * @param Periodo $Periodo
	 * @param array $opcoes ['incluir_calendario_unicamp' => bool, 'nivel' => 'G']
	 * @return string
	 */
	public function exportarHorarioAluno(Aluno $Aluno, Periodo $Periodo, array $opcoes = array()): string {
		$nivel = isset($opcoes['nivel']) ? $opcoes['nivel'] : Disciplina::NIVEL_GRAD;
		$Horario = $Aluno->Monta_Horario($Periodo->getPeriodo(false), $nivel);
		return $this->gerarIcsDeHorario($Horario, $Periodo, $opcoes, array());
	}

	/**
	 * Gera o conteudo RFC 5545 (.ics) a partir da matriz de horarios, periodo e extras
	 *
	 * @param array $Horario Matriz [$diaSemana][$hora][] = array($Oferecimento, $sala)
	 * @param Periodo|null $Periodo
	 * @param array $opcoes
	 * @param PlanejadoExtra[] $Extras
	 * @return string
	 */
	public function gerarIcsDeHorario(array $Horario, ?Periodo $Periodo = null, array $opcoes = array(), array $Extras = array()): string {
		$tz = new \DateTimeZone(self::TIMEZONE);
		list($primeiroDia, $ultimoDia) = $this->resolverLimitesPeriodo($Periodo, $tz);

		$until = clone $ultimoDia;
		$until->setTime(23, 59, 59);
		$untilUtc = self::formatUtcDateTime($until);
		$dtStamp = self::formatUtcDateTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

		$nomePeriodo = ($Periodo !== null && $Periodo->getNome(true) !== Periodo::PERIODO_DESCONHECIDO_DAC)
			? $Periodo->getNome(true)
			: 'Planejador';

		$linhas = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:' . self::PRODID,
			'CALSCALE:GREGORIAN',
			'METHOD:PUBLISH',
			'X-WR-CALNAME:' . self::escapeIcsText('GDE - Horarios ' . $nomePeriodo),
			'X-WR-TIMEZONE:' . self::TIMEZONE,
			'BEGIN:VTIMEZONE',
			'TZID:' . self::TIMEZONE,
			'BEGIN:STANDARD',
			'DTSTART:19700101T000000',
			'TZOFFSETFROM:-0300',
			'TZOFFSETTO:-0300',
			'TZNAME:BRT',
			'END:STANDARD',
			'END:VTIMEZONE',
		);

		$processados = array();
		$eventoIdx = 0;

		for ($diaSemana = 2; $diaSemana <= 7; $diaSemana++) {
			for ($hora = 7; $hora < 23; $hora++) {
				if (empty($Horario[$diaSemana][$hora])) {
					continue;
				}
				foreach ($Horario[$diaSemana][$hora] as $dados) {
					list($Oferecimento, $sala) = $dados;
					$salaStr = self::limparTextoHtml((string)$sala);
					$chave = $this->chaveEvento($Oferecimento, $salaStr);
					if (isset($processados[$diaSemana][$hora][$chave])) {
						continue;
					}

					$horaInicio = $hora;
					$horaTermino = $this->achaHorarioTermino($Horario, $diaSemana, $horaInicio, $Oferecimento, $salaStr);
					for ($h = $horaInicio; $h < $horaTermino; $h++) {
						$processados[$diaSemana][$h][$chave] = true;
					}

					$diaAula = $this->primeiroDiaAula($diaSemana, $primeiroDia);
					$inicio = (clone $diaAula)->setTime($horaInicio, 0, 0);
					$termino = (clone $diaAula)->setTime($horaTermino, 0, 0);

					$sigla = self::limparTextoHtml((string)$Oferecimento->getSigla(false));
					$turma = self::limparTextoHtml((string)$Oferecimento->getTurma(false));
					$titulo = trim($sigla . ' ' . $turma);
					$disciplina = $Oferecimento->getDisciplina(false);
					$nomeDisc = ($disciplina !== null) ? self::limparTextoHtml((string)$disciplina->getNome(false, false)) : '';
					if ($nomeDisc !== '') {
						$titulo .= ' - ' . $nomeDisc;
					}

					$descPartes = array();
					if ($sigla !== '') {
						$descPartes[] = 'Disciplina: ' . $sigla . ($nomeDisc !== '' ? ' (' . $nomeDisc . ')' : '');
					}
					if ($turma !== '') {
						$descPartes[] = 'Turma: ' . $turma;
					}
					$profs = $this->extrairNomesProfessores($Oferecimento);
					if ($profs !== '') {
						$descPartes[] = 'Professor(es): ' . $profs;
					}
					if ($salaStr !== '') {
						$descPartes[] = 'Sala: ' . $salaStr;
					}

					$eventoIdx++;
					$uid = sprintf('gde-aula-%s-%d-%d-%d@gde.unicamp', $nomePeriodo, $diaSemana, $horaInicio, $eventoIdx);

					$linhas[] = 'BEGIN:VEVENT';
					$linhas[] = 'UID:' . $uid;
					$linhas[] = 'DTSTAMP:' . $dtStamp;
					$linhas[] = 'SUMMARY:' . self::escapeIcsText($titulo);
					if ($salaStr !== '') {
						$linhas[] = 'LOCATION:' . self::escapeIcsText($salaStr);
					}
					if (!empty($descPartes)) {
						$linhas[] = 'DESCRIPTION:' . self::escapeIcsText(implode("\n", $descPartes));
					}
					$linhas[] = 'DTSTART;TZID=' . self::TIMEZONE . ':' . $inicio->format('Ymd\THis');
					$linhas[] = 'DTEND;TZID=' . self::TIMEZONE . ':' . $termino->format('Ymd\THis');
					$linhas[] = 'RRULE:FREQ=WEEKLY;UNTIL=' . $untilUtc;
					$linhas[] = 'END:VEVENT';
				}
			}
		}

		// Atividades extras / estagios do planejador
		foreach ($Extras as $Extra) {
			if (!($Extra instanceof PlanejadoExtra)) {
				continue;
			}
			$diaExtra = intval($Extra->getDia());
			// PlanejadoExtra usa 1=Segunda..6=Sabado, 7=Domingo; primeiroDiaAula usa Dom=1, Seg=2..Sab=7
			$diaAula = ($diaExtra === 7) ? 1 : ($diaExtra + 1);
			$dataBase = $this->primeiroDiaAula($diaAula, $primeiroDia);

			$inicioTime = $Extra->getInicio(false);
			$fimTime = $Extra->getFim(false);
			if (!($inicioTime instanceof \DateTimeInterface) || !($fimTime instanceof \DateTimeInterface)) {
				continue;
			}

			$inicio = (clone $dataBase)->setTime((int)$inicioTime->format('H'), (int)$inicioTime->format('i'), (int)$inicioTime->format('s'));
			$termino = (clone $dataBase)->setTime((int)$fimTime->format('H'), (int)$fimTime->format('i'), (int)$fimTime->format('s'));
			$eventoIdx++;

			$linhas[] = 'BEGIN:VEVENT';
			$linhas[] = 'UID:gde-extra-' . $nomePeriodo . '-' . $eventoIdx . '@gde.unicamp';
			$linhas[] = 'DTSTAMP:' . $dtStamp;
			$linhas[] = 'SUMMARY:' . self::escapeIcsText(self::limparTextoHtml((string)$Extra->getNome(false)));
			$linhas[] = 'DESCRIPTION:' . self::escapeIcsText('Estagio / Atividade Extra-Curricular (GDE)');
			$linhas[] = 'DTSTART;TZID=' . self::TIMEZONE . ':' . $inicio->format('Ymd\THis');
			$linhas[] = 'DTEND;TZID=' . self::TIMEZONE . ':' . $termino->format('Ymd\THis');
			$linhas[] = 'RRULE:FREQ=WEEKLY;UNTIL=' . $untilUtc;
			$linhas[] = 'END:VEVENT';
		}

		// Marcos do calendario academico da UNICAMP (opcionais)
		if (!empty($opcoes['incluir_calendario_unicamp']) && $Periodo !== null) {
			$marcos = array(
				array($Periodo->getData_Caderno_Horarios(false), $Periodo->getData_Caderno_Horarios(false), 'Divulgacao do caderno de horarios'),
				array($Periodo->getData_Desistencia_Inicio(false), $Periodo->getData_Desistencia_Fim(false), 'Periodo para desistencia de disciplinas'),
				array($Periodo->getData_Semana_Estudos_Inicio(false), $Periodo->getData_Semana_Estudos_Fim(false), 'Semana de estudos'),
				array($Periodo->getData_Exames_Inicio(false), $Periodo->getData_Exames_Fim(false), 'Semana de exames'),
				array($Periodo->getData_Matricula_Inicio(false), $Periodo->getData_Matricula_Fim(false), 'Periodo de matriculas'),
				array($Periodo->getData_Alteracao_Inicio(false), $Periodo->getData_Alteracao_Fim(false), 'Periodo de alteracao de matriculas'),
			);
			foreach ($marcos as $marco) {
				list($dIni, $dFim, $tituloMarco) = $marco;
				if (!($dIni instanceof \DateTimeInterface)) {
					continue;
				}
				if (!($dFim instanceof \DateTimeInterface)) {
					$dFim = $dIni;
				}
				$dtFimExclusivo = (new \DateTime($dFim->format('Y-m-d')))->modify('+1 day');
				$eventoIdx++;

				$linhas[] = 'BEGIN:VEVENT';
				$linhas[] = 'UID:gde-unicamp-' . $nomePeriodo . '-' . $eventoIdx . '@gde.unicamp';
				$linhas[] = 'DTSTAMP:' . $dtStamp;
				$linhas[] = 'SUMMARY:' . self::escapeIcsText('[UNICAMP] ' . $tituloMarco);
				$linhas[] = 'DTSTART;VALUE=DATE:' . $dIni->format('Ymd');
				$linhas[] = 'DTEND;VALUE=DATE:' . $dtFimExclusivo->format('Ymd');
				$linhas[] = 'END:VEVENT';
			}
		}

		$linhas[] = 'END:VCALENDAR';
		return implode("\r\n", $linhas) . "\r\n";
	}

	/**
	 * Retorna a primeira data de aula correspondente ao dia da semana (1=Dom, 2=Seg .. 7=Sab)
	 * sem mutar o objeto $primeiroDia recebido.
	 *
	 * @param int $diaAula
	 * @param \DateTimeInterface $primeiroDia
	 * @return \DateTime
	 */
	public function primeiroDiaAula(int $diaAula, \DateTimeInterface $primeiroDia): \DateTime {
		$base = new \DateTime($primeiroDia->format('Y-m-d H:i:s'), $primeiroDia->getTimezone());
		$diaN = intval($base->format('N')); // 1=Seg..7=Dom
		$diaSemana = ($diaN === 7) ? 1 : ($diaN + 1); // 1=Dom, 2=Seg..7=Sab
		if ($diaAula < $diaSemana) {
			$diferenca = 7 - ($diaSemana - $diaAula);
		} else {
			$diferenca = $diaAula - $diaSemana;
		}
		if ($diferenca > 0) {
			$base->modify('+' . $diferenca . ' days');
		}
		return $base;
	}

	/**
	 * Encontra o horario de termino de uma sequencia continua de aulas da mesma materia e sala.
	 *
	 * @param array $Horario
	 * @param int $dia
	 * @param int $hora
	 * @param Oferecimento|null $OferecimentoAlvo
	 * @param string|null $salaAlvo
	 * @return int
	 */
	public function achaHorarioTermino(array $Horario, int $dia, int $hora, $OferecimentoAlvo = null, $salaAlvo = null): int {
		if ($OferecimentoAlvo === null) {
			if (empty($Horario[$dia][$hora])) {
				return $hora + 1;
			}
			list($OferecimentoAlvo, $salaRaw) = $Horario[$dia][$hora][0];
			$salaAlvo = self::limparTextoHtml((string)$salaRaw);
		}

		$chaveAlvo = $this->chaveEvento($OferecimentoAlvo, (string)$salaAlvo);
		$horaTermino = $hora + 1;

		while ($horaTermino <= 23 && !empty($Horario[$dia][$horaTermino])) {
			$encontrou = false;
			foreach ($Horario[$dia][$horaTermino] as $dadosProximo) {
				list($ProxOferecimento, $proxSalaRaw) = $dadosProximo;
				$proxSala = self::limparTextoHtml((string)$proxSalaRaw);
				if ($this->chaveEvento($ProxOferecimento, $proxSala) === $chaveAlvo) {
					$encontrou = true;
					break;
				}
			}
			if (!$encontrou) {
				break;
			}
			$horaTermino++;
		}

		return $horaTermino;
	}

	/**
	 * Formata um DateTimeInterface para UTC no padrao iCalendar (Ymd\THis\Z)
	 *
	 * @param \DateTimeInterface $dt
	 * @return string
	 */
	public static function formatUtcDateTime(\DateTimeInterface $dt): string {
		$utc = (new \DateTimeImmutable($dt->format('c')))->setTimezone(new \DateTimeZone('UTC'));
		return $utc->format('Ymd\THis\Z');
	}

	/**
	 * Escapa texto conforme especificacao RFC 5545 (barra invertida, ponto-e-virgula, virgula e quebra de linha)
	 *
	 * @param string $text
	 * @return string
	 */
	public static function escapeIcsText(string $text): string {
		$decoded = self::limparTextoHtml($text);
		$decoded = str_replace(array("\r\n", "\r", "\n"), "\n", $decoded);
		$decoded = str_replace('\\', '\\\\', $decoded);
		$decoded = str_replace(array(';', ','), array('\;', '\,'), $decoded);
		$decoded = str_replace("\n", '\\n', $decoded);
		return $decoded;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private static function limparTextoHtml(string $text): string {
		return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
	}

	/**
	 * @param Oferecimento $Oferecimento
	 * @param string $sala
	 * @return string
	 */
	private function chaveEvento(Oferecimento $Oferecimento, string $sala): string {
		$sigla = self::limparTextoHtml((string)$Oferecimento->getSigla(false));
		$turma = self::limparTextoHtml((string)$Oferecimento->getTurma(false));
		return $sigla . '|' . $turma . '|' . $sala;
	}

	/**
	 * @param Oferecimento $Oferecimento
	 * @return string
	 */
	private function extrairNomesProfessores(Oferecimento $Oferecimento): string {
		$Professores = $Oferecimento->getProfessores(false);
		if ($Professores === null || count($Professores) === 0) {
			return '';
		}
		$nomes = array();
		foreach ($Professores as $Professor) {
			$nome = self::limparTextoHtml((string)$Professor->getNome(false));
			if ($nome !== '') {
				$nomes[] = $nome;
			}
		}
		return implode(', ', $nomes);
	}

	/**
	 * Resolve as datas de inicio e fim das aulas com fallback inteligente caso o Periodo nao tenha datas cadastradas.
	 *
	 * @param Periodo|null $Periodo
	 * @param \DateTimeZone $tz
	 * @return array [DateTime $inicio, DateTime $fim]
	 */
	private function resolverLimitesPeriodo(?Periodo $Periodo, \DateTimeZone $tz): array {
		if ($Periodo !== null && $Periodo->Tem_Inicio_E_Fim()) {
			$ini = $Periodo->getData_Inicio_Aulas(false);
			$fim = $Periodo->getData_Fim_Aulas(false);
			return array(
				new \DateTime($ini->format('Y-m-d') . ' 00:00:00', $tz),
				new \DateTime($fim->format('Y-m-d') . ' 00:00:00', $tz),
			);
		}

		$ano = ($Periodo !== null && $Periodo->getAno() > 1990) ? $Periodo->getAno() : intval(date('Y'));
		$sem = ($Periodo !== null) ? $Periodo->getSemestre() : 1;

		if ($sem === 2) {
			return array(
				new \DateTime($ano . '-08-01 00:00:00', $tz),
				new \DateTime($ano . '-12-15 00:00:00', $tz),
			);
		} elseif ($sem === Periodo::PERIODO_FERIAS_VERAO) {
			return array(
				new \DateTime($ano . '-01-05 00:00:00', $tz),
				new \DateTime($ano . '-02-20 00:00:00', $tz),
			);
		}

		return array(
			new \DateTime($ano . '-03-01 00:00:00', $tz),
			new \DateTime($ano . '-07-15 00:00:00', $tz),
		);
	}
}
