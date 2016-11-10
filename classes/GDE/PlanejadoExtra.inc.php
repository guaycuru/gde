<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlanejadoExtra
 *
 * @ORM\Table(name="gde_planejado_extras", indexes={@ORM\Index(name="id_planejado", columns={"id_planejado"})})
 * @ORM\Entity
 */
class PlanejadoExtra extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_planejado_extra", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_planejado_extra;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="dia", type="boolean", nullable=false)
	 */
	protected $dia;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="inicio", type="time", nullable=false)
	 */
	protected $inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="fim", type="time", nullable=false)
	 */
	protected $fim;

	/**
	 * @var \GDEGdePlanejados
	 *
	 * @ORM\ManyToOne(targetEntity="Planejado")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_planejado", referencedColumnName="id_planejado")
	 * })
	 */
	protected $id_planejado;


}
