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
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_opcao;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $opcao;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $ativa = false;

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


}
