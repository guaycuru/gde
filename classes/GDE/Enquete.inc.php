<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Enquete
 *
 * @ORM\Table(name="gde_enquetes")
 * @ORM\Entity
 */
class Enquete extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_enquete", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_enquete;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="pergunta", type="text", nullable=false)
	 */
	protected $pergunta;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data", type="date", nullable=false)
	 */
	protected $data;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="ativa", type="boolean", nullable=false)
	 */
	protected $ativa = false;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="max_votos", type="boolean", nullable=false)
	 */
	protected $max_votos = '1';


}
