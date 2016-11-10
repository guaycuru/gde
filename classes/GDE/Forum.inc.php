<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Forum
 *
 * @ORM\Table(name="gde_foruns")
 * @ORM\Entity
 */
class Forum extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_forum", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_forum;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=1, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="id_pai", type="string", length=16, nullable=false)
	 */
	protected $id_pai;


}
