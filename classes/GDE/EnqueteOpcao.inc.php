<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * EnqueteOpcao
 *
 * @ORM\Table(name="gde_enquetes_opcoes")
 * @ORM\Entity
 */
class EnqueteOpcao extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_opcao;

	/**
	 * @var Enquete
	 *
	 * @ORM\ManyToOne(targetEntity="Enquete")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_enquete", referencedColumnName="id_enquete")
	 * })
	 */
	protected $enquete;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Usuario", mappedBy="enquetes_opcoes")
	 */
	protected $usuario;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $opcao;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $ativa = false;


}
