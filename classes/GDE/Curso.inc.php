<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Curso
 *
 * @ORM\Table(name="gde_cursos", uniqueConstraints={@ORM\UniqueConstraint(name="numero", columns={"numero", "nivel"})})
 * @ORM\Entity
 */
class Curso extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_curso", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_curso;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="numero", type="smallint", nullable=false)
	 */
	protected $numero;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nivel", type="string", length=1, nullable=false)
	 */
	protected $nivel;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=true)
	 */
	protected $nome;

	/**
	 * Listar
	 *
	 * @param array $niveis
	 * @return ArrayCollection
	 */
	public static function Listar($niveis = array()) {
		$dql = 'SELECT C FROM GDE\\Curso C ';
		if(count($niveis) > 0)
			$dql .= 'WHERE C.nivel IN (?1) ';
		$dql .= 'ORDER BY C.nome ASC';
		$query = self::_EM()->createQuery($dql);
		if(count($niveis) > 0)
			$query->setParameter(1, $niveis);
		return $query->getResult();
	}

}
