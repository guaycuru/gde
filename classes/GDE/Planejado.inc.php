<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Planejado
 *
 * @ORM\Table(name="gde_planejados", indexes={@ORM\Index(name="compartilhado", columns={"compartilhado"}), @ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class Planejado extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_planejado", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_planejado;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_periodo", type="smallint", nullable=false)
	 */
	protected $id_periodo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_periodo_atual", type="smallint", nullable=true)
	 */
	protected $id_periodo_atual;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="compartilhado", type="boolean", nullable=false)
	 */
	protected $compartilhado = false;

	/**
	 * @var \GDEGdeUsuarios
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $id_usuario;


}
