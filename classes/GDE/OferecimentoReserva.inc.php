<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * OferecimentoReserva
 *
 * @ORM\Table(name="gde_oferecimentos_reservas")
 * @ORM\Entity
 */
class OferecimentoReserva extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_reserva;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento", inversedBy="reservas")
	 * @ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")
	 */
	protected $oferecimento;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumn(name="id_curso", referencedColumnName="id_curso")
	 */
	protected $curso;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $catalogo;

}
