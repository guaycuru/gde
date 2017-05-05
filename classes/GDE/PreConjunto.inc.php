<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreConjunto
 *
 * @ORM\Table(
 *  name="gde_pre_conjuntos",
 *  indexes={
 *     @ORM\Index(name="disciplina_catalogo", columns={"id_disciplina", "catalogo"})
 *  }
 * )
 * @ORM\Entity
 */
class PreConjunto extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_conjunto;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina", inversedBy="pre_conjuntos")
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $disciplina;

	/**
	 * @var PreLista
	 *
	 * @ORM\OneToMany(targetEntity="PreLista", mappedBy="conjunto")
	 * @ORM\OrderBy({"sigla" = "ASC"})
	 */
	protected $lista;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=4, nullable=false)
	 */
	protected $catalogo;

	/**
	 * getSigla
	 *
	 * Retorna a sigla da Disciplina deste Conjunto
	 *
	 * @param bool $html
	 * @return null
	 */
	public function getSigla($html = false) {
		if($this->getDisciplina(false) === null)
			return null;
		return $this->getDisciplina()->getSigla($html);
	}

}
