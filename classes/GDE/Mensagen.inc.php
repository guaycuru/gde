<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mensagen
 *
 * @ORM\Table(name="gde_mensagens", indexes={@ORM\Index(name="origem", columns={"origem"}), @ORM\Index(name="destino", columns={"destino"})})
 * @ORM\Entity
 */
class Mensagen extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
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
	 * @var \GDEGdeUsuarios
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="origem", referencedColumnName="login")
	 * })
	 */
	protected $origem;

	/**
	 * @var \GDEGdeUsuarios
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="destino", referencedColumnName="login")
	 * })
	 */
	protected $destino;


}
