<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * CurriculosEletiva
 *
 * @ORM\Table(name="gde_curriculos_eletivas")
 * @ORM\Entity
 */
class CurriculosEletiva extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_eletivas", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_eletivas;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="curso", type="boolean", nullable=false)
	 */
	protected $curso;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="modalidade", type="string", length=2, nullable=true)
	 */
	protected $modalidade;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="catalogo", type="smallint", nullable=false)
	 */
	protected $catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="creditos", type="smallint", nullable=false)
	 */
	protected $creditos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_eletivas_conjunto", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_eletivas_conjunto;


}
