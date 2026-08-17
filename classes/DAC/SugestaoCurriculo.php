<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;
use GDE\Curriculo;

/**
 * SugestaoCurriculo
 *
 * @ORM\Table(name="vw_sugestao_curriculo")
 * @ORM\Entity(readOnly=true)
 */
class SugestaoCurriculo extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="DANOCAT", type="smallint", nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CCURSO", type="smallint", nullable=false)
	 */
	protected $codigo_curso;

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CNIVCUR", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $nivel_curso;

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CMODAL", type="string", length=2, nullable=false, options={"fixed"=true})
	 */
	protected $sigla_modalidade;

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="NPER", type="smallint", nullable=false)
	 */
	protected $semestre;

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=false)
	 */
	protected $sigla_disciplina = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=false)
	 */
	protected $nivel_disciplina = '';

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="NCREDDIS", type="integer", nullable=true)
	 */
	protected $creditos_disciplina;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CCURSO", referencedColumnName="CCURSO"),
	 *     @ORM\JoinColumn(name="CNIVCUR", referencedColumnName="CNIVCUR")
	 * })
	 */
	protected $curso;

	/**
	 * @var Modalidade
	 *
	 * @ORM\ManyToOne(targetEntity="Modalidade")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CCURSO", referencedColumnName="CCURSO"),
	 *     @ORM\JoinColumn(name="CNIVCUR", referencedColumnName="CNIVCUR"),
	 *     @ORM\JoinColumn(name="DANOCAT", referencedColumnName="DANOCAT"),
	 *     @ORM\JoinColumn(name="CMODAL", referencedColumnName="CMODAL")
	 * })
	 */
	protected $modalidade;

	/**
	 * @var \GDE\Curriculo
	 */
	private $_dado_gde = null;

	const ELET = 'E';
	const LING = 'L';

	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Curriculo {
		if($this->_dado_gde === null) {
			$Curso = $this->curso->Dado_GDE($load, null, $dirty);
			$Modalidade = $this->modalidade->Dado_GDE($load, null, $dirty);

			$eletiva = $this->sigla_disciplina == self::ELET;
			$linguas = $this->sigla_disciplina == self::LING;

			$eletiva = $linguas = false;
			if($this->sigla_disciplina == self::ELET) {
				$eletiva = true;
				$sigla_disciplina = Curriculo::ELETIVA;
			} elseif($this->sigla_disciplina == self::LING) {
				$linguas = true;
				$sigla_disciplina = Curriculo::LINGUAS;
			} else {
				$sigla_disciplina = $this->sigla_disciplina;
			}

			$Disciplina = null;
			if(!$eletiva && !$linguas) {
				$Disciplina_DAC = Disciplina::FindOneBy(array('sigla' => $this->sigla_disciplina, 'nivel' => $this->nivel_disciplina));
				if($Disciplina_DAC !== null) {
					$Disciplina = $Disciplina_DAC->Dado_GDE($load, null, $dirty);
				} else
					$Disciplina = \GDE\Disciplina::FindOneBy(array('sigla' => $this->sigla_disciplina, 'nivel' => $this->nivel_disciplina));
			}

			$Curriculos = ($load) ? \GDE\Curriculo::FindBy(array(
				'curso' => $Curso->getId(),
				'modalidade' => ($Modalidade != null) ? $Modalidade->getId() : null,
				'sigla' => $sigla_disciplina,
				'catalogo' => $this->catalogo,
				'semestre' => $this->semestre
			)) : array();

			if(count($Curriculos) == 0) {
				$Curriculo = new \GDE\Curriculo();
				$Curriculo->setCurso($Curso);
				$Curriculo->setModalidade($Modalidade);
				if($Disciplina != null && $Disciplina->getId() != null)
					$Curriculo->setDisciplina($Disciplina);
				if($eletiva)
					$Curriculo->setSigla(Curriculo::ELETIVA);
				elseif($linguas)
					$Curriculo->setSigla(Curriculo::LINGUAS);
				else
					$Curriculo->setSigla($sigla_disciplina);
				$Curriculo->setCatalogo($this->catalogo);
				$Curriculo->setSemestre($this->semestre);
				$Curriculo->setCreditos($this->creditos_disciplina);
				$dirty = true;
			} else
				$Curriculo = $Curriculos[0];

			if($Curriculo->getCreditos() != $this->creditos_disciplina) {
				$Curriculo->setCreditos($this->creditos_disciplina);
				$dirty = true;
			}

			$this->_dado_gde = $Curriculo;
		}
		return $this->_dado_gde;
	}
}
