<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table(name="gde_logs", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class Log extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="bigint", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_log;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_usuario;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $descricao;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=31, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=15, nullable=false)
	 */
	protected $ip;


}
