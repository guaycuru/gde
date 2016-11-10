<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoPergunta
 *
 * @ORM\Table(name="gde_avaliacao_perguntas")
 * @ORM\Entity
 */
class AvaliacaoPergunta extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_pergunta", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_pergunta;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="pergunta", type="string", length=255, nullable=false)
	 */
	protected $pergunta;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=1, nullable=false)
	 */
	protected $tipo;


}
