<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquivalentesConjunto
 *
 * @ORM\Table(
 *  name="gde_equivalencias_equivalentes",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="id_equivalente_sigla", columns={"id_equivalencia_equivalente", "sigla"})
 *  }
 * )
 * @ORM\Entity
 */
class EquivalenciaEquivalente extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_equivalencia_equivalente;

	/**
	 * @var Equivalencia
	 *
	 * @ORM\ManyToOne(targetEntity="Equivalencia", inversedBy="equivalentes")
	 * @ORM\JoinColumn(name="id_equivalencia", referencedColumnName="id_equivalencia")
	 */
	protected $equivalencia;

	/**
	 * @var Disciplina
	 *
	 * Nem sempre estara preenchida pois existem disciplinas do curriculo que nao temos em nosso DB
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $disciplina;

	/**
	 * @var string
	 *
	 * Nao utilizamos uma relation com disciplina aqui pois existem disciplinas equivalentes que nao temos em nosso DB
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $sigla;

	/**
	 * @param bool $vazio
	 * @return Disciplina|null
	 */
	public function getDisciplina($vazio = true) {
		if(parent::getDisciplina(false) !== null) {
			return parent::getDisciplina();
		}

		$Disciplina = Disciplina::Por_Sigla($this->getSigla(false), Disciplina::$NIVEIS_GRAD, $vazio);

		if((parent::getDisciplina(false) === null) && ($Disciplina->getID() != null)) {
			$this->setDisciplina($Disciplina);
			$this->Save(false);
			self::_EM()->flush($this);
		}

		if(($vazio === true) && ($Disciplina->getID() == null)) {
			$Disciplina->setSigla($this->getSigla(false));
		}

		return $Disciplina;
	}

}
