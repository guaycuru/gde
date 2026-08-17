<?php

namespace DAC;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Curso
 *
 * @ORM\Table(name="vw_cursos")
 * @ORM\Entity(readOnly=true)
 */
class Curso extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CCURSO", type="smallint", nullable=false)
	 */
	protected $codigo;

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CNIVCUR", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $nivel;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="NOME_CURSO", type="string", length=201, nullable=true)
	 */
	protected $nome;

	/**
	 * @var string Codigo da unidade e departamento
	 *
	 * @ORM\Column(name="CDEPTOFE", type="string", length=4, nullable=false)
	 */
	protected $cdeptofe = '';

	/**
	 * @var Modalidade[]
	 *
	 * @ORM\OneToMany(targetEntity="Modalidade", mappedBy="curso")
	 */
	protected $modalidades;

	/**
	 * @var \GDE\Curso
	 */
	private $_dado_gde = null;

	/**
	 * @param bool $load Se deve carregar do DB
	 * @param \GDE\Base|null $Parent
	 * @param bool $dirty Se o objeto foi modificado
	 * @return \GDE\Curso
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): \GDE\Curso {
		if($this->_dado_gde === null) {
			$Curso = ($load) ? \GDE\Curso::Por_Numero($this->codigo, $this->nivel) : null;

			if($Curso === null) {
				$Curso = new \GDE\Curso();
				$Curso->setNumero($this->codigo);
				$Curso->setNivel($this->nivel);
				$dirty = true;
			}

			if($Curso->getNome(false) != $this->nome) {
				$Curso->setNome($this->nome);
				$dirty = true;
			}

			// ToDo: Departamento

			$this->_dado_gde = $Curso;
		}
		return $this->_dado_gde;
	}

	/**
	 * @param $catalogo
	 * @return ArrayCollection|Modalidade[]
	 */
	public function Modalidades_Por_Catalogo($catalogo) {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("catalogo", $catalogo));
		return $this->getModalidades()->matching($criteria);
	}
}
