<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;
use GDE\Equivalencia;
use GDE\EquivalenciaEquivalente;

/**
 * DisciplinaContinencias
 *
 * @ORM\Table(name="vw_disciplinas_continencias")
 * @ORM\Entity(readOnly=true)
 */
class DisciplinaContinencias extends Base {
	/**
	 * @var string|null
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=true, options={"fixed"=true})
	 */
	protected $sigla;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="CADEXPSIGA", type="text", length=16777215, nullable=true)
	 */
	protected $conjuntos;

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

	/**
	 * Conjuntos
	 *
	 * Quebra o campo de continencias da DAC nos conjuntos de siglas que ele
	 * descreve. Os conjuntos vem separados por " / " e os marcados com "***"
	 * sao descartados.
	 *
	 * Nao toca o banco de proposito, para poder ser testada isoladamente.
	 *
	 * @param string|null $continencias
	 * @return array[] Lista de conjuntos, cada um uma lista de siglas
	 */
	public static function Conjuntos(?string $continencias): array {
		$Conjuntos = array();
		if(($continencias === null) || (trim($continencias) === ''))
			return $Conjuntos;

		foreach(preg_split("/\s+\/\s+/", $continencias) as $conjunto) {
			$conjunto = trim($conjunto);
			if(strpos($conjunto, '***') !== false)
				continue;

			if(preg_match_all('/(\w{2}\d{3}|\w\s+\d{3})\s*/i', $conjunto, $subse, PREG_PATTERN_ORDER) == 0)
				continue;

			$siglas = array();
			foreach($subse[1] as $sigla) {
				// Normaliza antes de deduplicar: siglas como "F  328" e "F 328"
				// sao a mesma coisa
				$sigla = trim(preg_replace('/\s\s+/', ' ', $sigla));
				$siglas[$sigla] = true;
			}

			$Conjuntos[] = array_keys($siglas);
		}

		return $Conjuntos;
	}

	/**
	 * EquivalenciasGDE
	 *
	 * Monta os conjuntos de Equivalencia da $Disciplina informada.
	 *
	 * A $Disciplina e obrigatoria: e ela que responde "de quem e este
	 * conjunto". Sem ela a gde_equivalencias.id_disciplina fica NULL e o
	 * mappedBy="disciplina" nunca acha nada, ou seja, a equivalencia existe no
	 * banco mas e invisivel para o sistema inteiro.
	 *
	 * @param \GDE\Disciplina $Disciplina Disciplina dona dos conjuntos
	 * @return Equivalencia[]
	 */
	public function EquivalenciasGDE(\GDE\Disciplina $Disciplina): array {
		$nivel = $Disciplina->getNivel(false);
		$Equivalencias = array();

		foreach(self::Conjuntos($this->conjuntos) as $siglas) {
			$Equivalencia = new Equivalencia();
			$Equivalencia->setDisciplina($Disciplina);

			foreach($siglas as $sigla) {
				$Equivalente = new EquivalenciaEquivalente();
				$Equivalente->setSigla($sigla);
				// Resolve a sigla: sem isto o getDisciplina() do equivalente
				// devolve uma Disciplina vazia e o conjunto nunca casa com o
				// historico do aluno
				$Equivalente_Disciplina = \GDE\Disciplina::Por_Sigla($sigla, $nivel, false);
				if($Equivalente_Disciplina !== null)
					$Equivalente->setDisciplina($Equivalente_Disciplina);
				$Equivalencia->addEquivalentes($Equivalente);
			}

			$Equivalencias[] = $Equivalencia;
		}

		return $Equivalencias;
	}
}
