<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquivalentesConjunto
 *
 * @ORM\Table(name="gde_equivalentes_conjuntos", uniqueConstraints={@ORM\UniqueConstraint(name="id_conjunto_sigla", columns={"id_conjunto", "sigla"})})
 * @ORM\Entity
 */
class EquivalentesConjunto extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_equivalente_conjunto;

	/**
	 * @var Equivalente
	 *
	 * @ORM\ManyToOne(targetEntity="Equivalente", inversedBy="conjuntos")
	 * @ORM\JoinColumn(name="id_equivalente", referencedColumnName="id_equivalente")
	 */
	protected $equivalente;

	/**
	 * @var string
	 *
	 * Nao utilizamos uma relation com disciplina aqui pois existem disciplinas equivalentes que nao temos em nosso DB
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $sigla;

}
