<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * DisciplinaPreRequisitos
 *
 * @ORM\Table(name="vw_disciplinas_pre_requisitos")
 * @ORM\Entity(readOnly=true)
 */
class DisciplinaPreRequisitos extends Base {
	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=7, nullable=false)
	 */
	protected $sigla;

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=false)
	 */
	protected $nivel = '';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="DANOCAT", type="smallint", nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var int|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="ANO_INI_PRE_REQU", type="smallint", nullable=true)
	 */
	protected $ano_inicio;

	/**
	 * Ano final da vigencia da regra, ou null se ela ainda vale.
	 *
	 * NAO faz parte do identificador, de proposito: e a unica coluna da view
	 * que pode ser nula (34,6% das 447 mil linhas, justamente as regras
	 * vigentes) e o Doctrine recusa NULL em identificador, entao hidratar uma
	 * dessas linhas lancava ORMInvalidArgumentException e derrubava o
	 * cron/dac_disciplinas.php. Tira-la da chave nao perde unicidade: com ou
	 * sem ela as 447.902 linhas continuam distintas, porque ANO_INI_PRE_REQU e
	 * DESC_ATIV_CAD ja separam os conjuntos alternativos do mesmo periodo.
	 *
	 * @var int|null
	 *
	 * @ORM\Column(name="ANO_FIM_PRE_REQU", type="smallint", nullable=true)
	 */
	protected $ano_fim;

	/**
	 * @var string|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="DESC_ATIV_CAD", type="string", length=238, nullable=true)
	 */
	protected $lista_siglas;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina", inversedBy="pre_requisitos")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CDISC", referencedColumnName="CDISC"),
	 *     @ORM\JoinColumn(name="TNIVDIS", referencedColumnName="TNIVDIS"),
	 *     @ORM\JoinColumn(name="DANOCAT", referencedColumnName="DANOCAT")
	 * })
	 */
	protected $disciplina;

	/**
	 * @var \GDE\PreConjunto
	 */
	private $_dado_gde = null;

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\PreConjunto|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\PreConjunto {
		if($this->_dado_gde === null) {
			$Disciplina = ($Parent !== null) ? $Parent : $this->disciplina->Dado_GDE($load, null, $dirty);

			// Nao eh possivel fazer load de um pre conjunto
			$Conjunto = new \GDE\PreConjunto();
			$Conjunto->setDisciplina($Disciplina);

			// Assumindo que dac_disciplinas esta pegando disciplinas pelo catalogo,
			// este PreRequisitos vai ter o mesmo ano de catalogo
			$Conjunto->setCatalogo($this->catalogo);
			$dirty = true;

			preg_match_all('/(\*?(?:\w{2}\d{3}|\w\s+\d{3}))\s*/i', trim($this->lista_siglas), $msiglas, PREG_PATTERN_ORDER);

			foreach($msiglas[1] as $sigla) {
				$Pre = new \GDE\PreLista();
				if($sigla[0] == '*') {
					$Pre->setSigla(substr($sigla, 1));
					$Pre->setParcial(true);
				} else {
					$Pre->setSigla($sigla);
					$Pre->setParcial(false);
				}
				$Conjunto->addLista($Pre);
			}

			$this->_dado_gde = $Conjunto;
		}
		return $this->_dado_gde;
	}
}
