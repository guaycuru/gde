<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * Docente
 *
 * @ORM\Table(name="vw_docentes")
 * @ORM\Entity(readOnly=true)
 */
class Docente extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="MATRICULA", type="integer", nullable=false)
	 */
	protected $matricula;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="NOME", type="string", length=60, nullable=true, options={"fixed"=true})
	 */
	protected $nome;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="TIPO", type="string", length=1, nullable=true)
	 */
	protected $tipo;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="DEPTO", type="string", length=4, nullable=true)
	 */
	protected $departamento;

	/**
	 * @var \GDE\Professor
	 */
	protected $_dado_gde = null;

	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Professor {
		if ($this->_dado_gde === null) {
			$Docente = null;
			if($load) {
				$Docente = \GDE\Professor::FindOneBy(array('matricula_dac' => $this->matricula));
				if($Docente === null) {
					$Docentes = \GDE\Professor::Consultar_Simples($this->nome);
					if(count($Docentes) > 0)
						$Docente = $Docentes[0];
				}
			}

			if($Docente === null) {
				$Docente = new \GDE\Professor();
				$dirty = true;
			}

			$nome = trim($this->nome);
			if($Docente->getNome(false) != $nome) {
				$Docente->setNome($nome);
				$dirty = true;
			}

			if($Docente->getMatricula_Dac(false) != $this->matricula) {
				$Docente->setMatricula_Dac($this->matricula);
				$dirty = true;
			}

			// ToDo: Tipo e Departamento

			$this->_dado_gde = $Docente;
		}
		return $this->_dado_gde;
	}
}
