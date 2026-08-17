<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * MatriculadoDisciplinaTurma
 *
 * Esta view eh temporaria e deixara de existir no futuro
 * Alunos desta view nao estao presentes em vw_alunos
 *
 * @ORM\Table(name="vw_matriculados_disciplinas_turmas")
 * @ORM\Entity(readOnly=true)
 */
class MatriculadoDisciplinaTurma extends Base {
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
	 * Tipo Periodo: Anual (A) ou Semestral (S)
	 * @ORM\Column(name="TPER", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $tipo_periodo = '';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="NREGALUN", type="integer", nullable=false)
	 */
	private $ra_aluno = '0';

	/**
	 * @var string
	 *
	 * @ORM\Column(name="NOME_ALUNO", type="string", nullable=false)
	 */
	protected $nome_aluno = '';

	/**
	 * @var int|null Ano Catalogo
	 *
	 * @ORM\Column(name="CATALOGO", type="integer", nullable=true)
	 */
	protected $catalogo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=false, options={"fixed"=true})
	 */
	private $sigla_disciplina = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=false)
	 */
	private $nivel_disciplina = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CTURMA", type="string", length=3, nullable=false, options={"fixed"=true})
	 */
	private $turma = '';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CCURSO", type="smallint", nullable=false)
	 */
	protected $codigo_curso = '0';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="COD_NIV_CUR", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $nivel_curso = '';

	/**
	 * @var string Codigo da Modalidade
	 *
	 * @ORM\Column(name="CMODAL", type="string", length=2, nullable=false, options={"fixed"=true})
	 */
	protected $sigla_modalidade = '';

	/**
	 * @var Aluno
	 *
	 * @ORM\ManyToOne(targetEntity="Aluno")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="NREGALUN", referencedColumnName="NREGALUN")
	 * })
	 */
	protected $aluno;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento", inversedBy="matriculados")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="ANOTURMA", referencedColumnName="ANOTURMA"),
	 *     @ORM\JoinColumn(name="PTURMA", referencedColumnName="PTURMA"),
	 *     @ORM\JoinColumn(name="CDISC", referencedColumnName="CDISC"),
	 *     @ORM\JoinColumn(name="TNIVDIS", referencedColumnName="TNIVDIS"),
	 *     @ORM\JoinColumn(name="CTURMA", referencedColumnName="CTURMA")
	 * })
	 */
	protected $oferecimento;

	/**
	 * @var \GDE\Aluno
	 */
	private $_dado_gde;

	private static $_cache_alunos = array();

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\Aluno|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Aluno {
		if($this->_dado_gde === null) {
			if(array_key_exists($this->ra_aluno, self::$_cache_alunos))
				$Aluno_DAC = self::$_cache_alunos[$this->ra_aluno];
			else {
				$Aluno_DAC = Aluno::Novo($this->ra_aluno, $this->nome_aluno, $this->nivel_curso, $this->codigo_curso, $this->sigla_modalidade, $this->catalogo);
				self::$_cache_alunos[$this->ra_aluno] = $Aluno_DAC;
			}
			$Aluno = $Aluno_DAC->Dado_GDE($load, $Parent, $dirty);
			$this->_dado_gde = $Aluno;
		}
		return $this->_dado_gde;
	}

}
