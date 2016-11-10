<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatConversa
 *
 * @ORM\Table(name="gde_chat_conversas", indexes={@ORM\Index(name="id_usuario_origem", columns={"id_usuario_origem"}), @ORM\Index(name="id_usuario_destino", columns={"id_usuario_destino"})})
 * @ORM\Entity
 */
class ChatConversa extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_chat_conversa", type="bigint", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_chat_conversa;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_usuario_origem", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_usuario_origem;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_usuario_destino", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_usuario_destino;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="session_usuario_origem", type="string", length=27, nullable=true)
	 */
	protected $session_usuario_origem;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="session_usuario_destino", type="string", length=27, nullable=true)
	 */
	protected $session_usuario_destino;


}
