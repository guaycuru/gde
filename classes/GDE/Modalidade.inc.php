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
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
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

	public static function Listar($nivel, $curso, $catalogo = null) {
		$dql = 'SELECT M FROM GDE\\Modalidade M INNER JOIN M.curso C '.
			'WHERE M.nivel = ?1 AND C.numero = ?2 ';
		if($catalogo != null)
			$dql .= 'AND M.catalogo = ?3 ';
		$dql .= 'GROUP BY M.sigla ORDER BY M.sigla ASC';
		$query = self::_EM()->createQuery($dql);
			$query->setParameter(1, $nivel);
			$query->setParameter(2, $curso);
		if($catalogo != null)
			$query->setParameter(3, $catalogo);
		return $query->getResult();
	}

}
