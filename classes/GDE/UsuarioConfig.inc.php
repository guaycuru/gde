<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioConfig
 *
 * @ORM\Table(name="gde_usuarios_config")
 * @ORM\Entity
 */
class UsuarioConfig extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_config;

	/**
	 * @var Usuario
	 *
	 * @ORM\OneToOne(targetEntity="Usuario", inversedBy="config")
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 */
	protected $usuario;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=1}, nullable=false)
	 */
	protected $avisos_aniversario = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=1}, nullable=false)
	 */
	protected $acontecimentos_mensagens = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=1}, nullable=false)
	 */
	protected $acontecimentos_minhas = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=1}, nullable=false)
	 */
	protected $acontecimentos_amigos = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", options={"default"=1}, nullable=false)
	 */
	protected $acontecimentos_grupos = true;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=1}, nullable=false)
	 */
	protected $acontecimentos_gde = true;


}
