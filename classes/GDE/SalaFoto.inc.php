<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalaFoto
 *
 * @ORM\Table(name="gde_salas_fotos")
 * @ORM\Entity
 */
class SalaFoto extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_sala_foto;

	/**
	 * @var Sala
	 *
	 * @ORM\ManyToOne(targetEntity="Sala")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_sala", referencedColumnName="id_sala")
	 * })
	 */
	protected $sala;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=100, nullable=false)
	 */
	protected $foto;


}
