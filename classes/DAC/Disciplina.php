<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * VwDisciplinas
 *
 * @ORM\Table(name="vw_disciplinas")
 * @ORM\Entity(readOnly=true)
 */
class Disciplina extends Base {
	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=7, nullable=false)
	 */
	protected $sigla;

	/**
	 * @var string|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=true)
	 */
	protected $nivel;

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="DANOCAT", type="smallint", nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="NOME_DISCI", type="string", length=120, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="TIPO_APROV", type="string", length=1, nullable=true, options={"fixed"=true})
	 */
	protected $tipo_aprovacao;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="TIPO_OFER_DISCI", type="smallint", nullable=true)
	 */
	protected $tipo_oferecimento;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="TIPO_PER", type="string", length=1, nullable=true, options={"fixed"=true})
	 */
	protected $tipo_periodo;

	/**
	 * @var int|null 0: nao (semanal), 1: sim (quinzenal)
	 *
	 * @ORM\Column(name="TIPO_DISCI", type="smallint", nullable=true)
	 */
	protected $quinzenal;

	/**
	 * @var string|null S: sim, N: nao
	 *
	 * @ORM\Column(name="FLAG_EXAME", type="string", length=1, nullable=true, options={"fixed"=true})
	 */
	protected $exame;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="NUM_CRED_DISCI", type="smallint", nullable=true)
	 */
	protected $creditos;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="CH", type="bigint", nullable=true)
	 */
	protected $carga_horaria;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="COD_DEPTO_DISC", type="string", length=4, nullable=false)
	 */
	protected $departamento = '';

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="DESC_EMEN", type="text", length=0, nullable=true)
	 */
	protected $ementa;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="DESC_BIBL", type="text", length=0, nullable=true)
	 */
	protected $bibliografia;

	/**
	 * @var DisciplinaPreRequisitos[]
	 *
	 * @ORM\OneToMany(targetEntity="DisciplinaPreRequisitos", mappedBy="disciplina")
	 */
	protected $pre_requisitos;

	/**
	 * @var \GDE\Disciplina
	 */
	private $_dado_gde = null;

	/**
	 * @param $ano
	 * @return DisciplinaPreRequisitos[]
	 */
	public function Pre_Requisitos_Por_Ano($ano): array {
		$Pre_Requisitos = array();
		foreach($this->pre_requisitos as $pre) {
			$inicio = $pre->getAno_Inicio();
			$fim = $pre->getAno_Fim();
			if(($inicio <= $ano) && ($fim == null || $fim >= $ano))
				$Pre_Requisitos[] = $pre;
		}
		return $Pre_Requisitos;
	}

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\Disciplina|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Disciplina {
		if ($this->_dado_gde === null) {
			$Disciplina = ($load) ? \GDE\Disciplina::Por_Sigla($this->sigla, $this->nivel, false) : null;

			if($Disciplina === null) {
				$Disciplina = new \GDE\Disciplina();
				$Disciplina->setSigla($this->sigla);
				$Disciplina->setNivel($this->nivel);
				$dirty = true;
			}

			if($Disciplina->getNome(false) != $this->nome) {
				$Disciplina->setNome($this->nome);
				$dirty = true;
			}

			if($Disciplina->getCreditos(false) != $this->creditos) {
				$Disciplina->setCreditos($this->creditos);
				$dirty = true;
			}

			$quinzenal = ($this->quinzenal == 1);
			if($Disciplina->getQuinzenal(false) !== $quinzenal) {
				$Disciplina->setQuinzenal($quinzenal);
				$dirty = true;
			}

			if($Disciplina->getEmenta(false) != $this->ementa) {
				$Disciplina->setEmenta($this->ementa);
				$dirty = true;
			}

			if($Disciplina->getBibliografia(false) != $this->bibliografia) {
				$Disciplina->setBibliografia($this->bibliografia);
				$dirty = true;
			}

			// ToDo: Catalogo, creditos por catalogo, tipo_aprovacao, tipo_oferecimento, tipo_periodo, exame, carga_horaria

			$this->_dado_gde = $Disciplina;
		}
		return $this->_dado_gde;
	}

}
