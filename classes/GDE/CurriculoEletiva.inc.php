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
	 * @var CurriculoEletivaConjunto
	 *
	 * @ORM\OneToMany(targetEntity="CurriculoEletivaConjunto", mappedBy="eletiva")
	 */
	protected $conjuntos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $curso;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=2, nullable=true)
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
		$dql = 'SELECT C FROM GDE\\CurriculoEletiva C ';
		if($param['curso'] == 51) {
			$dql .= 'WHERE C.curso = 28 ';
			unset($param['curso'], $param['modalidade']);
		} else
			$dql .= 'WHERE C.curso = :curso ';
		if(empty($param['modalidade'])) {
			$dql .= 'AND C.modalidade IS NULL ';
			unset($param['modalidade']);
		} else
			$dql .= 'AND C.modalidade = :modalidade ';
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
	 * getCOnjuntos
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
				if(strpos($Conjunto->getSigla(false), '-') !== false)
					return self::TIPO_SEMI_LIVRE;
			}
			return self::TIPO_FECHADA;
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
		if($this->_copia === true)
			return $this;
		$Copia = clone $this;
		$Copia->_copia = true;
		return $Copia;
	}

}
