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
	 * @ORM\Column(name="id_chat_mensagem", type="bigint", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_chat_mensagem;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_chat_conversa", type="bigint", nullable=false)
	 */
	protected $id_chat_conversa;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="direcao", type="string", length=1, nullable=false)
	 */
	protected $direcao;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="mensagem", type="string", length=1000, nullable=false)
	 */
	protected $mensagem;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data", type="datetime", nullable=false)
	 */
	protected $data = 'CURRENT_TIMESTAMP';

	/**
	 * @var string
	 *
	 * @ORM\Column(name="recebida", type="boolean", nullable=false)
	 */
	protected $recebida = false;


}
