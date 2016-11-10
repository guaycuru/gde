<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Recomendacoe
 *
 * @ORM\Table(name="gde_recomendacoes", uniqueConstraints={@ORM\UniqueConstraint(name="ra", columns={"ra"}), @ORM\UniqueConstraint(name="email", columns={"email"})}, indexes={@ORM\Index(name="login", columns={"login"}), @ORM\Index(name="recomendado", columns={"recomendado"})})
 * @ORM\Entity
 */
class Recomendacoe extends Base {
	/**
	 * @var string
	 *
	 * @ORM\Column(name="chave", type="string", length=16, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $chave = '';

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="ra", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $ra;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="email", type="string", length=255, nullable=false)
	 */
	protected $email;

	/**
	 * @var \GDEGdeUsuarios
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="login", referencedColumnName="login")
	 * })
	 */
	protected $login;

	/**
	 * @var \GDEGdeUsuarios
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="recomendado", referencedColumnName="login")
	 * })
	 */
	protected $recomendado;


}
