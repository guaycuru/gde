<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Modalidade
 *
 * @ORM\Table(name="gde_modalidades", uniqueConstraints={@ORM\UniqueConstraint(name="curso_nivel_sigla_catalogo", columns={"id_curso", "nivel", "sigla", "catalogo"})})
 * @ORM\Entity
 */
class Modalidade extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_modalidade;

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
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $nivel;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=2, nullable=true)
	 */
	protected $sigla;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $nome;

	/**
	 * Listar
	 *
	 * @param string $nivel
	 * @param integer $curso
	 * @param integer $catalogo
	 * @return ArrayCollection
	 */
	public static function Listar($nivel, $curso, $catalogo = null) {
		$dql = 'SELECT M FROM GDE\\Modalidade M INNER JOIN M.curso C '.
			'WHERE M.nivel IN (?1) AND C.numero = ?2 ';
		if($catalogo != null)
			$dql .= 'AND M.catalogo = ?3 ';
		$dql .= 'GROUP BY M.sigla ORDER BY M.sigla ASC';
		$query = self::_EM()->createQuery($dql);
		if(!is_array($nivel))
			$nivel = array($nivel);
		$query->setParameter(1, $nivel);
		$query->setParameter(2, $curso);
		if($catalogo != null)
			$query->setParameter(3, $catalogo);
		return $query->getResult();
	}

	/**
	 * Por_Unique
	 *
	 * @param string $nivel
	 * @param integer $curso
	 * @param string $sigla
	 * @param integer $catalogo
	 * @return self|null
	 */
	public static function Por_Unique($nivel, $curso, $sigla, $catalogo) {
		return self::FindOneBy(array('nivel' => $nivel, 'curso' => $curso, 'sigla' => $sigla, 'catalogo' => $catalogo));
	}

	/**
	 * Por_Curso_Sigla_Catalogo
	 *
	 * @param integer $curso
	 * @param string $sigla
	 * @param array $nivel
	 * @param integer $catalogo
	 * @return self|null
	 */
	public static function Por_Curso_Sigla_Catalogo($curso, $sigla, $nivel = array('G', 'T'), $catalogo = null) {
		$dql = 'SELECT M FROM GDE\\Modalidade M INNER JOIN M.curso C '.
			'WHERE M.nivel IN (?1) AND C.numero = ?2 ';
		if($catalogo != null)
			$dql .= 'AND M.catalogo = ?3 ';
		$dql .= 'GROUP BY M.sigla ORDER BY M.sigla ASC';
		$query = self::_EM()->createQuery($dql);
		$query->setParameter(1, $nivel);
		$query->setParameter(2, $curso);
		if($catalogo != null)
			$query->setParameter(3, $catalogo);
		$query->setMaxResults(1);
		return $query->getOneOrNullResult();
	}

}
