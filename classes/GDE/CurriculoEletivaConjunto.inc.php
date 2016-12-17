<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * CurriculosEletivasConjunto
 *
 * @ORM\Table(
 *   name="gde_curriculos_eletivas_conjuntos",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="id_eletivas_sigla", columns={"id_eletivas", "sigla"})
 *   }
 * )
 * @ORM\Entity
 */
class CurriculoEletivaConjunto extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @var CurriculoEletiva
	 *
	 * @ORM\ManyToOne(targetEntity="CurriculoEletiva", inversedBy="conjuntos")
	 * @ORM\JoinColumn(name="id_eletivas", referencedColumnName="id_eletivas")
	 */
	protected $eletiva;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=5, nullable=false)
	 */
	protected $sigla;

	/**
	 * getDisciplina
	 *
	 * @return Disciplina
	 */
	public function getDisciplina() {
		if(strpos($this->getSigla(false), '-') === false)
			return Disciplina::Por_Sigla($this->getSigla(false));
		else {
			$Disciplina = new Disciplina();
			$Disciplina->setSigla($this->getSigla(false));
			return $Disciplina;
		}
	}

	/**
	 * Fechada
	 *
	 * Retorna true se esta for uma disciplina fechada (sem - na sigla)
	 *
	 * @return bool
	 */
	public function Fechada() {
		return (strpos($this->getSigla(false), '-') === false);
	}

}
