<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioEmprego
 *
 * @ORM\Table(name="gde_usuarios_empregos", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class UsuarioEmprego extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_emprego", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
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
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=1, nullable=false)
	 */
	protected $tipo = 'e';

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="inicio", type="date", nullable=true)
	 */
	protected $inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="fim", type="date", nullable=true)
	 */
	protected $fim;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="atual", type="boolean", nullable=false)
	 */
	protected $atual = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="cargo", type="string", length=255, nullable=true)
	 */
	protected $cargo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="site", type="string", length=255, nullable=true)
	 */
	protected $site;

}
