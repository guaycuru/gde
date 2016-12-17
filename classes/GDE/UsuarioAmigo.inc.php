<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioAmigo
 *
 * @ORM\Table(
 *   name="gde_usuarios_amigos",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="id_usuario_amigo", columns={"id_usuario", "id_amigo"})
 *   }
 * )
 * @ORM\Entity
 */
class UsuarioAmigo extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_amizade;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_amigo", referencedColumnName="id_usuario")
	 * })
	 */
	protected $amigo;

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
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $apelido;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $ativo = false;


}
