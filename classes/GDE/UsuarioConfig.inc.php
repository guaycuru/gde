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
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
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
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $avisos_aniversario = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $avisos_forum = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $acontecimentos_mensagens = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $acontecimentos_minhas = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $acontecimentos_amigos = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $acontecimentos_grupos = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $acontecimentos_gde = true;


}
