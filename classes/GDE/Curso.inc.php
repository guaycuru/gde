<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Curso
 *
 * @ORM\Table(name="gde_cursos", uniqueConstraints={@ORM\UniqueConstraint(name="numero_nivel", columns={"numero", "nivel"})})
 * @ORM\Entity
 */
class Curso extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_curso;

	/**
	 * @var ArrayCollection|Curriculo[]
	 *
	 * @ORM\OneToMany(targetEntity="Curriculo", mappedBy="curso")
	 */
	protected $curriculos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $numero;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $nivel;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $nome;

	const NIVEL_GRAD = 'G';
	const NIVEL_TEC = 'T';
	const NIVEL_MESTRADO = 'M';
	const NIVEL_DOUTORADO = 'D';
	const NIVEL_PROFISSIONAL = 'S';
	const NIVEIS_GRAD = array(self::NIVEL_GRAD, self::NIVEL_TEC);
	const NIVEIS_POS = array(self::NIVEL_MESTRADO, self::NIVEL_DOUTORADO, self::NIVEL_PROFISSIONAL);

	/**
	 * Listar
	 *
	 * @param array $niveis
	 * @param bool $sem_especial
	 * @return Curso[]
	 */
	public static function Listar($niveis = array(), $sem_especial = false) {
		$dql = 'SELECT C FROM GDE\\Curso C ';
		if(count($niveis) > 0)
			$dql .= 'WHERE C.nivel IN (?1) ';
		if($sem_especial)
			$dql .= 'AND C.numero != 99 ';
		$dql .= 'ORDER BY C.nome ASC';
		$query = self::_EM()->createQuery($dql);
		if(count($niveis) > 0)
			$query->setParameter(1, $niveis);
		return $query->getResult();
	}
	
	/**
	 * Por_Numero
	 *
	 * @param integer $numero
	 * @param array $niveis
	 * @return self|null
	 */
	public static function Por_Numero($numero, $niveis = self::NIVEIS_GRAD) {
		$dql = 'SELECT C FROM GDE\\Curso C WHERE C.numero = ?1';
		if(count($niveis) > 0)
			$dql .= ' AND C.nivel IN (?2)';
		$query = self::_EM()->createQuery($dql);
		$query->setParameter(1, $numero);
		if(count($niveis) > 0)
			$query->setParameter(2, $niveis);
		$query->setMaxResults(1);
		return $query->getOneOrNullResult();
	}

}
