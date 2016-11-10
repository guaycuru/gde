<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalasFoto
 *
 * @ORM\Table(name="gde_salas_fotos", indexes={@ORM\Index(name="id_sala", columns={"id_sala"})})
 * @ORM\Entity
 */
class SalasFoto extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_sala_foto", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_sala_foto;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_sala", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_sala;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="foto", type="string", length=100, nullable=false)
	 */
	protected $foto;


}
