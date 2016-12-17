<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mensagen
 *
 * @ORM\Table(name="gde_mensagens")
 * @ORM\Entity
 */
class Mensagen extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_mensagem;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $texto;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario_origem", referencedColumnName="id_usuario")
	 * })
	 */
	protected $origem;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario_destino", referencedColumnName="id_usuario")
	 * })
	 */
	protected $destino;


}
