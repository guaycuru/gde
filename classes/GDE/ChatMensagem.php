<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatMensagem
 *
 * @ORM\Table(name="gde_chat_mensagens", indexes={@ORM\Index(name="id_chat_conversa", columns={"id_chat_conversa"}), @ORM\Index(name="recebida", columns={"recebida"})})
 * @ORM\Entity
 */
class ChatMensagem extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="bigint", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_chat_mensagem;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="bigint", nullable=false)
	 */
	protected $id_chat_conversa;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $direcao;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1000, nullable=false)
	 */
	protected $mensagem;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", options={"default"=0}, nullable=false)
	 */
	protected $data;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $recebida = false;


}
