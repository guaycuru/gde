<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * OferecimentoDocente
 *
 * @ORM\Table(name="vw_ministra")
 * @ORM\Entity(readOnly=true)
 */
class OferecimentoDocente extends Base {
	/**
	 * @var int|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="ANOTURMA", type="smallint", nullable=true)
	 */
	protected $ano;

	/**
	 * @var int|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="PTURMA", type="smallint", nullable=true)
	 */
	protected $periodo;

	/**
	 * @var string|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TPER", type="string", length=1, nullable=true, options={"fixed"=true})
	 */
	protected $tipo_periodo;

	/**
	 * @var string|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=true, options={"fixed"=true})
	 */
	protected $sigla_disciplina;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=true)
	 */
	protected $nivel_disciplina;

	/**
	 * @var string|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CTURMA", type="string", length=3, nullable=true, options={"fixed"=true})
	 */
	protected $turma;

	/**
	 * @var int|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="MATRICULA", type="integer", nullable=true)
	 */
	protected $matricula;

	/**
	 * @var int|null
	 *
	 * Responsabilidade do docente
	 * @ORM\Column(name="FDOCERES", type="smallint", nullable=true)
	 */
	protected $fdoceres;

	/**
	 * @var string|null
	 *
	 * D: Docente, C: Colaborador externo
	 * @ORM\Column(name="TIPO_DOC", type="string", length=1, nullable=true)
	 */
	protected $tipo_docente;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento", inversedBy="docentes")
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
	 * @var Docente
	 *
	 * @ORM\ManyToOne(targetEntity="Docente")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="MATRICULA", referencedColumnName="MATRICULA")
	 * })
	 */
	protected $docente;

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return null
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Base {
		// Nao eh possivel fazer a conversao direta
		return null;
	}
}
