<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoResposta
 *
 * @ORM\Table(name="gde_avaliacao_respostas", indexes={@ORM\Index(name="id_professor", columns={"id_professor"}), @ORM\Index(name="sigla", columns={"sigla"}), @ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="id_pergunta", columns={"id_pergunta", "id_usuario"}), @ORM\Index(name="IDX_F1D22D1542919D01", columns={"id_pergunta"})})
 * @ORM\Entity
 */
class AvaliacaoResposta extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_resposta", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_resposta;

	/**
	 * @var AvaliacaoPergunta
	 *
	 * @ORM\ManyToOne(targetEntity="AvaliacaoPergunta")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_pergunta", referencedColumnName="id_pergunta")
	 * })
	 */
	protected $pergunta;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $usuario;

	/**
	 * @var Professor
	 *
	 * @ORM\ManyToOne(targetEntity="Professor")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_professor", referencedColumnName="id_professor")
	 * })
	 */
	protected $professor;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sigla", type="string", length=5, nullable=true)
	 */
	protected $sigla;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="resposta", type="smallint", options={"unsigned"=true}), nullable=false)
	 */
	protected $resposta;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data", type="datetime", nullable=false)
	 */
	protected $data;

}
