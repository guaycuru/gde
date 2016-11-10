<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * CurriculosEletivasConjunto
 *
 * @ORM\Table(name="gde_curriculos_eletivas_conjuntos")
 * @ORM\Entity
 */
class CurriculosEletivasConjunto extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_eletivas_conjunto", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 */
	protected $id_eletivas_conjunto;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sigla", type="string", length=5, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 */
	protected $sigla;


}
