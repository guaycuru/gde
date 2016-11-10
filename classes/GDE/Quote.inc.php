<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Quote
 *
 * @ORM\Table(name="gde_quotes", indexes={@ORM\Index(name="tipo", columns={"tipo"}), @ORM\Index(name="hash", columns={"hash"})})
 * @ORM\Entity
 */
class Quote extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="hash", type="string", length=32, nullable=false)
	 */
	protected $hash;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=1, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="texto", type="text", nullable=false)
	 */
	protected $texto;


}
