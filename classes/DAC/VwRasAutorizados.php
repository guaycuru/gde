<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * VwRasAutorizados
 *
 * @ORM\Table(name="vw_ras_autorizados")
 * @ORM\Entity(readOnly=true)
 */
class VwRasAutorizados extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Column(name="NREGALUN", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $nregalun;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="NOME_ALUNO", type="string", length=80, nullable=true)
	 */
	private $nomeAluno;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="DATA_INCLUSAO", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
	 */
	private $dataInclusao = 'CURRENT_TIMESTAMP';


	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Base {
		// TODO: Implement DadoGDE() method.
	}
}
