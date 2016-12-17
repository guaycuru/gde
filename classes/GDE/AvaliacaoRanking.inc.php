<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoRanking
 *
 * @ORM\Table(name="gde_avaliacao_rankings", uniqueConstraints={@ORM\UniqueConstraint(name="id_pergunta", columns={"id_pergunta", "id_professor", "sigla"})})
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
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $id_pergunta;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
	 */
	protected $id_professor;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=5, nullable=true)
	 */
	protected $sigla;

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
