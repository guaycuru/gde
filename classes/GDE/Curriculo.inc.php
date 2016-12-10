<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Curriculo
 *
 * @ORM\Table(name="gde_curriculos", indexes={@ORM\Index(name="curso", columns={"curso", "modalidade", "catalogo"})})
 * @ORM\Entity
 */
class Curriculo extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_curriculo", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_curriculo;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumn(name="id_curso", referencedColumnName="id_curso")
	 */
	protected $curso;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="modalidade", type="string", length=2, nullable=true)
	 */
	protected $modalidade;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="catalogo", type="smallint", nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sigla", type="string", length=5, nullable=false)
	 */
	protected $sigla;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="semestre", type="smallint", options={"unsigned"=true}), nullable=false)
	 */
	protected $semestre;

	/**
	 * @param $param
	 * @return mixed
	 */
	public static function Consultar($param) {
		$dql = 'SELECT C FROM GDE\\Curriculo C INNER JOIN C.curso U ';
		if($param['curso'] == 51) {
			$dql .= 'WHERE U.numero = 28 AND C.semestre < 4 ';
		} else
			$dql .= 'WHERE U.numero = :curso ';
		if(empty($param['modalidade'])) {
			$dql .= 'AND C.modalidade IS NULL ';
			unset($param['modalidade']);
		} else
			$dql .= 'AND C.modalidade = :modalidade ';
		$dql .= 'AND C.catalogo = :catalogo ';
		$dql .= 'ORDER BY C.semestre ASC';
		return self::_EM()->createQuery($dql)
			->setParameters($param)
			->getResult();
	}

	/**
	 * @param $curso
	 * @param $modalidade
	 * @param $catalogo
	 * @return bool
	 */
	public static function Existe($curso, $modalidade, $catalogo) {
		// Se for cursao, utilizar o curriculo da matematica aplicada
		if($curso == 51)
			$curso = 28;
		$dql = 'SELECT COUNT(C) FROM GDE\\Curriculo C INNER JOIN C.curso U '.
			'WHERE U.numero = ?1 '.
			'AND C.modalidade '.(($modalidade == null) ? 'IS NULL ' : '= ?2 ').
			'AND C.catalogo = ?3';
		$query = self::_EM()->createQuery($dql);
		$query->setParameter(1, $curso);
		$query->setParameter(3, $catalogo);
		if($modalidade != null)
			$query->setParameter(2, $modalidade);
		return ($query->getSingleScalarResult() > 0);
	}

	/**
	 * @param bool $vazio
	 * @return Disciplina
	 */
	public function getDisciplina($vazio = false) {
		return Disciplina::Por_Sigla($this->getSigla(false), $this->getCurso(true)->getNivel(false), $vazio);
	}
}
