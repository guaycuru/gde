<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CurriculosEletiva
 *
 * @ORM\Table(name="gde_curriculos_eletivas")
 * @ORM\Entity
 */
class CurriculoEletiva extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_eletivas;

	/**
	 * @var ArrayCollection|CurriculoEletivaConjunto[]
	 *
	 * @ORM\OneToMany(targetEntity="CurriculoEletivaConjunto", mappedBy="eletiva", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected $conjuntos;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumn(name="id_curso", referencedColumnName="id_curso")
	 */
	protected $curso;

	/**
	 * @var Modalidade
	 *
	 * @ORM\ManyToOne(targetEntity="Modalidade")
	 * @ORM\JoinColumn(name="id_modalidade", referencedColumnName="id_modalidade")
	 */
	protected $modalidade;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=false)
	 */
	protected $creditos;

	// Determina se esta eh uma copia da entidade original, que pode ser modificada
	private $_copia = false;

	const TIPO_FECHADA = 1;
	const TIPO_SEMI_LIVRE = 2;
	const TIPO_LIVRE = 3;

	/**
	 * Consultar
	 *
	 * @param $param
	 * @return CurriculoEletiva[]
	 */
	public static function Consultar($param) {
		$dql = 'SELECT C FROM '.get_class().' C INNER JOIN C.curso U LEFT JOIN C.modalidade M ';
		if($param['curso'] == 51) {
			$dql .= 'WHERE U.numero = 28 ';
			unset($param['curso'], $param['modalidade']);
		} else
			$dql .= 'WHERE U.numero = :curso ';
		if(empty($param['modalidade'])) {
			$dql .= 'AND M.id_modalidade IS NULL ';
			unset($param['modalidade']);
		} else
			$dql .= 'AND M.sigla = :modalidade ';
		$dql .= 'AND C.catalogo = :catalogo ';
		$dql .= 'ORDER BY C.id_eletivas ASC';
		return self::_EM()->createQuery($dql)
			->setParameters($param)
			->getResult();
	}

	/**
	 * Bate_Eletiva
	 *
	 * Determina se uma $sigla pode ser contada para $eletiva
	 *
	 * @param $eletiva
	 * @param $sigla
	 * @param string $semi
	 * @return bool
	 */
	public static function Bate_Eletiva($eletiva, $sigla, $semi = '?') {
		if($semi === '?')
			$semi = (strpos($eletiva, '-') !== false);
		if($semi === false)
			return ($sigla == $eletiva);
		$len = strlen($eletiva);
		for($i = 0; $i < $len; $i++) {
			if($eletiva[$i] == '-')
				continue;
			if($eletiva[$i] != $sigla[$i])
				return false;
		}
		return true;
	}

	/**
	 * getConjuntos
	 *
	 * @param bool $vazio
	 * @return ArrayCollection
	 */
	public function getConjuntos($vazio = false) {
		$Conjuntos = parent::getConjuntos();
		if(($Conjuntos->isEmpty() === false) || ($vazio === false))
			return $Conjuntos;
		$Conjunto = new CurriculoEletivaConjunto();
		$Conjunto->setSigla('-----');
		return new ArrayCollection(array($Conjunto));
	}

	/**
	 * getTipo
	 *
	 * Determina se eh uma eletiva fechada, livre ou semi livre
	 *
	 * @return int
	 */
	public function getTipo() {
		if($this->getConjuntos(false)->isEmpty() === false) {
			foreach($this->getConjuntos(false) as $Conjunto) {
				if($Conjunto->Fechada())
					return self::TIPO_FECHADA;
			}
			return self::TIPO_SEMI_LIVRE;
		} else {
			return self::TIPO_LIVRE;
		}
	}

	/**
	 * Copia
	 *
	 * Se esta ja eh uma copia, retorna-a, caso contraria, cria uma copia e retorna-a
	 *
	 * @return $this|CurriculoEletiva
	 */
	public function Copia() {
		// ToDo: Remover isto
		if($this->_copia === true)
			return $this;
		$Copia = clone $this;
		Base::_EM()->detach($Copia);
		$Copia->_copia = true;
		return $Copia;
	}

}
