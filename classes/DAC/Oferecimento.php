<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * Oferecimento
 *
 * @ORM\Table(name="vw_oferecimento")
 * @ORM\Entity(readOnly=true)
 */
class Oferecimento extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="ANOTURMA", type="smallint", nullable=false)
	 */
	protected $ano = '0';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="PTURMA", type="smallint", nullable=false)
	 */
	protected $periodo = '0';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=false, options={"fixed"=true})
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
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CTURMA", type="string", length=3, nullable=false, options={"fixed"=true})
	 */
	protected $turma = '';

	/**
	 * @var string
	 *
	 * Tipo Periodo: Anual (A) ou Semestral (S)
	 * @ORM\Column(name="TPER", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $tipo_periodo = '';

	/**
	 * @var int
	 *
	 * @ORM\Column(name="ACATDISC", type="smallint", nullable=false)
	 */
	protected $catalogo_disciplina = '0';

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="NVAGAORI", type="smallint", nullable=true)
	 */
	protected $vagas;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CDISC", referencedColumnName="CDISC"),
	 *     @ORM\JoinColumn(name="TNIVDIS", referencedColumnName="TNIVDIS"),
	 *     @ORM\JoinColumn(name="ACATDISC", referencedColumnName="DANOCAT")
	 * })
	 */
	protected $disciplina;

	/**
	 * @var OferecimentoDimensao[]
	 *
	 * @ORM\OneToMany(targetEntity="OferecimentoDimensao", mappedBy="oferecimento")
	 */
	protected $dimensoes;

	/**
	 * @var OferecimentoDocente[]
	 *
	 * @ORM\OneToMany(targetEntity="OferecimentoDocente", mappedBy="oferecimento")
	 */
	protected $docentes;

	/**
	 * @var OferecimentoReserva[]
	 *
	 * @ORM\OneToMany(targetEntity="OferecimentoReserva", mappedBy="oferecimento")
	 */
	protected $reservas;

	/**
	 * @var OferecimentoAluno[]
	 *
	 * @ORM\OneToMany(targetEntity="OferecimentoAluno", mappedBy="oferecimento")
	 */
	protected $alunos;

	/**
	 * @var MatriculadoDisciplinaTurma[]
	 *
	 * @ORM\OneToMany(targetEntity="MatriculadoDisciplinaTurma", mappedBy="oferecimento")
	 */
	protected $matriculados;

	/**
	 * @var \GDE\Oferecimento
	 */
	protected $_dado_gde = null;

	const PERIODO_PRIMEIRO_SEMESTRE = 1;
	const PERIODO_SEGUNDO_SEMESTRE = 2;
	const PERIODO_FERIAS_VERAO = 5;
	const PERIODO_FERIAS_INVERNO = 6;

	// ToDo: Suporte para outros periodos
	const PERIODOS_SUPORTADOS = array(
		self::PERIODO_PRIMEIRO_SEMESTRE,
		self::PERIODO_SEGUNDO_SEMESTRE,
		self::PERIODO_FERIAS_VERAO
	);

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\Oferecimento|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Oferecimento {
		if (in_array($this->periodo, self::PERIODOS_SUPORTADOS) === false)
			return null;

		if($this->_dado_gde === null) {
			$periodo_gde = ($this->periodo !== self::PERIODO_FERIAS_VERAO) ? $this->periodo : \GDE\Periodo::PERIODO_FERIAS_VERAO;
			$Periodo = ($load) ? \GDE\Periodo::Load($this->ano.$periodo_gde) : null;
			if($Periodo->getId() == null)
				// Periodo tem que ter sido criado previamente
				return null;

			$Disciplina = $this->disciplina->Dado_GDE($load, null, $dirty);

			$turma = trim($this->turma);
			$Oferecimento = ($load) ? \GDE\Oferecimento::Por_Unique($Disciplina, $turma, $Periodo) : null;
			if($Oferecimento === null) {
				$Oferecimento = new \GDE\Oferecimento();
				$Oferecimento->setDisciplina($Disciplina);
				$Oferecimento->setPeriodo($Periodo);
				$Oferecimento->setTurma($turma);
				$dirty = true;
			}

			if($Oferecimento->getVagas(false) != $this->vagas) {
				$Oferecimento->setVagas($this->vagas);
				$dirty = true;
			}

			$fechado = ($this->vagas == 0);
			if($Oferecimento->getFechado() !== $fechado) {
				$Oferecimento->setFechado($fechado);
				$dirty = true;
			}

			$this->_dado_gde = $Oferecimento;
		}
		return $this->_dado_gde;
	}

}
