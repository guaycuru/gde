<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * Modalidade
 *
 * @ORM\Table(name="vw_modalidades")
 * @ORM\Entity(readOnly=true)
 */
class Modalidade extends Base {
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
	 * @ORM\Column(name="CNIVCUR", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $nivel_curso = '';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="DANOCAT", type="smallint", nullable=false)
	 */
	protected $catalogo = '0';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CMODAL", type="string", length=2, nullable=false, options={"fixed"=true})
	 */
	protected $sigla = '';

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="MEXTMODAL", type="string", length=160, nullable=true, options={"fixed"=true})
	 */
	protected $nome;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="NTOTCCUR", type="integer", nullable=true)
	 */
	protected $ntotccur;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="NTOTHCUR", type="integer", nullable=true)
	 */
	protected $ntothcur;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso", inversedBy="modalidades")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CCURSO", referencedColumnName="CCURSO"),
	 *     @ORM\JoinColumn(name="CNIVCUR", referencedColumnName="CNIVCUR")
	 * })
	 */
	protected $curso;

	/**
	 * @var \GDE\Modalidade
	 */
	private $_dado_gde = null;

	/**
	 * @param bool $load Se deve carregar do DB
	 * @param \GDE\Base|null $Parent
	 * @param bool $dirty Se o objeto foi modificado
	 * @return \GDE\Modalidade|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Modalidade {
		if($this->sigla == null)
			return null;

		if($this->_dado_gde === null) {
			$Curso = $this->curso->Dado_GDE($load, null, $dirty);

			$Modalidade = ($load) ? \GDE\Modalidade::Por_Unique($Curso->getId(), $this->sigla, $this->catalogo) : null;

			if($Modalidade === null) {
				$Modalidade = new \GDE\Modalidade();
				$Modalidade->setCurso($Curso);
				$Modalidade->setSigla($this->sigla);
				$Modalidade->setCatalogo($this->catalogo);
				$dirty = true;
			}

			$nome = trim($this->nome);
			if($Modalidade->getNome(false) != $nome) {
				$Modalidade->setNome($nome);
				$dirty = true;
			}

			// ToDo: NTOTCCUR e NTOTHCUR

			$this->_dado_gde = $Modalidade;
		}
		return $this->_dado_gde;
	}

}
