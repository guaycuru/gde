<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * EnqueteOpcao
 *
 * @ORM\Table(name="gde_enquetes_opcoes", indexes={@ORM\Index(name="id_enquete", columns={"id_enquete"})})
 * @ORM\Entity
 */
class EnqueteOpcao extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_opcao", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_opcao;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="opcao", type="string", length=255, nullable=false)
	 */
	protected $opcao;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="ativa", type="boolean", nullable=false)
	 */
	protected $ativa = false;

	/**
	 * @var \GDEGdeEnquetes
	 *
	 * @ORM\ManyToOne(targetEntity="Enquete")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_enquete", referencedColumnName="id_enquete")
	 * })
	 */
	protected $id_enquete;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Usuario", mappedBy="id_opcao")
	 */
	protected $id_usuario;


}
