<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoResposta
 *
 * @ORM\Table(name="gde_avaliacao_respostas", indexes={@ORM\Index(name="sigla", columns={"sigla"}), @ORM\Index(name="id_pergunta_usuario", columns={"id_pergunta", "id_usuario"})})
 * @ORM\Entity
 */
class AvaliacaoResposta extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
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
	 * @ORM\Column(type="string", length=5, nullable=true)
	 */
	protected $sigla;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=false)
	 */
	protected $resposta;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

}
