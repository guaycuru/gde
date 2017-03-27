<?php

namespace GDE;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Mapping as ORM;

/**
 * AvaliacaoPergunta
 *
 * @ORM\Table(
 *  name="gde_avaliacao_perguntas",
 *  indexes={
 *     @ORM\Index(name="tipo", columns={"tipo"})
 *  }
 * )
 * @ORM\Entity
 */
class AvaliacaoPergunta extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
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

	// Caches
	private $MediaP = array(); //array('R' => 0, 'v' => 0, 'C' => 0, 'm' => 0, 'W' => 0);
	private $MediaD = array(); //array('R' => 0, 'v' => 0, 'C' => 0, 'm' => 0, 'W' => 0);
	private $MediaO = array(); //array('R' => 0, 'v' => 0, 'C' => 0, 'm' => 0, 'W' => 0);

	/**
	 * @param $tipo
	 * @return mixed
	 */
	public static function Listar($tipo = null) {
		$params = array();
		if($tipo != null)
			$params = array('tipo' => $tipo);
		return self::FindBy($params);
	}

	/**
	 * @param null $id_professor
	 * @param null $sigla
	 * @param bool $cache
	 * @return array
	 */
	public function getMedia($id_professor = null, $sigla = null, $cache = true) {
		// ToDo: Pegar tabelas da metadata
		$param = array('pergunta' => $this->getID());
		if(($id_professor != null) && ($sigla != null)) {
			$cond_rv = "A.professor = :professor AND A.disciplina = :disciplina";
			$param['professor'] = $id_professor;
			$param['disciplina'] = $sigla;
			$group = "A.professor, A.disciplina";
			$Media =& $this->MediaO[$id_professor][$sigla];
		} elseif($id_professor != null) {
			$cond_rv = "A.professor = :professor";
			$param['professor'] = $id_professor;
			$group = "A.professor";
			$Media =& $this->MediaP[$id_professor];
		} elseif($sigla != null) {
			$cond_rv = "A.disciplina = :disciplina";
			$param['disciplina'] = $sigla;
			$group = "A.disciplina";
			$Media =& $this->MediaD[$sigla];
		} else
			return array('r' => 0, 'v' => 0, 'c' => 0, 'm' => 0, 'w' => 0);
		if($cache) {
			// w = nota
			// v = votos
			$dql = "SELECT A FROM GDE\\AvaliacaoRanking AS A WHERE A.pergunta = :pergunta AND ".$cond_rv;
			$qwv = self::_EM()->createQuery($dql)->setParameters($param)->setMaxResults(1)->getOneOrNullResult();
			if($qwv === null) {
				$w = $v = $m = 0;
			} else {
				$w = $qwv->getNota(false);
				$v = $qwv->getVotos(false);
			}
			$m = 0; // Desnecessario quando vem do cache
			$c = 0; // Desnecessario quando vem do cache
			$r = 0; // Desnecessario quando vem do cache
		} else {
			$rvq = "SELECT AVG(A.resposta) AS media, COUNT(*) AS votos FROM GDE\\AvaliacaoResposta AS A WHERE A.pergunta = :pergunta AND ".$cond_rv;
			$rv = self::_EM()->createQuery($rvq)->setParameters($param)->getScalarResult();
			$r = $rv['media'];
			$v = $rv['votos'];
			$cq = "SELECT AVG(A.resposta) AS media FROM GDE\\AvaliacaoResposta AS A WHERE A.pergunta = :pergunta";
			$c = self::_EM()->createQuery($cq)->setParameters($param)->getSingleScalarResult();
			// m = Pega o maior numero de respostas que esta pergunta teve pra algum professor * CONFIG_AVALIACAO_M_MUL
			$mq = "SELECT COUNT(*) AS conta FROM GDE\\AvaliacaoResposta AS A WHERE A.pergunta = :pergunta GROUP BY ".$group." ORDER BY `conta` DESC LIMIT 1";
			$m = self::_EM()->createQuery($mq)->setParameters($param)->getSingleScalarResult() * CONFIG_AVALIACAO_M_MUL;
			if($m == 0 && $v == 0)
				return $Media = array('r' => $r, 'v' => $v, 'c' => $c, 'm' => $m, 'w' => 0);
			$w = ($v / ($v + $m)) * $r + ($m / ($v + $m)) * $c;
		}
		return $Media = array('r' => $r, 'v' => $v, 'c' => $c, 'm' => $m, 'w' => $w);
	}

	/**
	 * @param Usuario $Usuario
	 * @param Professor|null $Professor
	 * @param Disciplina|null $Disciplina
	 * @return bool|int
	 */
	public function Pode_Votar(Usuario $Usuario, Professor $Professor = null, Disciplina $Disciplina = null) {
		if($Usuario->getAluno(false) === null)
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
		return false;
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
		//$criteria = Criteria::create()->where(Criteria::expr()->eq("usuario", $Usuario));
		$criteria = Criteria::create()->where(Criteria::expr()->eq("pergunta", $this));
		if($Professor !== null)
			$criteria->andWhere(Criteria::expr()->eq("professor", $Professor));
		if($Disciplina !== null)
			$criteria->andWhere(Criteria::expr()->eq("disciplina", $Disciplina));
		$criteria->setMaxResults(1);
		//$Voto = $this->getRespostas()->matching($criteria);
		$Voto = $Usuario->getAvaliacao_Respostas()->matching($criteria);
		if($Voto->count() > 0)
			return ($resposta === true)
				? $Voto->first()->getResposta(false)
				: $Voto->first();
		return null;
	}

	/**
	 * @param Professor|null $Professor
	 * @param Disciplina|null $Disciplina
	 * @return array|false|mixed
	 */
	public function Ranking(Professor $Professor = null, Disciplina $Disciplina = null) {
		$param = array('pergunta' => $this->getID());
		if($Professor !== null)
			$param['professor'] = $Professor->getID();
		if($Disciplina !== null)
			$param['disciplina'] = $Disciplina->getID();
		$lista = true;
		if($this->getTipo(false) == 'p') { // Ranking de Professor
			if($Professor != null) { // Ranking deste professor
				$lista = false;
			}
		} elseif($this->getTipo(false) == 'd') { // Ranking de Disciplina
			if($Disciplina != null) {
				$lista = false;
			}
		} else { // Ranking de Professor x Disciplina
			if(($Professor != null) && ($Disciplina != null)) {
				$lista = false;
			}
		}
		$Resultados = AvaliacaoRanking::FindBy($param, array('ranking' => 'ASC'));
		if($lista)
			return $Resultados;
		if(count($Resultados) == 0)
			return '-';
		$Resultado = array_pop($Resultados);
		return $Resultado->getRanking(true);
	}

	/**
	 * @param Disciplina|null $Disciplina
	 * @return mixed|string
	 */
	public function Max_Ranking(Disciplina $Disciplina = null) {
		$param = array('pergunta' => $this->getID());
		if($Disciplina !== null)
			$param['disciplina'] = $Disciplina->getID();
		$res = self::_EM()->
			createQuery("SELECT MAX(A.ranking) FROM GDE\\AvaliacaoRanking AS A WHERE A.pergunta = :pergunta".(($Disciplina != null) ? " AND A.disciplina = :disciplina" : ""))
			->setParameters($param)
			->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
		return ($res != null) ? $res : '-';
	}
}
