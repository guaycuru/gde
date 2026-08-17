<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * VwDisciplinasCursadas
 *
 */
class VwDisciplinasCursadas extends Base {
	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="NREGALUN", type="integer", nullable=true)
	 */
	private $nregalun;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="PHIST", type="smallint", nullable=false)
	 */
	private $phist = '0';

	/**
	 * @var string
	 *
	 * @ORM\Column(name="TPER", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	private $tper = '';

	/**
	 * @var int
	 *
	 * @ORM\Column(name="ANOHIST", type="smallint", nullable=false)
	 */
	private $anohist = '0';

	/**
	 * @var string
	 *
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=false, options={"fixed"=true})
	 */
	private $cdisc = '';

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="CTURMA", type="string", length=3, nullable=true, options={"fixed"=true})
	 */
	private $cturma;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="NCREDHIS", type="smallint", nullable=true)
	 */
	private $ncredhis;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="SITUACAO", type="string", length=17, nullable=true)
	 */
	private $situacao;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=false)
	 */
	private $tnivdis = '';


	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Base {
		// TODO: Implement DadoGDE() method.
	}
}
