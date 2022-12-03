<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Oferecimento
 *
 * @ORM\Table(
 *   name="gde_oferecimentos",
 *   indexes={
 *     @ORM\Index(name="turma", columns={"turma"}),
 *     @ORM\Index(name="fechado", columns={"fechado"}),
 *     @ORM\Index(name="periodo_fechado", columns={"id_periodo", "fechado"})
 *   },
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="disciplina_periodo_turma", columns={"id_disciplina", "id_periodo", "turma"})
 *   }
 * )
 * @ORM\Entity
 */
class Oferecimento extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_oferecimento;

	/**
	 * @var Disciplina
	 *
	 * @ORM\ManyToOne(targetEntity="Disciplina", inversedBy="oferecimentos", cascade={"persist"})
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $disciplina;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo", referencedColumnName="id_periodo")
	 */
	protected $periodo;

	/**
	 * @var ArrayCollection|Professor[]
	 *
	 * @ORM\ManyToMany(targetEntity="Professor", inversedBy="oferecimentos")
	 * @ORM\JoinTable(name="gde_r_oferecimentos_professores",
	 *      joinColumns={@ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_professor", referencedColumnName="id_professor")}
	 * )
	 */
	protected $professores;

	/**
	 * @var ArrayCollection|OferecimentoReserva[]
	 *
	 * @ORM\OneToMany(targetEntity="OferecimentoReserva", mappedBy="oferecimento", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	protected $reservas;

	/**
	 * @var ArrayCollection|Dimensao[]
	 *
	 * @ORM\ManyToMany(targetEntity="Dimensao", inversedBy="oferecimentos")
	 * @ORM\JoinTable(name="gde_r_oferecimentos_dimensoes",
	 *      joinColumns={@ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_dimensao", referencedColumnName="id_dimensao")}
	 * )
	 */
	protected $dimensoes;

	/**
	 * @var ArrayCollection|Aluno[]
	 *
	 * @ORM\ManyToMany(targetEntity="Aluno", mappedBy="oferecimentos")
	 */
	protected $alunos;

	/**
	 * @var ArrayCollection|Aluno[]
	 *
	 * @ORM\ManyToMany(targetEntity="Aluno", mappedBy="trancados")
	 */
	protected $alunos_trancados;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=5, nullable=false)
	 */
	protected $turma;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"default"=0}, nullable=false)
	 */
	protected $vagas = 0;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $fechado = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $pagina;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"default"=0}, nullable=false)
	 */
	protected $matriculados = 0;

	const SALA_DESCONHECIDA = '????';

	// ToDo: Remover isto!
	static $ordens_nome = array('Relev&acirc;ncia', 'Sigla e Turma', 'Nome', 'Professor(es)');
	static $ordens_inte = array('rank', 'DI.sigla', 'DI.nome', 'P.nome');

	/**
	 * Por_Unique
	 *
	 * Encontra um Oferecimento por disciplina, turma e periodo
	 *
	 * @param Disciplina $Disciplina
	 * @param $turma
	 * @param Periodo $Periodo
	 * @return false|null|Oferecimento
	 */
	public static function Por_Unique(Disciplina $Disciplina, $turma, Periodo $Periodo) {
		return self::FindOneBy(array(
			'disciplina' => $Disciplina,
			'turma' => $turma,
			'periodo' => $Periodo
		));
	}

	/**
	 * Por_Disciplina_Periodo
	 *
	 * Encontra um Oferecimento por disciplina, turma e periodo
	 *
	 * @param Disciplina $Disciplina
	 * @param Periodo $Periodo
	 * @return false|null|Oferecimento
	 */
	public static function Por_Disciplina_Periodo(Disciplina $Disciplina, Periodo $Periodo) {
		return self::FindBy(array(
			'disciplina' => $Disciplina,
			'periodo' => $Periodo
		));
	}

	/**
	 * Consultar
	 *
	 * Efetua uma consulta por Oferecimentos
	 *
	 * @param $param
	 * @param null $ordem
	 * @param null $total
	 * @param int $limit
	 * @param int $start
	 * @return Oferecimento[]
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public static function Consultar($param, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		$qrs = $jns = array();
		$join_disciplina = false;
		if($ordem == null)
			$ordem = "O.id_oferecimento ASC";
		if(($ordem == "DI.sigla ASC") || ($ordem == "DI.sigla DESC")) {
			$ordem = ($ordem != "DI.sigla DESC") ? "DI.sigla ASC, O.turma ASC" : "DI.sigla DESC, O.turma DESC";
			$join_disciplina = true;
		}
		if(!empty($param['sigla'])) {
			$join_disciplina = true;
			if(strlen($param['sigla']) == 5) {
				$qrs[] = "DI.sigla = :sigla";
			} else {
				$qrs[] = "DI.sigla LIKE :sigla";
				$param['sigla'] = '%' . $param['sigla'] . '%';
			}
		}
		if(!empty($param['disciplina'])) {
			$join_disciplina = true;
			$qrs[] = "DI.id_disciplina = :disciplina";
		}
		if(!empty($param['periodo'])) {
			$qrs[] = "O.periodo = :periodo";
		}
		if(isset($param['fechado'])) {
			$qrs[] = "O.fechado = :fechado";
		}
		if((!empty($param['sigla'])) || (!empty($param['nome'])) || (!empty($param['creditos'])) || (!empty($param['instituto'])) || (!empty($param['nivel'])) || ($ordem == "DI.nome ASC") || ($ordem == "DI.nome DESC"))
			$join_disciplina = true;
		if($join_disciplina === true)
			$jns[] = "JOIN O.disciplina AS DI";
		if(!empty($param['nome'])) {
			$qrs[] = "DI.nome LIKE :nome";
			$param['nome'] = '%'.$param['nome'].'%';
		}
		if(!empty($param['creditos']))
			$qrs[] = "DI.creditos = :creditos";
		if(!empty($param['instituto']))
			$qrs[] = "DI.instituto = :instituto";
		if(!empty($param['nivel'])) {
			if(is_array($param['nivel']))
				$qrs[] = "DI.nivel IN (:nivel)";
			else
				$qrs[] = "DI.nivel = :nivel";
		}
		if(isset($param['turma']))
			$qrs[] = "O.turma = :turma";
		if(!empty($param['professor']))
			$qrs[] = "P.nome LIKE :professor";
		if(!empty($param['professor']) || ($ordem == "P.nome ASC") || ($ordem == "P.nome DESC"))
			$jns[] = "JOIN O.professores AS P";
		if((!empty($param['dia'])) || (!empty($param['horario'])) || (!empty($param['sala'])))
			$jns[] = "JOIN O.dimensoes AS D";
		if(!empty($param['dia']))
			$qrs[] = "D.dia = :dia";
		if(!empty($param['horario']))
			$qrs[] = "D.horario = :horario";
		if(!empty($param['sala'])) {
			$jns[] = "JOIN D.sala AS S";
			$qrs[] = "S.nome LIKE :sala";
		}
		$joins = (count($jns) > 0) ? implode(" ", $jns) : null;
		$where = (count($qrs) > 0) ? " WHERE ".implode(" AND ", $qrs) : "";
		if($total !== null) {
			$dqlt = "SELECT COUNT(DISTINCT O.id_oferecimento) FROM ".get_class()." AS O ".$joins.$where;
			$queryt = self::_EM()->createQuery($dqlt)->setParameters($param);
			if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
				$queryt->enableResultCache(CONFIG_RESULT_CACHE_TTL);
			$total = $queryt->getSingleScalarResult();
		}
		$dql = "SELECT DISTINCT O".(($join_disciplina) ? ", DI" : "")." FROM ".get_class()." AS O ".$joins.$where." ORDER BY ".$ordem;
		$query = self::_EM()->createQuery($dql)->setParameters($param);
		if($limit > 0)
			$query->setMaxResults($limit);
		if($start > -1)
			$query->setFirstResult($start);
		if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$query->enableResultCache(CONFIG_RESULT_CACHE_TTL);
		return $query->getResult();
	}

	/**
	 * @param $q
	 * @param null $ordem
	 * @param null $total
	 * @param int $limit
	 * @param int $start
	 * @return Oferecimento[]
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public static function Consultar_Simples($q, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		// ToDo: Pegar nome da tabela das annotations
		$limit = intval($limit);
		$start = intval($start);
		if((preg_match('/^[a-z ]{2}\d{3}$/i', $q) > 0) || (mb_strlen($q) < CONFIG_FT_MIN_LENGTH)) {
			$extra_join = "";
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC') {
				$ordem = ($ordem != 'rank DESC')
					? 'O.`id_periodo` ASC, DI.`sigla` DESC, O.`turma` DESC'
					: 'O.`id_periodo` DESC, DI.`sigla` ASC, O.`turma` ASC';
			} elseif($ordem == "P.nome ASC" || $ordem == "P.nome DESC")
				$extra_join = " JOIN `gde_r_oferecimentos_professores` AS OP ON (OP.`id_oferecimento` = O.`id_oferecimento`) JOIN `gde_professores` AS P ON (P.`id_professor` = OP.`id_professor`) ";
			elseif(($ordem == "DI.sigla ASC") || ($ordem == "DI.sigla DESC")) {
				$ordem = ($ordem != "DI.sigla DESC")
					? "DI.`sigla` ASC, O.`turma` ASC"
					: "DI.`sigla` DESC, O.`turma` DESC";
			} else
				$extra_join = "";
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_oferecimentos` AS O INNER JOIN `gde_disciplinas` AS DI ON (O.`id_disciplina` = DI.`id_disciplina`)" . $extra_join . " WHERE DI.`sigla` LIKE :q";
			$sql = "SELECT O.* FROM `gde_oferecimentos` AS O INNER JOIN `gde_disciplinas` AS DI ON (O.`id_disciplina` = DI.`id_disciplina`)" . $extra_join . " WHERE DI.`sigla` LIKE :q ORDER BY " . $ordem;
			if($limit > 0) {
				if($start > 0)
					$sql .= " LIMIT " . $start . "," . $limit;
				else
					$sql .= " LIMIT " . $limit;
			}
			$q = $q . '%';
		} elseif(CONFIG_FTS_ENABLED === false) {
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC') {
				$ordem = ($ordem != 'rank DESC')
					? 'O.`id_periodo` ASC, DI.`sigla` DESC, O.`turma` DESC'
					: 'O.`id_periodo` DESC, DI.`sigla` ASC, O.`turma` ASC';
			} elseif(($ordem == "DI.sigla ASC") || ($ordem == "DI.sigla DESC")) {
				$ordem = ($ordem != "DI.sigla DESC")
					? "DI.`sigla` ASC, O.`turma` ASC"
					: "DI.`sigla` DESC, O.`turma` DESC";
			}
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_oferecimentos` AS O INNER JOIN `gde_disciplinas` AS DI ON (O.`id_disciplina` = DI.`id_disciplina`) LEFT JOIN `gde_r_oferecimentos_professores` AS OP ON (OP.`id_oferecimento` = O.`id_oferecimento`) LEFT JOIN `gde_professores` AS P ON (P.`id_professor` = OP.`id_professor`) WHERE DI.`sigla` LIKE :q OR DI.`nome` LIKE :q OR P.`nome` LIKE :q";
			$sql = "SELECT O.* FROM `gde_oferecimentos` AS O INNER JOIN `gde_disciplinas` AS DI ON (O.`id_disciplina` = DI.`id_disciplina`) LEFT JOIN `gde_r_oferecimentos_professores` AS OP ON (OP.`id_oferecimento` = O.`id_oferecimento`) LEFT JOIN `gde_professores` AS P ON (P.`id_professor` = OP.`id_professor`) WHERE DI.`sigla` LIKE :q OR DI.`nome` LIKE :q OR P.`nome` LIKE :q ORDER BY " . $ordem;
			if($limit > 0) {
				if($start > 0)
					$sql .= " LIMIT " . $start . "," . $limit;
				else
					$sql .= " LIMIT " . $limit;
			}
			$q = '%' . str_replace(' ', '%', $q) . '%';
		} else {
			$q = Util::String_FTS($q);
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC') {
				$ordem = ($ordem != 'rank DESC')
					? "`rank` ASC, O.id_periodo ASC, O.`sigla` DESC, O.`turma` DESC"
					: "`rank` DESC, O.`id_periodo` DESC, O.`sigla` ASC, O.`turma` ASC";
				$extra_select1 = ", MATCH(P.`nome`) AGAINST(:q) AS `rank`, DI.`sigla` AS `sigla`";
				$extra_select2 = ", MATCH(DI.`sigla`, DI.`nome`) AGAINST(:q) AS `rank`, DI.`sigla` AS `sigla`";
				$extra_join1 = "JOIN `gde_disciplinas` AS DI ON (O.`id_disciplina` = DI.`id_disciplina`) ";
				$extra_join2 = "JOIN `gde_r_oferecimentos_professores` AS OP ON (OP.`id_oferecimento` = O.`id_oferecimento`) JOIN `gde_professores` AS P ON (P.`id_professor` = OP.`id_professor`) ";
			} elseif($ordem == "DI.nome ASC" || $ordem == "DI.nome DESC") {
				$extra_select1 = $extra_select2 = ", DI.`nome` AS `disciplina`";
				$extra_join1 = "JOIN `gde_disciplinas` AS DI ON (O.`id_disciplina` = DI.`id_disciplina`) ";
				$extra_join2 = "";
				$ordem = ($ordem != "DI.nome DESC") ? "O.`disciplina` ASC" : "O.`disciplina` DESC";
			} elseif($ordem == "P.nome ASC" || $ordem == "P.nome DESC") {
				$extra_select1 = $extra_select2 = ", P.`nome` AS `professor`";
				$extra_join1 = "";
				$extra_join2 = "JOIN `gde_r_oferecimentos_professores` AS OP ON (OP.`id_oferecimento` = O.`id_oferecimento`) JOIN `gde_professores` AS P ON (P.`id_professor` = OP.`id_professor`) ";
				$ordem = ($ordem != "P.nome DESC")
					? "O.`professor` ASC" : "O.`professor` DESC";
			} elseif(($ordem == "DI.sigla ASC") || ($ordem == "DI.sigla DESC")) {
				$extra_select1 = $extra_select2 = ", DI.`sigla` AS `sigla`";
				$extra_join1 = "JOIN `gde_disciplinas` AS DI ON (O.`id_disciplina` = DI.`id_disciplina`) ";
				$ordem = ($ordem != "DI.sigla DESC")
					? "O.`sigla` ASC, O.`turma` ASC"
					: "O.`sigla` DESC, O.`turma` DESC";
				$extra_join2 = "";
			} else
				$extra_select1 = $extra_select2 = $extra_join1 = $extra_join2 = "";
			if($total !== null)
				$sqlt = "SELECT A.`total` + B.`total` AS `total` FROM (SELECT COUNT(*) AS total FROM `gde_oferecimentos` AS O INNER JOIN `gde_r_oferecimentos_professores` AS OP ON (OP.`id_oferecimento` = O.`id_oferecimento`) INNER JOIN `gde_professores` AS P ON (P.`id_professor` = OP.`id_professor`) WHERE MATCH(P.`nome`) AGAINST(:q IN BOOLEAN MODE)) AS A, (SELECT COUNT(*) AS `total` FROM `gde_oferecimentos` AS O INNER JOIN `gde_disciplinas` AS DI ON (DI.`id_disciplina` = O.`id_disciplina`) WHERE MATCH(DI.`sigla`, DI.`nome`) AGAINST(:q IN BOOLEAN MODE)) AS B";
			$sql = "SELECT O.* FROM ((SELECT O.*".$extra_select1." FROM `gde_oferecimentos` AS O ".$extra_join1."INNER JOIN `gde_r_oferecimentos_professores` AS OP ON (OP.`id_oferecimento` = O.`id_oferecimento`) INNER JOIN `gde_professores` AS P ON (P.`id_professor` = OP.`id_professor`) WHERE MATCH(P.`nome`) AGAINST(:q IN BOOLEAN MODE)) UNION ALL (SELECT O.*".$extra_select2." FROM `gde_oferecimentos` AS O ".$extra_join2."INNER JOIN `gde_disciplinas` AS DI ON (DI.`id_disciplina` = O.`id_disciplina`) WHERE MATCH(DI.`sigla`, DI.`nome`) AGAINST(:q IN BOOLEAN MODE))) AS O ORDER BY ".$ordem;
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
			if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
				$queryt->enableResultCache(CONFIG_RESULT_CACHE_TTL);
			$total = $queryt->getSingleScalarResult();
		}

		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata(get_class(), 'O');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('q', $q);
		if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$query->enableResultCache(CONFIG_RESULT_CACHE_TTL);
		return $query->getResult();
	}

	/**
	 * getSigla
	 *
	 * Retorna a sigla da Disciplina deste Oferecimento
	 *
	 * @param bool $html
	 * @return null
	 */
	public function getSigla($html = false) {
		if($this->getDisciplina(false) === null)
			return null;
		return $this->getDisciplina()->getSigla($html);
	}

	/**
	 * getProfessores
	 *
	 * Retorna a lista de Professores deste Oferecimento
	 *
	 * @param bool $formatado
	 * @param string $cola
	 * @return ArrayCollection|Professor[]|string
	 */
	public function getProfessores($formatado = false, $cola = ', ') {
		if($formatado === false)
			return parent::getProfessores();

		$Professores = parent::getProfessores();
		if(count($Professores) == 0)
			return "Desconhecido";

		$professores = array();
		foreach($Professores as $Professor)
			$professores[] = '<a href="'.CONFIG_URL.'perfil/?professor='.$Professor->getID().'">'.$Professor->getNome(true).'</a>';
		return implode($cola, $professores);
	}

	/**
	 * getReservas
	 *
	 * Retorna a lista de Reservas deste Oferecimento, opcionalmente formatadas (HTML)
	 *
	 * @param bool $formatado
	 * @return OferecimentoReserva[]|string
	 */
	public function getReservas($formatado = false) {
		$Reservas = parent::getReservas();
		if($formatado === false)
			return $Reservas;
		else {
			if($Reservas->count() == 0)
				return 'N&atilde;o Dispon&iacute;vel';
			if($Reservas->first()->getCurso(false) === null)
				return 'Sem Reservas';
			else {
				$lista = array();
				foreach($Reservas as $Reserva)
					$lista[] = $Reserva->getCurso(true)->getNome(true)." (".$Reserva->getCurso(true)->getNumero(true).")".(($Reserva->getCatalogo(false) != null) ? " / ".$Reserva->getCatalogo(true) : null);
				return implode("<br />", $lista);
			}
		}
	}

	/**
	 * Monta_Horario
	 *
	 * Organiza as Dimensoes deste Oferecimento
	 *
	 * @return array
	 */
	public function Monta_Horario() {
		$Lista = array();
		foreach($this->getDimensoes() as $Dimensao)
			$Lista[$Dimensao->getDia()][$Dimensao->getHorario()] = $Dimensao->getSala(true)->getNome(true);
		return $Lista;
	}

	/**
	 * Lista_Horario
	 *
	 * Retorna uma lista das Dimensoes deste Oferecimento
	 *
	 * @param bool $cru
	 * @return array
	 */
	public function Lista_Horarios($cru = false) {
		$Lista = array();
		foreach($this->getDimensoes() as $Dimensao)
			$Lista[] = ($cru) ? $Dimensao->getDia().sprintf("%02d", $Dimensao->getHorario()) : array($Dimensao->getDia(), $Dimensao->getHorario(), $Dimensao->getSala(true)->getNome(true));
		return $Lista;
	}

	/**
	 * Formata_Horario
	 *
	 * Retorna um horario formatado para este Oferecimento
	 *
	 * @param $Horario
	 * @param $dia
	 * @param $horario
	 * @return string
	 */
	public static function Formata_Horario($Horario, $dia, $horario) {
		if((!array_key_exists($dia, $Horario)) || (!array_key_exists($horario, $Horario[$dia])))
			return '-';
		if(($Horario[$dia][$horario] == self::SALA_DESCONHECIDA) || ($Horario[$dia][$horario] == null))
			return ($Horario[$dia][$horario] != null) ? $Horario[$dia][$horario] : self::SALA_DESCONHECIDA;
		return "<a href=\"".CONFIG_URL."sala/".$Horario[$dia][$horario]."/\">".$Horario[$dia][$horario]."</a>";
	}

	/**
	 * Viola_Reserva
	 *
	 * Determina se $Usuario cursar este Oferecimento violaria alguma reserva
	 *
	 * @param Usuario $Usuario
	 * @return bool
	 */
	public function Viola_Reserva(Usuario $Usuario) {
		if(count($this->getReservas()) == 0)
			return false;
		foreach($this->getReservas() as $Reserva) {
			if(
				($Reserva->getCurso(false) === null) ||
				(
					($Reserva->getCurso(true)->getID() == $Usuario->getCurso(true)->getID()) &&
					(($Reserva->getCatalogo(false) == null) || ($Reserva->getCatalogo(false) == $Usuario->getCatalogo(false)))
				)
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Desistencias
	 *
	 * Retorna o numero de Alunos que trancaram este Oferecimento
	 *
	 * @return integer
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Desistencias() {
		$dqlt = "SELECT COUNT(A) FROM ".get_class()." AS O JOIN O.alunos_trancados AS A WHERE O.id_oferecimento = :id_oferecimento";
		$queryt = self::_EM()->createQuery($dqlt)->setParameters(array('id_oferecimento' => $this->getID()));
		if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$queryt->enableResultCache(CONFIG_RESULT_CACHE_TTL);
		return $queryt->getSingleScalarResult();
	}

	/**
	 * @param bool $agrupar
	 * @return array
	 */
	public function Eventos($agrupar = false) {
		$Lista = array();
		$fh = $lh = null;
		$Horarios = $this->Monta_Horario();
		ksort($Horarios);
		foreach($Horarios as $dia => $Resto) {
			ksort($Resto);
			$horarios = array_keys($Resto);
			sort($horarios);
			$ch = count($horarios);
			$fh = $lh = $horarios[0];
			$sala = $salan = strtoupper($Resto[$horarios[0]]);
			$j = 1;
			for($i = 1; $i < $ch; $i++) {
				if(($horarios[$i] == $fh + $j) && (($agrupar === true) || (strtoupper($Resto[$horarios[$i]]) == $sala))) {
					if(strtoupper($Resto[$horarios[$i]]) != $sala) {
						$sala = strtoupper($Resto[$horarios[$i]]);
						$salan .= '/'.$sala;
					}
					$lh = $horarios[$i];
					$j++;
				} else {
					$Lista[] = array('id' => $this->getID(), 'title' => $this->getSigla().' '.$this->getTurma().' '.$salan, 'start' => '2003-12-0'.($dia-1).'T'.sprintf("%02d", $fh).':00:00-03:00', 'end' => '2003-12-0'.($dia-1).'T'.sprintf("%02d", ($lh+1)).':00:00-03:00');
					$fh = $lh = $horarios[$i];
					$j = 1;
					$sala = strtoupper($Resto[$horarios[$i]]);
				}
			}
			$Lista[] = array('id' => $this->getID(), 'title' => $this->getSigla().' '.$this->getTurma().' '.$salan, 'start' => '2003-12-0'.($dia-1).'T'.sprintf("%02d", $fh).':00:00-03:00', 'end' => '2003-12-0'.($dia-1).'T'.sprintf("%02d", ($lh+1)).':00:00-03:00');
		}
		return $Lista;
	}

	public function Professores($html = false, $cola = ', ') {
		$professores = array();
		foreach($this->getProfessores(false) as $Professor)
			$professores[] = $Professor->getNome($html);
		return implode($cola, $professores);
	}

	public function Atualiza_Numero_Matriculados() {
		$this->setMatriculados($this->alunos->count());
	}

}
