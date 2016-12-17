<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreConjunto
 *
 * @ORM\Table(name="gde_pre_conjuntos", indexes={@ORM\Index(name="sigla_catalogo", columns={"sigla", "catalogo"})})
 * @ORM\Entity
 */
class PreConjunto extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_conjunto;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina", inversedBy="pre_conjuntos")
	 * @ORM\JoinColumn(name="sigla", referencedColumnName="sigla")
	 */
	protected $disciplina;

	/**
	 * @var PreLista
	 *
	 * @ORM\OneToMany(targetEntity="PreLista", mappedBy="conjunto")
	 * @ORM\OrderBy({"sigla" = "ASC"})
	 */
	protected $lista;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=5, nullable=false)
	 */
	protected $sigla;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=4, nullable=false)
	 */
	protected $catalogo;


}
