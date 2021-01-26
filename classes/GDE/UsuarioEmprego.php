<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioEmprego
 *
 * @ORM\Table(name="gde_usuarios_empregos")
 * @ORM\Entity
 */
class UsuarioEmprego extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_emprego;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $usuario;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $nome;

	// ToDo: Constants
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, options={"default"="e"}, nullable=false)
	 */
	protected $tipo = 'e';

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $fim;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $atual = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $cargo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $site;

}
