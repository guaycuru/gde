<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Planejado
 *
 * @ORM\Table(
 *  name="gde_planejados_eliminadas",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="id_planejado_disciplina", columns={"id_planejado", "id_disciplina"})
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
	 * @ORM\JoinColumn(name="id_planejado", referencedColumnName="id_planejado")
	 */
	protected $planejado;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $disciplina;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $parcial = false;

	/**
	 * @return UsuarioEliminada
	 */
	public function Para_UsuarioEliminada() {
		$UE = new UsuarioEliminada();
		$UE->markReadOnly();
		$UE->setUsuario($this->getPlanejado()->getUsuario(false), false);
		$UE->setDisciplina($this->getDisciplina(false), false);
		$UE->setPeriodo($this->getPlanejado()->getPeriodo_Atual(false), false);
		$UE->setParcial(false);
		return $UE;
	}

}
