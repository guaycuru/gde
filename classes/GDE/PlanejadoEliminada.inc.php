<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Planejado
 *
 * @ORM\Table(
 *  name="gde_planejados_eliminadas",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="id_planejado_sigla", columns={"id_planejado", "sigla"})
 *  }
 * )
 * @ORM\Entity
 */
class PlanejadoEliminada extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_planejado_eliminada;

	/**
	 * @var Planejado
	 *
	 * @ORM\ManyToOne(targetEntity="Planejado", inversedBy="eliminadas")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_planejado", referencedColumnName="id_planejado")
	 * })
	 */
	protected $planejado;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumn(name="sigla", referencedColumnName="sigla")
	 */
	protected $disciplina;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $parcial = false;

}
