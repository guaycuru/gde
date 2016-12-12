<?php

namespace GDE;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoPergunta
 *
 * @ORM\Table(name="gde_avaliacao_perguntas"), indexes={@ORM\Index(name="tipo", columns={"tipo"})})
 * @ORM\Entity
 */
class AvaliacaoPergunta extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_pergunta;

	/**
	 * @var AvaliacaoResposta
	 *
	 * @ORM\OneToMany(targetEntity="AvaliacaoResposta", mappedBy="pergunta")
	 */
	protected $respostas;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $pergunta;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $tipo;

	const ERRO_JA_VOTOU = 1;
	const ERRO_NAO_CURSOU = 2;
	const ERRO_NAO_ALUNO = 3;

	/**
	 * @param $tipo
	 * @return mixed
	 */
	public static function Listar($tipo = null) {
		$params = array();
		if($tipo != null)
			$params[] = array('tipo' => $tipo);
		return self::FindBy($params);
	}

	public function getMedia($id_professor = null, $sigla = null, $cache = true) {
		// ToDO
		return false;
	}

	public function Pode_Votar(Usuario $Usuario, Professor $Professor = null, Disciplina $Disciplina = null) {
		if($Usuario->getRA() == null)
			return self::ERRO_NAO_ALUNO;
		if($this->Ja_Votou($Usuario, $Professor, $Disciplina))
			return self::ERRO_JA_VOTOU;
		if($Professor != null && $Disciplina != null) {
			if($Usuario->getAluno(true)->Cursou_Com($Professor, $Disciplina) === true)
				return true;
			if($Usuario->getAluno(true)->Trancou_Com($Professor, $Disciplina) === true)
				return true;
			return self::ERRO_NAO_CURSOU;
		} elseif($Professor != null) {
			if($Usuario->getAluno(true)->Cursou_Com($Professor) === true)
				return true;
			if($Usuario->getAluno(true)->Trancou_Com($Professor) === true)
				return true;
			return self::ERRO_NAO_CURSOU;
		} elseif($Disciplina != null) {
			if($Usuario->getAluno(true)->Cursou($Disciplina) === true)
				return true;
			if($Usuario->getAluno(true)->Trancou($Disciplina) === true)
				return true;
			return self::ERRO_NAO_CURSOU;
		}
	}

	/**
	 * @param Usuario $Usuario
	 * @param Professor|null $Professor
	 * @param Disciplina|null $Disciplina
	 * @return bool
	 */
	public function Ja_Votou(Usuario $Usuario, Professor $Professor = null, Disciplina $Disciplina = null) {
		return ($this->Meu_Voto($Usuario, $Professor, $Disciplina, false) !== null);
	}

	/**
	 * @param Usuario $Usuario
	 * @param Professor|null $Professor
	 * @param Disciplina|null $Disciplina
	 * @param bool $resposta
	 * @return mixed
	 */
	public function Meu_Voto(Usuario $Usuario, Professor $Professor = null, Disciplina $Disciplina = null, $resposta = true) {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("usuario", $Usuario));
		if($Professor !== null)
			$criteria->andWhere(Criteria::expr()->eq("professor", $Professor));
		if($Disciplina !== null)
			$criteria->andWhere(Criteria::expr()->eq("sigla", $Disciplina->getSigla(false)));
		$criteria->setMaxResults(1);
		$Voto = $this->getRespostas()->matching($criteria);
		if($Voto->count() > 0)
			return ($resposta === true)
				? $Voto->first()->getResposta(false)
				: $Voto->first();
	}
}
