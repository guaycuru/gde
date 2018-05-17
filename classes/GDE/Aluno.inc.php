<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Aluno
 *
 * @ORM\Table(
 *  name="gde_alunos",
 *  indexes={
 *     @ORM\Index(name="nome", columns={"nome"}, flags={"fulltext"})
 *  }
 * )
 * @ORM\Entity
 */
class Aluno extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 */
	protected $ra;

	/**
	 * @var Usuario
	 *
	 * @ORM\OneToOne(targetEntity="Usuario", mappedBy="aluno")
	 */
	protected $usuario;

	/**
	 * @var ArrayCollection|Oferecimento[]
	 *
	 * @ORM\ManyToMany(targetEntity="Oferecimento", inversedBy="alunos")
	 * @ORM\JoinTable(name="gde_r_alunos_oferecimentos",
	 *      joinColumns={@ORM\JoinColumn(name="ra", referencedColumnName="ra")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")}
	 * )
	 */
	protected $oferecimentos;

	/**
	 * @var ArrayCollection|Oferecimento[]
	 *
	 * @ORM\ManyToMany(targetEntity="Oferecimento")
	 * @ORM\JoinTable(name="gde_r_alunos_trancados",
	 *      joinColumns={@ORM\JoinColumn(name="ra", referencedColumnName="ra")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")}
	 * )
	 */
	protected $trancados;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumn(name="id_curso", referencedColumnName="id_curso")
	 */
	protected $curso;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumn(name="id_curso_pos", referencedColumnName="id_curso")
	 */
	protected $curso_pos;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $nivel;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $nivel_pos;

	/**
	 * @var string
	 *
	 * Nao podemos utilizar uma Relation com Modalidade pois Aluno nao tem Catalogo
	 *
	 * @ORM\Column(type="string", length=16, nullable=true)
	 */
	protected $modalidade;

	/**
	 * @var string
	 *
	 * Nao podemos utilizar uma Relation com Modalidade pois Aluno nao tem Catalogo
	 *
	 * @ORM\Column(type="string", length=16, nullable=true)
	 */
	protected $modalidade_pos;

	const NIVEL_EGRESSADO = 'E';
	const NIVEL_GRAD = 'G';
	const NIVEL_POS = 'P';

	private static $_niveis = array(
		'G' => 'Gradua&ccedil;&atilde;o',
		'T' => 'Tecnologia',
		self::NIVEL_EGRESSADO => 'Egressado',
		'M' => 'Mestrado',
		'D' => 'Doutorado',
		'P' => 'Aluno Especial',
		'S' => 'Mestrado Profissional'
	);
	private static $_niveis_grad = array(
		'G' => 'Gradua&ccedil;&atilde;o',
		'T' => 'Tecnologia',
		//self::NIVEL_EGRESSADO => 'Egressado'
	);
	private static $_niveis_pos = array(
		'M' => 'Mestrado',
		'D' => 'Doutorado',
		'P' => 'Aluno Especial',
		//self::NIVEL_EGRESSADO => 'Egressado'
	);

	// ToDo: Remover isso!
	public static $ordens_nome = array('Relev&acirc;ncia', 'RA', 'Nome', 'N&iacute;vel');
	public static $ordens_inte = array('rank', 'A.ra', 'A.nome', 'A.nivel');

	/**
	 * @return array
	 */
	public static function Listar_Niveis() {
		return self::$_niveis;
	}

	/**
	 * @return array
	 */
	public static function Listar_Niveis_Grad() {
		return self::$_niveis_grad;
	}

	/**
	 * @return array
	 */
	public static function Listar_Niveis_Pos() {
		return self::$_niveis_pos;
	}

	/**
	 * @param $ra
	 * @return Aluno|null|false
	 */
	public static function Por_RA($ra) {
		return self::FindOneBy(array('ra' => $ra));
	}

	/**
	 * Consultar
	 *
	 * Efetua uma consulta por Alunos
	 *
	 * @param $param
	 * @param null $ordem
	 * @param int $total
	 * @param int $limit
	 * @param int $start
	 * @return Aluno[]
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public static function Consultar($param, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		$qrs = $jns = array();
		$usou_periodo = false;
		if($ordem == null)
			$ordem = 'A.ra ASC';
		if(!empty($param['ra']))
			$qrs[] = "A.ra = :ra";
		if(!empty($param['nome'])) {
			$qrs[] = "A.nome LIKE :nome";
			$param['nome'] = '%'.$param['nome'].'%';
		}
		if(!empty($param['nivel']))
			$qrs[] = "A.nivel = :nivel";
		if(!empty($param['curso']))
			$qrs[] = "A.curso = :curso";
		if(!empty($param['modalidade']))
			$qrs[] = "A.modalidade = :modalidade";
		if(!empty($param['nivel_pos']))
			$qrs[] = "A.nivel_pos = :nivel_pos";
		if(!empty($param['curso_pos']))
			$qrs[] = "A.curso_pos = :curso_pos";
		if(!empty($param['modalidade_pos']))
			$qrs[] = "A.modalidade_pos = :modalidade_pos";
		if((!empty($param['gde'])) || (!empty($param['sexo'])) || (!empty($param['relacionamento'])) ||
			(!empty($param['cidade'])) || (!empty($param['estado'])) || (!empty($param['amigos']))) {
			$jns[] = " LEFT JOIN A.usuario AS U";
			if(!empty($param['gde'])) {
				$qrs[] = "U.id_usuario IS" . (($param['gde'] == 't') ? " NOT" : null) . " NULL";
				unset($param['gde']);
			}
			if(!empty($param['sexo']))
				$qrs[] = "U.sexo = :sexo";
			if(!empty($param['relacionamento']))
				$qrs[] = "U.estado_civil = :relacionamento";
			if(!empty($param['cidade'])) {
				$qrs[] = "U.cidade LIKE :cidade";
				$param['cidade'] = '%'.$param['cidade'].'%';
			}
			if(!empty($param['estado'])) {
				$qrs[] = "U.estado LIKE :estado";
				$param['estado'] = '%'.$param['estado'].'%';
			}
		}
		if(!empty($param['id_oferecimento'])) {
			$jns[] = "JOIN A.oferecimentos AS O";
			$qrs[] = "O.id_oferecimento = :id_oferecimento";
		} elseif((isset($param['oferecimentos'])) && (count($param['oferecimentos'][1]) > 0)) {
			$mts = array();
			if($param['oferecimentos'][0]) { // AND
				$i = 0;
				foreach($param['oferecimentos'][1] as $oferecimento) {
					$jns[] = "JOIN A.oferecimentos AS O".$i;
					$jns[] = "JOIN O".$i.".disciplina AS D".$i;
					$qrs[] = "O".$i.".periodo = :periodo AND D".$i.".sigla = :d".$i."sigla".(($oferecimento[1]!='*')?" AND O".$i.".turma = :o".$i."turma":"");
					$param['d'.$i.'sigla'] = $oferecimento[0];
					if($oferecimento[1] != '*')
						$param['o'.$i.'turma'] = $oferecimento[1];
					$i++;
					$usou_periodo = true;
				}
			} else { // OR
				$jns[] = " JOIN A.oferecimentos AS O";
				$i = 0;
				foreach($param['oferecimentos'][1] as $oferecimento) {
					$jns[] = "JOIN O.disciplina AS D".$i;
					$mts[] = "(D".$i.".sigla = :d".$i."sigla".(($oferecimento[1] != '*')?" AND O.turma = :o".$i."turma" : "") . ")";
					$param['d'.$i.'sigla'] = $oferecimento[0];
					if($oferecimento[1] != '*')
						$param['o'.$i.'turma'] = $oferecimento[1];
					$i++;
				}
				$qrs[] = "O.periodo = :periodo AND (".implode(" OR ", $mts).")";
				$usou_periodo = true;
			}
			unset($param['oferecimentos']);
		} else
			unset($param['oferecimentos']);
		if((isset($param['amigos'])) && ($param['amigos'] === true)) {
			$jns[] = " INNER JOIN U.amigos AS UA";
			$qrs[] = " UA.amigo = :id_usuario";
			unset($param['amigos']);
		}

		if($usou_periodo === false)
			unset($param['periodo']);

		$joins = (count($jns) > 0) ? implode(" ", $jns) : null;
		$where = (count($qrs) > 0) ? " WHERE ".implode(" AND ", $qrs) : "";

		if($total !== null) {
			$dqlt = "SELECT COUNT(DISTINCT A.ra) FROM ".get_class()." AS A ".$joins.$where;
			$queryt = self::_EM()->createQuery($dqlt)->setParameters($param);
			if((defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
				$queryt->useResultCache(true, CONFIG_RESULT_CACHE_TTL);
			$total = $queryt->getSingleScalarResult();
		}

		$extra_select = "";
		if($ordem == 'A.ra ASC' || $ordem == 'A.ra DESC') {
			$extra_select =  ", (CASE WHEN A.ra<500000 THEN A.ra+1000000 ELSE A.ra END) AS HIDDEN ORD ";
			$ordem = ($ordem == 'A.ra ASC') ? "ORD ASC" : "ORD DESC";
		}

		$dql = "SELECT DISTINCT A".$extra_select." FROM ".get_class()." AS A ".$joins.$where." ORDER BY ".$ordem;
		$query = self::_EM()->createQuery($dql)->setParameters($param);
		if($limit > 0)
			$query->setMaxResults($limit);
		if($start > -1)
			$query->setFirstResult($start);
		if((defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$query->useResultCache(true, CONFIG_RESULT_CACHE_TTL);
		return $query->getResult();
	}

	/**
	 * @param $q
	 * @param null $ordem
	 * @param null $total
	 * @param int $limit
	 * @param int $start
	 * @return Aluno[]
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public static function Consultar_Simples($q, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		// ToDo: Pegar nome da tabela das annotations
		$limit = intval($limit);
		$start = intval($start);
		if(preg_match('/^[\d]+$/i', $q) > 0) {
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC')
				$ordem = ($ordem != 'rank DESC') ? 'A.ra ASC' : 'A.ra DESC';
			if($ordem == 'A.ra ASC' || $ordem == 'A.ra DESC') {
				$extra_select =  ", (CASE WHEN A.`ra`<500000 THEN A.`ra`+1000000 ELSE A.`ra` END) AS `ordem`";
				$ordem = ($ordem == 'A.ra ASC') ? "`ordem` ASC" : "`ordem` DESC";
			} else
				$extra_select = "";
			$q = (strlen($q) == 6) ? intval($q) : '%'.$q.'%';
			$w = (strlen($q) == 6) ? "A.`ra` = :q" : "A.`ra` LIKE :q";

			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_alunos` AS A WHERE ".$w;
			$sql = "SELECT A.*".$extra_select." FROM `gde_alunos` AS A WHERE ".$w." ORDER BY ".$ordem;
			if($limit > 0) {
				if($start > 0)
					$sql .= " LIMIT ".$start.",".$limit;
				else
					$sql .= " LIMIT ".$limit;
			}
		} elseif((CONFIG_FTS_ENABLED === false) || (mb_strlen($q) < CONFIG_FT_MIN_LENGTH)) {
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC')
				$ordem = ($ordem == 'rank DESC') ? 'A.`nome` ASC' : 'A.`nome` DESC';
			if($ordem == 'A.ra ASC' || $ordem == 'A.ra DESC') {
				$extra_select =  ", (CASE WHEN A.`ra`<500000 THEN A.`ra`+1000000 ELSE A.`ra` END) AS `ordem`";
				$ordem = ($ordem == 'A.ra ASC') ? "`ordem` ASC" : "`ordem` DESC";
			} else
				$extra_select = "";
			if(CONFIG_FTS_ENABLED === false)
				$q = '%'.str_replace(' ', '%', $q).'%';
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_alunos` AS A WHERE A.`nome` LIKE :q";
			$sql = "SELECT A.*".$extra_select." FROM `gde_alunos` AS A WHERE A.`nome` LIKE :q ORDER BY ".$ordem." LIMIT ".$start.",".$limit;
		} else {
			$q = preg_replace('/(\w{'.CONFIG_FT_MIN_LENGTH.',})/', '+$1*', $q);
			if($ordem == null)
				$ordem = 'rank DESC';
			if($ordem == 'rank ASC' || $ordem == 'rank DESC') {
				$extra_select = ", MATCH(`nome`) AGAINST(:q) AS `rank`";
				if($ordem == 'rank ASC')
					$ordem .= ', `nome` DESC';
				else
					$ordem .= ', `nome` ASC';
			} elseif($ordem == 'A.ra ASC' || $ordem == 'A.ra DESC') {
				$extra_select =  ", (CASE WHEN A.`ra`<500000 THEN A.`ra`+1000000 ELSE A.`ra` END) AS `ordem`";
				$ordem = ($ordem == 'A.ra ASC') ? "`ordem` ASC" : "`ordem` DESC";
			} else
				$extra_select = "";
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_alunos` AS A WHERE MATCH(A.`nome`) AGAINST(:q IN BOOLEAN MODE)";
			$sql = "SELECT A.*".$extra_select." FROM `gde_alunos` AS A WHERE MATCH(A.`nome`) AGAINST(:q IN BOOLEAN MODE) ORDER BY ".$ordem;
			if($limit > 0) {
				if($start > 0)
					$sql .= " LIMIT ".$start.",".$limit;
				else
					$sql .= " LIMIT ".$limit;
			}
		}

		if($total !== null) {
			$rsmt = new ResultSetMappingBuilder(self::_EM());
			$rsmt->addScalarResult('total', 'total');
			$queryt = self::_EM()->createNativeQuery($sqlt, $rsmt);
			$queryt->setParameter('q', $q);
			if((defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
				$queryt->useResultCache(true, CONFIG_RESULT_CACHE_TTL);
			$total = $queryt->getSingleScalarResult();
		}

		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata(get_class(), 'A');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('q', $q);
		if((defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$query->useResultCache(true, CONFIG_RESULT_CACHE_TTL);
		return $query->getResult();
	}

	/**
	 * @param bool $formatado
	 * @return int|string
	 */
	public function getRA($formatado = false) {
		if(($formatado) && ($this->ra == null))
			return "-";
		return ($formatado) ? sprintf("%06d", $this->ra) : $this->ra;
	}

	/**
	 * @param bool $nome
	 * @return null|string
	 */
	public function getNivel($nome = false) {
		$nivel = parent::getNivel(false);
		if($nivel == null)
			return null;
		return (($nome) && (isset(self::$_niveis[$nivel])))
			? self::$_niveis[$nivel]
			: $nivel;
	}

	/**
	 * @param bool $nome
	 * @return null|string
	 */
	public function getNivel_Pos($nome = false) {
		$nivel = parent::getNivel_Pos(false);
		if($nivel == null)
			return null;
		return (($nome) && (isset(self::$_niveis[$nivel])))
			? self::$_niveis[$nivel]
			: $nivel;
	}

	/**
	 * getOferecimentos
	 *
	 * Retorna a lista de Oferecimentos deste Aluno, opcionalmente filtrada por $periodo ou $niveis
	 *
	 * @param integer|null $periodo
	 * @param string[] $niveis
	 * @param bool $formatado
	 * @param bool $links
	 * @return ArrayCollection|Oferecimento[]|string
	 */
	public function getOferecimentos($periodo = null, $niveis = array(), $formatado = false, $links = true) {
		if($niveis == Disciplina::NIVEL_GRAD)
			$niveis = Disciplina::$NIVEIS_GRAD;
		elseif(is_array($niveis) === false)
			$niveis = array($niveis);

		if(($periodo == null) && (count($niveis) == 0))
			$Oferecimentos = parent::getOferecimentos();
		//elseif($this->oferecimentos->isInitialized()) {
		else {
			// ToDo: Melhorar isto. Eu nao posso usar o queryBuilder pq se o Aluno nao tem Oferecimentos
			// A collection nunca eh initialized!
			// Nao posso usar o Query Builder pois a colecao ja foi carregada do DB
			$Oferecimentos = parent::getOferecimentos()->filter(function ($Oferecimento) use ($periodo, $niveis) {
				return (
					(($periodo === null) || ($Oferecimento->getPeriodo()->getId_Periodo() == $periodo)) &&
					((count($niveis) == 0) || (in_array($Oferecimento->getDisciplina()->getNivel(false), $niveis)))
				);
			});
		}
		/*} else {
			$qb = self::_EM()->createQueryBuilder()
				->select('o')
				->from('GDE\\Oferecimento', 'o')
				->join('o.alunos', 'a')
				->where('a.ra = :ra')
				->setParameter('ra', $this->getRA(false));
			if($periodo != null) {
				$qb->andWhere('o.periodo = :periodo')
					->setParameter('periodo', $periodo);
			}
			if(count($niveis) > 0) {
				$qb->join('o.disciplina', 'd')
					->andWhere('d.nivel IN (:niveis)')
					->setParameter('niveis', $niveis);
			}
			$Oferecimentos = $qb->getQuery()->getResult();
		}*/

		if($formatado === false)
			return $Oferecimentos;
		else {
			$lista = array();
			foreach($Oferecimentos as &$Oferecimento) {
				$lista[] = ($links)
					? "<a href=\"" . CONFIG_URL . "oferecimento/" .$Oferecimento->getID() . "/\" title=\"" . $Oferecimento->getDisciplina(true)->getNome(true) . "\">" .
						$Oferecimento->getSigla(true) . $Oferecimento->getTurma() . "</a> (" . $Oferecimento->getDisciplina(true)->getCreditos(true) . ")"
					: $Oferecimento->getSigla(true) . $Oferecimento->getTurma(true) . " (" . $Oferecimento->getDisciplina(true)->getCreditos(true) . ")";
			}
			return (count($lista) > 0) ? implode(", ", $lista) : '-';
		}
	}

	/**
	 * getTrancados
	 *
	 * Retorna a lista de Oferecimentos trancados deste Aluno, opcionalmente filtrada por $periodo ou $niveis
	 *
	 * @param integer|null $periodo
	 * @param string[] $niveis
	 * @param bool $formatado
	 * @return ArrayCollection|Oferecimento[]|string
	 */
	public function getTrancados($periodo = null, $niveis = array(), $formatado = false) {
		if($niveis == self::NIVEL_GRAD)
			$niveis = array_keys(self::$_niveis_grad);
		elseif(is_array($niveis) === false)
			$niveis = array($niveis);

		if(($periodo == null) && (count($niveis) == 0))
			$Trancados = parent::getTrancados();
		else {
			$qb = self::_EM()->createQueryBuilder()
				->select('o')
				->from('GDE\\Oferecimento', 'o')
				->join('o.alunos_trancados', 'a')
				->where('a.ra = :ra')
				->setParameter('ra', $this->getRA(false));
			if($periodo != null) {
				$qb->join('o.periodo', 'p')
					->andWhere('p.id_periodo = :periodo')
					->setParameter('periodo', $periodo);
			}
			if(count($niveis) > 0) {
				$qb->join('o.disciplina', 'd')
					->andWhere('d.nivel IN (:niveis)')
					->setParameter('niveis', $niveis);
			}
			$Trancados = $qb->getQuery()->getResult();
		}

		if($formatado === false)
			return $Trancados;
		else {
			$lista = array();
			foreach($Trancados as $Trancado)
				$lista[] = "<a href=\"".CONFIG_URL."oferecimento/".$Trancado->getID()."/\" title=\"".$Trancado->getDisciplina(true)->getNome(true)."\">".
					$Trancado->getSigla(true).$Trancado->getTurma(true)."</a> (".$Trancado->getDisciplina()->getCreditos(true).")";
			return (count($lista) > 0) ? implode(", ", $lista) : '-';
		}
	}

	/**
	 * Monta_Horario
	 *
	 * Monta um array de horario deste Aluno, opcionalmente filtrada por $periodo ou $niveis
	 *
	 * @param integer|null $periodo
	 * @param string[] $niveis
	 * @return array
	 */
	public function Monta_Horario($periodo = null, $niveis = array()) {
		if($niveis == self::NIVEL_GRAD)
			$niveis = array_keys(self::$_niveis_grad);
		elseif(is_array($niveis) === false)
			$niveis = array($niveis);
		$Lista = array();
		foreach($this->getOferecimentos($periodo, $niveis) as $Oferecimento)
			foreach($Oferecimento->getDimensoes() as $Dimensao)
				$Lista[$Dimensao->getDia()][$Dimensao->getHorario()][] = array($Oferecimento, $Dimensao->getSala(true)->getNome(true));
		return $Lista;
	}

	/**
	 * Creditos_Atuais
	 *
	 * Retorna o numero de creditos deste Aluno, opcionalmente filtrada por $periodo ou $niveis
	 *
	 * @param null $periodo
	 * @param array $niveis
	 * @return mixed
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public function Creditos_Atuais($periodo = null, $niveis = array()) {
		if(!is_array($niveis))
			$niveis = array($niveis);

		if(is_object($periodo))
			$periodo = $periodo->getID();

		$dql = 'SELECT SUM(D.creditos) FROM GDE\\Aluno AS A '.
			'INNER JOIN A.oferecimentos AS O INNER JOIN O.disciplina AS D '.
			'WHERE A.ra = ?1 AND O.periodo = ?2';

		if(count($niveis) > 0)
			$dql .= ' AND D.nivel IN (?3)';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getRA(false))
			->setParameter(2, $periodo);

		if(count($niveis) > 0)
			$query->setParameter(3, $niveis);

		return $query->getSingleScalarResult();
	}

	/**
	 * Creditos_Trancados
	 *
	 * Retorna o numero de creditos trancados deste Aluno, opcionalmente filtrada por $periodo ou $niveis
	 *
	 * @param null $periodo
	 * @param array $niveis
	 * @return mixed
	 */
	public function Creditos_Trancados($periodo = null, $niveis = array()) {
		$creditos = 0;
		foreach($this->getTrancados($periodo, $niveis) as $Oferecimento)
			$creditos += $Oferecimento->getDisciplina()->getCreditos();
		return $creditos;
	}

	/**
	 * Cursou
	 *
	 * Determina se este Aluno cursou $Disciplina
	 *
	 * @param Disciplina $Disciplina
	 * @return bool
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public function Cursou(Disciplina $Disciplina) {
		$dql = 'SELECT COUNT(O.id_oferecimento) FROM GDE\\Aluno AS A '.
			'INNER JOIN A.oferecimentos AS O '.
			'INNER JOIN O.disciplina AS D '.
			'WHERE A.ra = ?1 AND D.sigla = ?2';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getRA(false))
			->setParameter(2, $Disciplina->getSigla());

		return ($query->getSingleScalarResult() > 0);
	}

	/**
	 * Trancou
	 *
	 * Determina se este Aluno trancou $Disciplina
	 *
	 * @param Disciplina $Disciplina
	 * @return bool
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public function Trancou(Disciplina $Disciplina) {
		$dql = 'SELECT COUNT(O.id_oferecimento) FROM GDE\\Aluno AS A '.
			'INNER JOIN A.trancados AS O '.
			'INNER JOIN O.disciplina AS D '.
			'WHERE A.ra = ?1 AND D.sigla = ?2';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getRA(false))
			->setParameter(2, $Disciplina->getSigla());

		return ($query->getSingleScalarResult() > 0);
	}

	/**
	 * Cursou_Com
	 *
	 * Determina se este Aluno ja cursou com $Professor, opcionalmente $Disciplina
	 *
	 * @param Professor $Professor
	 * @param Disciplina $Disciplina
	 * @return bool
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public function Cursou_Com(Professor $Professor, Disciplina $Disciplina = null) {
		$dql = 'SELECT COUNT(O.id_oferecimento) FROM GDE\\Aluno AS A '.
			'INNER JOIN A.oferecimentos AS O ';
		if($Disciplina !== null)
			$dql .= 'INNER JOIN O.disciplina AS D ';
		$dql .= 'WHERE A.ra = ?1 AND (?2 MEMBER OF O.professores)';

		if($Disciplina !== null)
			$dql .= ' AND D.sigla = ?3';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getRA(false))
			->setParameter(2, $Professor->getID());

		if($Disciplina !== null)
			$query->setParameter(3, $Disciplina->getSigla());

		return ($query->getSingleScalarResult() > 0);
	}

	/**
	 * Cursou_Com
	 *
	 * Determina se este Aluno ja trancou com $Professor, opcionalmente $Disciplina
	 *
	 * @param Professor $Professor
	 * @param Disciplina $Disciplina
	 * @return bool
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public function Trancou_Com(Professor $Professor, Disciplina $Disciplina = null) {
		$dql = 'SELECT COUNT(O.id_oferecimento) FROM GDE\\Aluno AS A '.
			'INNER JOIN A.trancados AS O ';
		if($Disciplina !== null)
			$dql .= 'INNER JOIN O.disciplina AS D ';
		$dql .= 'WHERE A.ra = ?1 AND (?2 MEMBER OF O.professores)';

		if($Disciplina !== null)
			$dql .= ' AND D.sigla = ?3';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getRA(false))
			->setParameter(2, $Professor->getID());

		if($Disciplina !== null)
			$query->setParameter(3, $Disciplina->getSigla());

		return ($query->getSingleScalarResult() > 0);
	}

}
