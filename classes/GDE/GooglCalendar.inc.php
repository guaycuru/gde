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
  public function setTokenAutenticacao(){
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
  public function setTokenAcesso($codigo, $token = ''){
    if (empty($token)){
      // Exchange authorization code for an access token.
      $accessToken = $this->client->fetchAccessTokenWithAuthCode($codigo);

      // Check to see if there was an error.
      if (array_key_exists('error', $accessToken)) {
          throw new Exception(join(', ', $accessToken));
      }
    } else {
			$accessToken = $token;
		}

    $this->client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($this->client->isAccessTokenExpired()) {
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
  public function setServico(){
    if (!empty($this->client)){
      $this->servico = new Google_Service_Calendar($this->client);
    }
  }

  /**
   * getCalendarios
   *
   * Devolve a lista de calendarios do usuario
   * @return Calendar_List
   */
  public function getCalendarios(){
    $listaCalendarios = $this->servico->calendarList->listCalendarList();
    return $listaCalendarios;
  }

	/**
	 * criaCalendario
	 *
	 * Cria um novo calendario para o usuario
	 * @return Calendar_ID
	 */
	public function criaCalendario($nome){
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
	public function adicionaCalendarioUnicamp($idCalendario, $Periodo_Selecionado){
		$ano = explode(' ', $Periodo_Selecionado->getNome())[0];

		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataDesistencia(),	$ano, 'Último dia para desistência de disciplinas');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataSemanaDeEstudos(), $ano, 'Semana de estudos');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataExames(), $ano, 'Semana de exames');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataMatricula(), $ano, 'Período de matriculas');
		$this->criarEventoAllDay($idCalendario, $Periodo_Selecionado->getDataAlteracao(), $ano, 'Período de alteração de matriculas');
	}

	/**
	 * criarEventoAllDay
	 *
	 * Cria um evento de dia todo
	 * @return Event_id
	 */
	private function criarEventoAllDay($idCalendario, $data, $ano, $nome) {
		if (strlen($data) > 5) { // se são vários dias
			$primeiro = explode('-', $data)[0];
			$ultimo = explode('-', $data)[1];
		} else { // se é só 1
			$primeiro = $data;
			$ultimo = $data;
		}

		$data_inicio = explode('/', $primeiro);
		$data_termino = explode('/', $ultimo);
		$inicio = $this->criaDataDia($ano, $data_inicio[1], $data_inicio[0]);
		$termino = $this->criaDataDia($ano, $data_termino[1], $data_termino[0]);

		$evento = new Google_Service_Calendar_Event(array(
			'summary' => $nome,
			'location' => '',
			'start' => array(
				'date' => $inicio,
				'timeZone' => self::FUSO_HORARIO,
			),
			'end' => array(
				'date' => $termino,
				'timeZone' => self::FUSO_HORARIO,
			),
			'reminders' => array(
				'useDefault' => FALSE,
			),
		));
		return $this->servico->events->insert($idCalendario, $evento);
	}

	/**
	 * criaDataDia
	 *
	 * @return string com data no formato para eventos de dia todo (all-day)
	 */
	private function criaDataDia($ano, $mes, $dia){
		return date("Y-m-d", mktime(0, 0, 0, $mes, $dia, $ano));
	}

	/**
   * adicionaHorario
   *
   * Cria os eventos das aulas no Calendario escolhido
   * @return null
   */
  public function adicionaHorario($idCalendario, $Horario, $Periodo_Selecionado){

	}
}
