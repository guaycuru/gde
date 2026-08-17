<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * OferecimentoReserva
 *
 * @ORM\Table(name="vw_reservas")
 * @ORM\Entity(readOnly=true)
 */
class OferecimentoReserva extends Base {
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
	 * @var int|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CCURSORE", type="smallint", nullable=true)
	 */
	protected $numero_curso;

	/**
	 * @var string|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CNIVCUR", type="string", length=1, nullable=true, options={"fixed"=true})
	 */
	protected $nivel_curso;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="TPER", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $tipo_periodo = '';

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="CMODAL", type="string", length=2, nullable=true)
	 */
	protected $sigla_modalidade;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="ACATRESE", type="integer", nullable=true)
	 */
	protected $catalogo_reserva;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento", inversedBy="reservas")
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
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CCURSORE", referencedColumnName="CCURSO"),
	 *     @ORM\JoinColumn(name="CNIVCUR", referencedColumnName="CNIVCUR")
	 * })
	 */
	// Existem dados invalidos apontando para cursos inexistentes nessa tabela
	//protected $curso;

	/**
	 * @var \GDE\OferecimentoReserva
	 */
	protected $_dado_gde = null;

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\OferecimentoReserva|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\OferecimentoReserva {
		if($this->_dado_gde === null) {
			// Existem dados invalidos apontando para cursos inexistentes nessa tabela
			//$Curso = $this->curso->Dado_GDE($load, null, $dirty);
			// Entao buscamos o curso manualmente
			$Curso = \GDE\Curso::Por_Numero($this->numero_curso, $this->nivel_curso);
			if($Curso === null) {
				// Curso invalido
				return null;
			}
			$catalogo_gde = ($this->catalogo_reserva != null) ? $this->catalogo_reserva : null;

			$Oferecimento = $Parent;

			$Reserva = null;
			if($load && $Curso->getId() != null && $Oferecimento->getId() != null) {
				$Reservas = \GDE\OferecimentoReserva::FindBy(array('oferecimento' => $Oferecimento->getId(),
					'curso' => $Curso->getId(), 'catalogo' => $catalogo_gde));
				if(count($Reservas) > 0)
					$Reserva = $Reservas[0];
			}

			if($Reserva === null) {
				$Reserva = new \GDE\OferecimentoReserva();
				$Reserva->setCurso($Curso);
				$Reserva->setOferecimento($Oferecimento);
				$Reserva->setCatalogo($catalogo_gde);
				$dirty = true;
			}
			$this->_dado_gde = $Reserva;
		}
		return $this->_dado_gde;
	}
}
