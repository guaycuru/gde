<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Modalidade
 *
 * @ORM\Table(name="gde_modalidades", uniqueConstraints={@ORM\UniqueConstraint(name="id_curso", columns={"id_curso", "nivel", "sigla", "catalogo"})})
 * @ORM\Entity
 */
class Modalidade extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_modalidade", type="integer", options={"unsigned"=true}), nullable=false)
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
	 * @ORM\Column(name="nivel", type="string", length=1, nullable=false)
	 */
	protected $nivel;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sigla", type="string", length=2, nullable=true)
	 */
	protected $sigla;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="catalogo", type="smallint", nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=true)
	 */
	protected $nome;


}
