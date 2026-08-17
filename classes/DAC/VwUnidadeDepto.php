<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * VwUnidadeDepto
 *
 */
class VwUnidadeDepto extends Base {
	/**
	 * @var string
	 *
	 * @ORM\Column(name="CUNIDEP", type="string", length=4, nullable=false)
	 */
	private $cunidep = '';

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="MLOTAB1", type="string", length=6, nullable=true, options={"fixed"=true})
	 */
	private $mlotab1;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="MLOTACE1", type="string", length=65, nullable=true, options={"fixed"=true})
	 */
	private $mlotace1;


	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Base {
		// TODO: Implement DadoGDE() method.
	}
}
