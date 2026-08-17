<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * OferecimentoAluno
 *
 * @ORM\Table(name="vw_matricula_atual")
 * @ORM\Entity(readOnly=true)
 */
class OferecimentoAluno extends Base {
	/**
	 * @var int|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="PERREQ", type="smallint", nullable=true)
	 */
	private $periodo;

	/**
	 * @var string|null
	 *
	 * Tipo Periodo: Anual (A) ou Semestral (S)
	 * @ORM\Id
	 * @ORM\Column(name="TPERREQ", type="string", length=1, nullable=true, options={"fixed"=true})
	 */
	private $tipo_periodo;

	/**
	 * @var int|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="ANOREQ", type="smallint", nullable=true)
	 */
	private $ano;

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
	 * @var int|null
	 *
	 * @ORM\Column(name="NCREDDIS", type="integer", nullable=true)
	 */
	private $creditos_disciplina;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento", inversedBy="alunos")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="ANOREQ", referencedColumnName="ANOTURMA"),
	 *     @ORM\JoinColumn(name="PERREQ", referencedColumnName="PTURMA"),
	 *     @ORM\JoinColumn(name="CDISC", referencedColumnName="CDISC"),
	 *     @ORM\JoinColumn(name="TNIVDIS", referencedColumnName="TNIVDIS"),
	 *     @ORM\JoinColumn(name="CTURMA", referencedColumnName="CTURMA")
	 * })
	 */
	protected $oferecimento;

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
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\Aluno|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Aluno {
		return $this->aluno->Dado_GDE($load, null, $dirty);
	}
}
