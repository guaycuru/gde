<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoRanking
 *
 * @ORM\Table(
 *  name="gde_avaliacao_rankings",
 *  indexes={
 *     @ORM\Index(name="pergunta_disciplina_ranking", columns={"id_pergunta", "id_disciplina", "ranking"})
 *  },
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="pergunta_professor_disciplina", columns={"id_pergunta", "id_professor", "id_disciplina"})
 *  }
 * )
 * @ORM\Entity
 */
class AvaliacaoRanking extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_ranking;

	/**
	 * @var AvaliacaoPergunta
	 *
	 * @ORM\ManyToOne(targetEntity="AvaliacaoPergunta")
	 * @ORM\JoinColumn(name="id_pergunta", referencedColumnName="id_pergunta")
	 */
	protected $pergunta;

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
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $disciplina;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $ranking;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="decimal", precision=11, scale=10, nullable=false)
	 */
	protected $nota;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $votos;


}
