<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nota
 *
 * @ORM\Table(name="gde_notas", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_materia", columns={"id_oferecimento"})})
 * @ORM\Entity
 */
class Nota extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_nota", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_nota;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_oferecimento", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_oferecimento;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sigla", type="string", length=255, nullable=false)
	 */
	protected $sigla;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nota", type="decimal", precision=4, scale=2, nullable=false)
	 */
	protected $nota;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="peso", type="decimal", precision=10, scale=5, nullable=false)
	 */
	protected $peso = '1.00000';

	/**
	 * @var \GDEGdeUsuarios
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $id_usuario;


}
