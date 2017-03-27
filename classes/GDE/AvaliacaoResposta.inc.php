<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoResposta
 *
 * @ORM\Table(
 *  name="gde_avaliacao_respostas",
 *  indexes={
 *     @ORM\Index(name="sigla", columns={"sigla"}),
 *     @ORM\Index(name="pergunta_usuario", columns={"id_pergunta", "id_usuario"})
 *  },
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="pergunta_usuario_professor_sigla", columns={"id_pergunta", "id_usuario", "id_professor", "sigla"})
 *  }
 * )
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
	 * @ORM\JoinColumn(name="id_pergunta", referencedColumnName="id_pergunta")
	 */
	protected $pergunta;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="avaliacao_respostas")
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 */
	protected $usuario;

	/**
	 * @var Professor
	 *
	 * @ORM\ManyToOne(targetEntity="Professor")
	 * @ORM\JoinColumn(name="id_professor", referencedColumnName="id_professor")
	 */
	protected $professor;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina")
	 * @ORM\JoinColumn(name="sigla", referencedColumnName="sigla")
	 */
	protected $disciplina;

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
