<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChatJanela
 *
 * @ORM\Table(name="gde_chat_janelas")
 * @ORM\Entity
 */
class ChatJanela extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 */
	protected $id_usuario;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 */
	protected $id_usuario_janela;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $status;


}
