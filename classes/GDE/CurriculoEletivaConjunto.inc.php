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
	 * Nao utilizamos uma relation com disciplina aqui pois existem disciplinas do curriculo que nao temos em nosso DB
	 *
	 * @ORM\Column(type="string", length=5, nullable=false)
	 */
	protected $sigla;

	/**
	 * @return Disciplina|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function getDisciplina() {
		if(strpos($this->getSigla(false), '-') !== false) {
			$Disciplina = new Disciplina();
			$Disciplina->markReadOnly();
			$Disciplina->setSigla($this->getSigla(false));
			return $Disciplina;
		}

		if(parent::getDisciplina(false) !== null) {
			return parent::getDisciplina();
		}

		$Disciplina = Disciplina::Por_Sigla($this->getSigla(false), Disciplina::$NIVEIS_GRAD);

		if((parent::getDisciplina(false) === null) && ($Disciplina->getID() != null)) {
			$this->setDisciplina($Disciplina);
			$this->Save(false);
			self::_EM()->flush($this);
		}

		return $Disciplina;
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
