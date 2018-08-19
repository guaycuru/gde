<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Periodo
 *
 * @ORM\Table(name="gde_periodos", indexes={@ORM\Index(name="tipo", columns={"tipo"})})
 * @ORM\Entity
 */
class Periodo extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 */
	protected $id_periodo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $nome;

	const TIPO_NORMAL = 'n';
	const TIPO_ATUAL = 'a';
	const TIPO_PROXIMO = 'p';
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, options={"default"="n"}, nullable=false)
	 */
	protected $tipo = self::TIPO_NORMAL;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $data_inicio_aulas;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $data_fim_aulas;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $data_desistencia;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $data_semana_estudos;

	/**
	* @var string
	*
	* @ORM\Column(type="string", nullable=true)
	*/
	protected $data_exames;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $data_matricula;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $data_alteracao;


	const PERIODO_DESCONHECIDO = 'Desconhecido';
	const PERIODO_DESCONHECIDO_DAC = '??????';

	public static function Load($periodo = null) {
		if($periodo == '?') {
			$Periodo = new self;
			$Periodo->markReadOnly();
			$Periodo->id_periodo = null;
			$Periodo->nome = self::PERIODO_DESCONHECIDO;
			$Periodo->tipo = '';
		} else
			$Periodo = parent::Load($periodo);
		return $Periodo;
	}

	// Metodo que passa no cheap check do ProxyGenerator
	public function getId_periodo() {
		return $this->id_periodo;
	}

	/**
	 * getPeriodo
	 *
	 * O campo periodo foi renomeado para id_periodo para seguir o padrao
	 *
	 * @param bool $html
	 * @return mixed
	 */
	public function getPeriodo($html = false) {
		return $this->getID_Periodo($html);
	}

	/**
	 * getNome
	 *
	 * Retorna o nome deste periodo
	 *
	 * @param bool $dac
	 * @return string
	 */
	public function getNome($dac = false) {
		if($dac)
			return $this->getNome_DAC();
		else
			return $this->nome;
	}

	public function getNome_DAC() {
		if($this->getID() == null)
			return self::PERIODO_DESCONHECIDO_DAC;
		return ((substr($this->id_periodo, -1) != 0)
			? substr($this->id_periodo, -1).'S'
			: 'VE').substr($this->id_periodo, 0, 4);
	}


	public function temInicioEFim() {
		if (empty($this->data_inicio_aulas) || empty($this->data_fim_aulas)){
			return false;
		}
		return true;
	}

	/**
	 * getInicioAulas
	 *
	 * Retorna a data do inicio deste periodo
	 *
	 * @return string
	 */
	public function getInicioAulas() {
		return $this->data_inicio_aulas;
	}

	/**
	 * getFimAulas
	 *
	 * Retorna a data do fim deste periodo
	 *
	 * @return string
	 */
	public function getFimAulas() {
		return $this->data_fim_aulas;
	}

	/**
	 * getDataDesistencia
	 *
	 * Retorna a data do ultimo dia para desistencia de disciplinas
	 *
	 * @return string
	 */
	public function getDataDesistencia() {
		return $this->data_desistencia;
	}

	/**
	 * getDataSemanaDeEstudos
	 *
	 * Retorna a data da semana de estudos
	 *
	 * @return string
	 */
	public function getDataSemanaDeEstudos() {
		return $this->data_semana_estudos;
	}

	/**
	 * getDataExames
	 *
	 * Retorna a data dos exames
	 *
	 * @return string
	 */
	public function getDataExames() {
		return $this->data_exames;
	}

	/**
	 * getDataMatricula
	 *
	 * Retorna a data da matricula do proximo semestre
	 *
	 * @return string
	 */
	public function getDataMatricula() {
		return $this->data_matricula;
	}

	/**
	 * getFimAulas
	 *
	 * Retorna a data da alteracao de matricula do proximo semestre
	 *
	 * @return string
	 */
	public function getDataAlteracao() {
		return $this->data_alteracao;
	}

	/**
	 * getDatasImportantesHTML
	 *
	 * Cria uma lista
	 *
	 * @return string
	 */
	public function getDatasImportantesHTML() {
		$ano = explode(' ', $this->nome)[0];
		$semestre = explode(' ', $this->nome)[2][0];
		echo '<li>'.$this->getDataDesistencia().' - Último dia para desistência de matrícula em disciplinas</li>';
		echo '<li>'.$this->getDataSemanaDeEstudos().' - Semana de estudos</li>';
		echo '<li>'.$this->getDataExames().' - Exames Finais</li>';
		echo '<li>'.$this->getDataMatricula().' - Matrícula em disciplinas do próximo semestre</li>';
		echo '<li>'.$this->getDataAlteracao().' - Alteração de matrícula em disciplinas do próximo semestre</li>';
	}

	/**
	 * getProximo
	 *
	 * Retorna o periodo anterior ao carregado
	 *
	 * @return self
	 */
	public function Anterior() {
		return self::_EM()->createQuery('SELECT P FROM '.get_class().' P WHERE P.id_periodo < ?1 ORDER BY P.id_periodo DESC')
			->setParameter(1, $this->getID(false))
			->setMaxResults(1)
			->getOneOrNullResult();
	}

	/**
	 * Proximo
	 *
	 * Retorna o proximo periodo depois do carregado
	 *
	 * @return self
	 */
	public function Proximo() {
		return self::_EM()->createQuery('SELECT P FROM '.get_class().' P WHERE P.id_periodo > ?1 ORDER BY P.id_periodo ASC')
			->setParameter(1, $this->getID(false))
			->setMaxResults(1)
			->getOneOrNullResult();
	}

	/**
	 * Listar
	 *
	 * Retorna a lista de periodos
	 *
	 * @return array
	 */
	public static function Listar() {
		return self::_EM()->createQuery('SELECT P FROM '.get_class().' P ORDER BY P.id_periodo DESC')->getResult();
	}

	/**
	 * getAtual
	 *
	 * Retorna o periodo atual
	 *
	 * @return self
	 */
	public static function getAtual() {
		return self::_EM()->createQuery('SELECT P FROM '.get_class().' P WHERE P.tipo = ?1')
			->setParameter(1, self::TIPO_ATUAL)
			->setMaxResults(1)
			->getOneOrNullResult();
	}

	/**
	 * getProximo
	 *
	 * Retorna o proximo periodo
	 *
	 * @return self
	 */
	public static function getProximo() {
		return self::_EM()->createQuery('SELECT P FROM '.get_class().' P WHERE P.tipo = ?1 ORDER BY P.id_periodo ASC')
			->setParameter(1, self::TIPO_PROXIMO)
			->setMaxResults(1)
			->getOneOrNullResult();
	}

	/*public static function Tem_Proximo() {
		$res = $db->Execute("SELECT ".self::$chave.", tipo FROM ".self::$tabela." WHERE tipo = 'a' OR tipo = 'p'");
		if($res->RecordCount() >= 2) {
			foreach($res as $linha)
				if($linha['tipo'] == 'p')
					return new self($linha[self::$chave], $db);
		} else
			return false;
	}*/
}
