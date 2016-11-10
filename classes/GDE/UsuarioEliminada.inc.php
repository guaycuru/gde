<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuariosEliminada
 *
 * @ORM\Table(name="gde_usuarios_eliminadas", uniqueConstraints={@ORM\UniqueConstraint(name="id_usuario_sigla", columns={"id_usuario", "sigla"})})
 * @ORM\Entity
 */
class UsuarioEliminada extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_eliminada", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_eliminada;

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
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumn(name="sigla", referencedColumnName="sigla")
	 */
	protected $disciplina;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo", referencedColumnName="id_periodo")
	 */
	protected $periodo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="parcial", type="boolean", nullable=false)
	 */
	protected $parcial = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="proficiencia", type="boolean", nullable=false)
	 */
	protected $proficiencia = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=1, nullable=true)
	 */
	protected $tipo;

}
