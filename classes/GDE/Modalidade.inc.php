<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Modalidade
 *
 * @ORM\Table(
 *  name="gde_modalidades",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="curso_sigla_catalogo", columns={"id_curso", "sigla", "catalogo"})
 *  }
 * )
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
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $nome;

	/**
	 * Listar
	 *
	 * @param array|string $niveis
	 * @param integer $curso
	 * @param integer $catalogo
	 * @return Modalidade[]
	 */
	public static function Listar($niveis, $curso, $catalogo = null) {
		$dql = 'SELECT M FROM '.get_class().' M INNER JOIN M.curso C '.
			'WHERE C.nivel IN (?1) AND C.numero = ?2 ';
		if($catalogo != null)
			$dql .= 'AND M.catalogo = ?3 ';
		$dql .= 'GROUP BY M.sigla ORDER BY M.sigla ASC';
		$query = self::_EM()->createQuery($dql);
		if(!is_array($niveis))
			$niveis = array($niveis);
		$query->setParameter(1, $niveis);
		$query->setParameter(2, $curso);
		if($catalogo != null)
			$query->setParameter(3, $catalogo);
		if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$query->useResultCache(true, CONFIG_RESULT_CACHE_TTL);
		return $query->getResult();
	}

	/**
	 * Por_Unique
	 *
	 * @param integer $curso
	 * @param string $sigla
	 * @param integer $catalogo
	 * @return self|null|false
	 */
	public static function Por_Unique($curso, $sigla, $catalogo) {
		return self::FindOneBy(array('curso' => $curso, 'sigla' => $sigla, 'catalogo' => $catalogo));
	}

}
