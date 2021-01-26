<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Equivalencia
 *
 * @ORM\Table(name="gde_equivalencias")
 * @ORM\Entity
 */
class Equivalencia extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_equivalencia;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina", inversedBy="equivalencias")
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $disciplina;

	/**
	 * @var ArrayCollection|EquivalenciaEquivalente[]
	 *
	 * @ORM\OneToMany(targetEntity="EquivalenciaEquivalente", mappedBy="equivalencia", cascade={"persist", "remove"}, orphanRemoval=true)
	 * @ORM\OrderBy({"sigla" = "ASC"})
	 */
	protected $equivalentes;

}
