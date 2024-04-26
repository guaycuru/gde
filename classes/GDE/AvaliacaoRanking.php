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

	/**
	 * @return bool
	 */
	public static function Atualizar() {
		if(!defined('CONFIG_AVALIACAO_MINIMO'))
			return false;
		self::_EM()->getConnection()->executeUpdate("TRUNCATE TABLE gde_avaliacao_rankings"); // TODO: tirar isto, sem isto ta bugando...
		foreach(AvaliacaoPergunta::Listar() as $Pergunta) {
			if($Pergunta->getTipo(false) == AvaliacaoPergunta::TIPO_PROFESSOR) {
				$qr = "
INSERT IGNORE INTO gde_avaliacao_rankings
(id_pergunta, id_professor, id_disciplina, ranking, nota, votos)
	SELECT id_pergunta, id_professor, NULL, IF(@last_w != W, @numero := @numero + 1, @numero) AS ranking, IF(@last_w != W, @last_w := W, @last_w) AS nota, votos
	FROM
		(SELECT id_pergunta, id_professor, ((X.V / (X.V + Z.M)) * X.r + (Z.M / (X.V + Z.M)) * Y.C) AS W, X.V AS votos
		FROM
			(SELECT id_pergunta, id_professor, AVG(resposta) AS R, COUNT(*) AS V FROM gde_avaliacao_respostas WHERE id_pergunta = ? GROUP BY id_professor) AS X,
			(SELECT AVG(resposta) AS C FROM gde_avaliacao_respostas WHERE id_pergunta = ?) AS Y,
			(SELECT (COUNT(*) * 0.001) AS M FROM gde_avaliacao_respostas WHERE id_pergunta = ? GROUP BY id_professor ORDER BY M DESC LIMIT 1) AS Z,
			(SELECT @numero := 0) AS R,
			(SELECT @last_w := 0.00) AS U
		WHERE X.V >= ".CONFIG_AVALIACAO_MINIMO."
		ORDER BY W DESC) AS A
ON DUPLICATE KEY UPDATE ranking = ranking";
			} elseif($Pergunta->getTipo(false) == AvaliacaoPergunta::TIPO_DISCIPLINA) {
				// ToDo
				continue;
			} elseif($Pergunta->getTipo(false) == AvaliacaoPergunta::TIPO_OFERECIMENTO) {
				$qr = "
INSERT IGNORE INTO gde_avaliacao_rankings
(id_pergunta, id_professor, ranking, id_disciplina, nota, votos)
	SELECT id_pergunta, id_professor, IF(@last_sigla != id_disciplina, @numero := 1, IF(@last_w != W, @numero := @numero + 1, @numero)) AS ranking, IF(@last_sigla != id_disciplina, @last_sigla := id_disciplina, id_disciplina) AS id_disciplina, IF(@last_w != W, @last_w := W, @last_w) AS nota, votos
	FROM
		(SELECT id_pergunta, id_professor, IF(@last_sigla = id_disciplina, @numero := @numero + 1, @numero := 1) AS ranking, IF(@last_sigla != id_disciplina, @last_sigla := id_disciplina, id_disciplina) AS id_disciplina, W, votos
		FROM
			(SELECT id_pergunta, id_professor, id_disciplina, ((X.V / (X.V + Z.M)) * X.r + (Z.M / (X.V + Z.M)) * Y.C) AS W, X.V AS votos
			FROM
				(SELECT id_pergunta, id_professor, id_disciplina, AVG(resposta) AS R, COUNT(*) AS V FROM gde_avaliacao_respostas WHERE id_pergunta = ? GROUP BY id_disciplina, id_professor) AS X,
				(SELECT AVG(resposta) AS C FROM gde_avaliacao_respostas WHERE id_pergunta = ?) AS Y,
				(SELECT (COUNT(*) * 0.001) AS M FROM gde_avaliacao_respostas WHERE id_pergunta = ? GROUP BY id_disciplina, id_professor ORDER BY M DESC LIMIT 1) AS Z
				WHERE X.V >= ".CONFIG_AVALIACAO_MINIMO."
				ORDER BY id_disciplina ASC, W DESC) AS A,
			(SELECT @numero := 0) AS R,
			(SELECT @last_sigla := '') AS S,
			(SELECT @last_w := 0.00) AS U
		ORDER BY id_pergunta ASC, id_disciplina ASC, W DESC) AS A
ON DUPLICATE KEY UPDATE ranking = A.ranking";
			}
			if(self::_EM()->getConnection()->executeUpdate($qr, array($Pergunta->getID(), $Pergunta->getID(), $Pergunta->getID())) === false)
				return false;
		}
		return true;
	}

}
