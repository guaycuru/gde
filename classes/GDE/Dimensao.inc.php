<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dimensao
 *
 * @ORM\Table(
 *  name="gde_dimensoes",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="sala_dia_horario", columns={"id_sala", "dia", "horario"})
 *  }
 * )
 * @ORM\Entity
 */
class Dimensao extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_dimensao;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToMany(targetEntity="Oferecimento", mappedBy="dimensoes")
	 */
	protected $oferecimentos;

	/**
	 * @var Sala
	 *
	 * @ORM\ManyToOne(targetEntity="Sala")
	 * @ORM\JoinColumn(name="id_sala", referencedColumnName="id_sala")
	 */
	protected $sala;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=false)
	 */
	protected $dia;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=false)
	 */
	protected $horario;

	public static function Organizar(Dimensao $A, Dimensao $B) {
		$a = $A->getDia().$A->getHorario();
		$b = $B->getDia().$B->getHorario();
		if($a == $b)
			return 0;
		return  ($a < $b) ? -1 : 1;
	}

}
