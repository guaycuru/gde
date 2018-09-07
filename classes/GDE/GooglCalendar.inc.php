<?php

namespace GDE;

require_once('../vendor/autoload.php');
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_Event;
use DateTime;

/**
 * GooglCalendar
 *
 */
class GooglCalendar{
	/**
	 * @var Google_Client
	 *
	 */
	protected $client;

	/**
	 * @var Google_Service_Calendar
	 *
	 */
	protected $servico;

  /**
	 * @var string
	 *
	 */
	protected $tokenAcesso;

	// Constantes
	const FUSO_HORARIO = 'America/Sao_Paulo';
	const URL_CREDENCIAIS = '../credenciais.json'; // FIXME Colocar em um lugar melhor
  const URL_REDIRECIONAMENTO = CONFIG_URL .'views/google-calendar.php';

	public function __construct($estado = '') {
		$this->client = new Google_Client();
    $this->client->setApplicationName('GDE');
    $this->client->setScopes(Google_Service_Calendar::CALENDAR);
    $this->client->setAuthConfig(self::URL_CREDENCIAIS);
    $this->client->setAccessType('offline');
    $this->client->setRedirectUri(self::URL_REDIRECIONAMENTO);
		$this->client->setState($estado);
	}

  /**
   * setTokenAutenticacao
   *
   * redireciona para a pagina de autenticacao do Google
   */
  public function setTokenAutenticacao() {
    // Pede a autorizacao do usuario
    $authUrl = $this->client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
  }

  /**
   * setTokenAcesso
   *
   * param: tokenAutenticacao
   * @return accessToken
   */
  public function setTokenAcesso($codigo, $token = '') {
    if(empty($token)) {
      // Exchange authorization code for an access token.
      $accessToken = $this->client->fetchAccessTokenWithAuthCode($codigo);

      // Check to see if there was an error.
      if(array_key_exists('error', $accessToken)) {
          throw new Exception(join(', ', $accessToken));
      }
    } else {
			$accessToken = $token;
		}

    $this->client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if($this->client->isAccessTokenExpired()) {
        $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
    }
		return $accessToken;
  }

  /**
   * setServico
   *
   *
   * @return Google_Service_Calendar
   */
  public function setServico() {
    if(!empty($this->client)) {
      $this->servico = new Google_Service_Calendar($this->client);
    }
  }

  /**
   * getCalendarios
   *
   * Devolve a lista de calendarios do usuario
   * @return Calendar_List
   */
  public function getCalendarios() {
    $listaCalendarios = $this->servico->calendarList->listCalendarList();
    return $listaCalendarios;
  }

	/**
	 * criaCalendario
	 *
	 * Cria um novo calendario para o usuario
	 * @return Calendar_ID
	 */
	public function criaCalendario($nome) {
		$calendario = new Google_Service_Calendar_Calendar();
		$calendario->setSummary($nome);
		$calendario->setTimeZone(self::FUSO_HORARIO);
		$calendarioNovo = $this->servico->calendars->insert($calendario);
		return $calendarioNovo->getId();
	}

	/**
	 * adicionaCalendarioUnicamp
	 *
	 * Adiciona o calendario da UNICAMP no Calendar
	 */
	public function adicionaCalendarioUnicamp($idCalendario, $Periodo_Selecionado) {
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataCadernoHorarios(), $Periodo_Selecionado->getDataCadernoHorarios(), 'Divulgação do cardeno de horários');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataDesistenciaInicio(), $Periodo_Selecionado->getDataDesistenciaFim(), 'Período para desistência de disciplinas');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataSemanaDeEstudosInicio(), $Periodo_Selecionado->getDataSemanaDeEstudosFim(), 'Semana de estudos');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataExamesInicio(), $Periodo_Selecionado->getDataExamesFim(), 'Semana de exames');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataMatriculaInicio(), $Periodo_Selecionado->getDataMatriculaFim(), 'Período de matriculas');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataAlteracaoInicio(), $Periodo_Selecionado->getDataAlteracaoFim(), 'Período de alteração de matriculas');
	}

	/**
	 * criarEventoAllDay
	 *
	 * Cria um evento de dia todo
	 * @return Event_id
	 */
	private function criarEventoAllDay($idCalendario, $dataInicio, $dataFim, $nome) {
		if($dataInicio == '' || $dataFim == '')
			return -1;
		$evento = new Google_Service_Calendar_Event(array(
			'summary' => $nome,
			'location' => '',
			'start' => array(
				'date' => $dataInicio,
				'timeZone' => self::FUSO_HORARIO,
			),
			'end' => array(
				'date' => $dataFim,
				'timeZone' => self::FUSO_HORARIO,
			),
			'reminders' => array(
				'useDefault' => FALSE,
			),
		));
		return $this->servico->events->insert($idCalendario, $evento);
	}

	/**
   * adicionaHorario
   *
   * Cria os eventos das aulas no Calendario escolhido
   * @return null
   */
  public function adicionaHorario($idCalendario, $Horario, $Periodo_Selecionado) {
		$primeiroDia = $Periodo_Selecionado->getInicioAulas();
		$ultimoDia = new DateTime($Periodo_Selecionado->getFimAulas());
		$ultimoDia->modify('+ 1 day');

    for($diaSemana = 2; $diaSemana < 8; $diaSemana++) { // Percorre os dias
      for($hora = 7; $hora < 23; $hora++) { // Percorre as horas
        if(isset($Horario[$diaSemana][$hora])) {
					foreach($Horario[$diaSemana][$hora] as $dados) {
						list($Oferecimento, $sala) = $dados;
						$titulo = $Oferecimento->getSigla(true) . " " . $Oferecimento->getTurma(true);
	          $horaInicio = $hora;
	          $horaTermino = $this->achaHorarioTermino($Horario, $diaSemana, $hora); // cria apenas um evento para todas aulas iguais em sequencia
						$PrimeiroDiaAula = $this->primeiroDiaAula($diaSemana, $primeiroDia);
						$comeco = $PrimeiroDiaAula->setTime($horaInicio, 0)->format(DateTime::ATOM);
						$termino = $PrimeiroDiaAula->setTime($horaTermino, 0)->format(DateTime::ATOM);

	          $evento = new Google_Service_Calendar_Event(array(
	            'summary' => $titulo,
	            'location' => $sala,
	            'start' => array(
	              'dateTime' => $comeco,
	              'timeZone' => self::FUSO_HORARIO,
	            ),
	            'end' => array(
	              'dateTime' => $termino,
	              'timeZone' => self::FUSO_HORARIO,
	            ),
	            'recurrence' => array(
	              'RRULE:FREQ=WEEKLY;UNTIL=' . $ultimoDia->format("Ymd\THim\Z")
	            ),
	            'reminders' => array(
	              'useDefault' => FALSE,
	              'overrides' => array(
	                //array('method' => 'email', 'minutes' => 24 * 60),
	                //array('method' => 'popup', 'minutes' => 10),
	              ),
	            ),
	          ));
						$this->servico->events->insert($idCalendario, $evento);
						$hora = $horaTermino-1;
					}
        }
      }
    }
	}

	/**
	 * primeiroDiaAula
	 *
	 * parametros:
	 *	diaAula: dia da semana da aula da materia
	 *  primeiroDia: data do primeiro dia de aulas da unicamp
	 *
	 * @return DateTime data do primeiro dia de aula de uma materia
	 */
	public function primeiroDiaAula($diaAula, $primeiroDia) {
		$base = new DateTime($primeiroDia);
		$diaSemana = $base->format('N') + 1;
		if($diaAula < $diaSemana) {
			$diferenca = 7 - ($diaSemana - $diaAula);
		} else {
			$diferenca = $diaAula - $diaSemana;
		}
		if($diferenca > 0) {
			$base->modify('+ '.$diferenca.' days');
		}
		return $base;
	}

	/**
	 * achaHorarioTermino
	 *
	 * Acha horário de término da aula que começa na hora e dia determinados
	 *
	 * @return int termino de uma sequencia de aulas
	 */
	public function achaHorarioTermino($Horario, $dia, $hora) {
		$horaTermino = $hora + 1;
		if(isset($Horario[$dia][$hora])) {
			foreach($Horario[$dia][$hora] as $dados) {
				list($Oferecimento, $sala) = $dados;
				$titulo = $Oferecimento->getSigla(true) . " " . $Oferecimento->getTurma(true);

				if(!isset($Horario[$dia][$horaTermino])) return $horaTermino;
				foreach($Horario[$dia][$horaTermino] as $dadosProximo) {
					list($ProximoOferecimento, $proximaSala) = $dadosProximo;
					$proximoTitulo = $ProximoOferecimento->getSigla(true) . " " . $ProximoOferecimento->getTurma(true);

					while($hora < 23 && $titulo === $proximoTitulo && $sala === $proximaSala) {
						$hora = $hora + 1;
						$horaTermino = $horaTermino + 1;
						if(!isset($Horario[$dia][$horaTermino])) return $horaTermino;
						foreach($Horario[$dia][$horaTermino] as $dadosProximo) {
							list($ProximoOferecimento, $proximaSala) = $dadosProximo;
							$proximoTitulo = $ProximoOferecimento->getSigla(true) . " " . $ProximoOferecimento->getTurma(true);
						}
					}
				}
			}
		}
		return $horaTermino;
	}
}
