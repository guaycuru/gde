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
	 * @var boolean
	 *
	 * @ORM\Column(name="curso", type="boolean", nullable=false)
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
	 * @var boolean
	 *
	 * @ORM\Column(name="semestre", type="boolean", nullable=false)
	 */
	protected $semestre;


}
