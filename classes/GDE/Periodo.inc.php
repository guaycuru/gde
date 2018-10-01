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
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_inicio_aulas;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_fim_aulas;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_caderno_horarios;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_desistencia_inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_desistencia_fim;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_semana_estudos_inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_semana_estudos_fim;

	/**
	* @var \DateTime
	*
	* @ORM\Column(type="date", nullable=true)
	*/
	protected $data_exames_inicio;

	/**
	* @var \DateTime
	*
	* @ORM\Column(type="date", nullable=true)
	*/
	protected $data_exames_fim;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_matricula_inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_matricula_fim;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_alteracao_inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_alteracao_fim;

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

	/**
	 * Tem_Inicio_E_Fim
	 *
	 * Retorna se é possível criar o Calendar para o periodo
	 *
	 * @return boolean
	 */
	public function Tem_Inicio_E_Fim() {
		return (($this->getData_Inicio_Aulas() !== null) && ($this->getData_Fim_Aulas() !== null));
	}

	/**
	 * Tem_Calendario
	 *
	 * Retorna se existe alguma data cadastrado para o periodo
	 *
	 * @return bool
	 */
	public function Tem_Calendario() {
		return (
			($this->getData_Desistencia_Inicio() !== null) || ($this->getData_Desistencia_Fim() !== null) ||
			($this->getData_Caderno_Horarios() !== null) || ($this->getData_Semana_Estudos_Inicio() !== null) ||
			($this->getData_Semana_Estudos_Fim() !== null) || ($this->getData_Exames_Inicio() !== null) ||
			($this->getData_Exames_Fim() !== null) || ($this->getData_Matricula_Inicio() !== null) ||
			($this->getData_Matricula_Fim() !== null) || ($this->getData_Alteracao_Inicio() !== null) ||
			($this->getData_Alteracao_Fim() !== null)
		);
	}

	/**
	 * getDatasImportantesHTML
	 *
	 * Cria uma lista
	 *
	 * @return string
	 */
	public function Datas_Importantes_HTML() {
		$ret = '';
		if(!$this->Tem_Calendario()) {
			$ret .= '<input type="checkbox" id="checkbox-datas-importantes" disabled /><label for="checkbox-datas-importantes">Adicionar datas do calendário da UNICAMP</label>';
			$ret .= '<h2>Calendário da UNICAMP não disponível para inserção</h2>';
		} else {
			$ret .= '<input type="checkbox" id="checkbox-datas-importantes" /><label for="checkbox-datas-importantes">Adicionar datas do calendário da UNICAMP</label>';
			$ret .= '<ul id="calendario-unicamp">';
			if(($this->getData_Desistencia_Inicio() !== null) || ($this->getData_Desistencia_Fim() !== null))
				$ret .= '<li>'.$this->getData_Desistencia_Inicio('d/m/Y').' até '.$this->getData_Desistencia_Fim('d/m/Y').' - Último dia para desistência de matrícula em disciplinas</li>';
			if($this->getData_Caderno_Horarios() !== null)
				$ret .= '<li>'.$this->getData_Caderno_Horarios('d/m/Y').' - Divulgação do caderno de horários do próximo semestre</li>';
			if(($this->getData_Semana_Estudos_Inicio() !== null) || ($this->getData_Semana_Estudos_Fim() !== null))
				$ret .= '<li>'.$this->getData_Semana_Estudos_Inicio('d/m/Y').' até '.$this->getData_Semana_Estudos_Fim('d/m/Y').' - Semana de estudos</li>';
			if(($this->getData_Exames_Inicio() !== null) || ($this->getData_Exames_Fim() !== null))
				$ret .= '<li>'.$this->getData_Exames_Inicio('d/m/Y').' até '.$this->getData_Exames_Fim('d/m/Y').' - Exames Finais</li>';
			if(($this->getData_Matricula_Inicio() !== null) || ($this->getData_Matricula_Fim() !== null))
				$ret .= '<li>'.$this->getData_Matricula_Inicio('d/m/Y').' até '.$this->getData_Matricula_Fim('d/m/Y').' - Matrícula em disciplinas do próximo semestre</li>';
			if(($this->getData_Alteracao_Inicio() !== null) || ($this->getData_Alteracao_Fim() !== null))
				$ret .= '<li>'.$this->getData_Alteracao_Inicio('d/m/Y').' até '.$this->getData_Alteracao_Fim('d/m/Y').' - Alteração de matrícula em disciplinas do próximo semestre</li>';
			$ret .= '</ul>';
		}
		return $ret;
	}

	/**
	 * Anterior
	 *
	 * Retorna o periodo anterior ao carregado
	 *
	 * @return self
	 * @throws \Doctrine\ORM\NonUniqueResultException
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
	 * @throws \Doctrine\ORM\NonUniqueResultException
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
	 * @throws \Doctrine\ORM\NonUniqueResultException
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
	 * @throws \Doctrine\ORM\NonUniqueResultException
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
