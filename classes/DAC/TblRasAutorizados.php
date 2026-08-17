<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * TblRasAutorizados
 *
 * @ORM\Table(name="tbl_ras_autorizados", indexes={@ORM\Index(name="IDX_RA", columns={"NREGALUN"})})
 * @ORM\Entity
 */
class TblRasAutorizados extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Column(name="NREGALUN", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $nregalun;

	/**
	 * @var \DateTime|null
	 *
	 * @ORM\Column(name="DATA_INCLUSAO", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
	 */
	private $dataInclusao = 'CURRENT_TIMESTAMP';


	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Base {
		// TODO: Implement DadoGDE() method.
	}
}
