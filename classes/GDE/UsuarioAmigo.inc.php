<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioAmigo
 *
 * @ORM\Table(
 *   name="gde_usuarios_amigos",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="id_usuario", columns={"id_usuario", "id_amigo"})
 *   },
 *   indexes={
 *     @ORM\Index(name="id_amigo", columns={"id_amigo"}),
 *     @ORM\Index(name="IDX_1C2916CAFCF8192D", columns={"id_usuario"})
 *   }
 * )
 * @ORM\Entity
 */
class UsuarioAmigo extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_amizade", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_amizade;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="apelido", type="string", length=255, nullable=true)
	 */
	protected $apelido;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="ativo", type="boolean", nullable=false)
	 */
	protected $ativo = false;

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


}
