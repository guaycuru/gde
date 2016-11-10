<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApiSession
 *
 * @ORM\Table(name="gde_api_sessions", uniqueConstraints={@ORM\UniqueConstraint(name="sid", columns={"sid"})})
 * @ORM\Entity
 */
class ApiSession extends Base {
	/**
	 * @var string
	 *
	 * @ORM\Column(name="code", type="string", length=16, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $code;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="app", type="string", length=255, nullable=false)
	 */
	protected $app;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sym_key", type="text", nullable=false)
	 */
	protected $sym_key;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sym_iv", type="text", nullable=false)
	 */
	protected $sym_iv;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sid", type="string", length=32, nullable=true)
	 */
	protected $sid;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_usuario", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_usuario;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="rand", type="string", length=16, nullable=false)
	 */
	protected $rand;


}
