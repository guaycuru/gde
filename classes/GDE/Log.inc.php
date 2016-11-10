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
	 * @ORM\Column(name="id_log", type="bigint", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_log;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_usuario", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_usuario;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="descricao", type="text", nullable=false)
	 */
	protected $descricao;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data", type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=31, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="ip", type="string", length=15, nullable=false)
	 */
	protected $ip;


}
