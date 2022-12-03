<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Disciplina
 *
 * @ORM\Table(
 *  name="gde_disciplinas",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="sigla_nivel", columns={"sigla", "nivel"})
 *  },
 *  indexes={
 *     @ORM\Index(name="sigla_nome", columns={"sigla", "nome"}, flags={"fulltext"}),
 *     @ORM\Index(name="nome", columns={"nome"}),
 *     @ORM\Index(name="creditos", columns={"creditos"}),
 *     @ORM\Index(name="periodicidade", columns={"periodicidade"}),
 *     @ORM\Index(name="nivel", columns={"nivel"})
 *  }
 * )
 * @ORM\Entity
 */
class Disciplina extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_disciplina;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=5, nullable=false)
	 */
	protected $sigla;

	/**
	 * @var Instituto
	 *
	 * @ORM\ManyToOne(targetEntity="Instituto")
	 * @ORM\JoinColumn(name="id_instituto", referencedColumnName="id_instituto")
	 */
	protected $instituto;

	/**
	 * @var ArrayCollection|Oferecimento[]
	 *
	 * @ORM\OneToMany(targetEntity="Oferecimento", mappedBy="disciplina")
	 */
	protected $oferecimentos;

	/**
	 * @var ArrayCollection|PreConjunto[]
	 *
	 * @ORM\OneToMany(targetEntity="PreConjunto", mappedBy="disciplina", orphanRemoval=true)
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 * @ORM\OrderBy({"catalogo" = "ASC"})
	 */
	protected $pre_conjuntos;

	/**
	 * @var ArrayCollection|Equivalencia[]
	 *
	 * @ORM\OneToMany(targetEntity="Equivalencia", mappedBy="disciplina", orphanRemoval=true)
	 * @ORM\JoinColumn(name="id_disciplina", referencedColumnName="id_disciplina")
	 */
	protected $equivalencias;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $nome;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="smallint", options={"unsigned"=true}, nullable=true)
	 */
	protected $creditos;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $nivel;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $periodicidade;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"default"=0}, nullable=false)
	 */
	protected $parte = 0;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $ementa;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $bibliografia;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $quinzenal = false;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true, "default"=0}, nullable=false)
	 */
	protected $cursacoes = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true, "default"=0}, nullable=false)
	 */
	protected $reprovacoes = 0;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true, "default"=0}, nullable=false)
	 */
	protected $max_reprovacoes = 0;

	// Niveis
	const NIVEL_GRAD = 'G';
	const NIVEL_POS = 'P';
	const NIVEL_MP = 'S';
	public static $NIVEIS_GRAD = array(self::NIVEL_GRAD);
	public static $NIVEIS_POS = array(self::NIVEL_POS, self::NIVEL_MP);
	private static $_niveis = array(
		self::NIVEL_GRAD => 'Gradua&ccedil;&atilde;o / Tecnologia',
		self::NIVEL_POS => 'P&oacute;s-Gradua&ccedil;&atilde;o',
		self::NIVEL_MP => 'Mestrado Profissional'
	);

	// Periodicidades
	const PERIODICIDADE_DESCONHECIDA = 0;
	const PERIODICIDADE_PRIMEIRO = 1;
	const PERIODICIDADE_SEGUNDO = 2;
	const PERIODICIDADE_AMBOS = 5;
	const PERIODICIDADE_CRITERIO = 6;
	private static $_periodicidades = array(
		self::PERIODICIDADE_DESCONHECIDA => "Desconhecido",
		self::PERIODICIDADE_PRIMEIRO => "Primeiro Semestre do Ano",
		self::PERIODICIDADE_SEGUNDO => "Segundo Semestre do Ano",
		self::PERIODICIDADE_AMBOS => "Os Dois Semestres do Ano",
		self::PERIODICIDADE_CRITERIO => "A Crit&eacute;rio do Instituto"
	);

	const NOME_VAZIO = '(Desconhecido)';
	const CREDITOS_VAZIO = '?';

	// ToDo: Remover isto!
	static $ordens_nome = array('Relev&acirc;ncia', 'Sigla', 'Nome', 'Cr&eacute;ditos');
	static $ordens_inte = array('rank', 'D.sigla', 'D.nome', 'D.creditos');

	/**
	 * @return array
	 */
	public static function Listar_Periodicidades() {
		return self::$_periodicidades;
	}

	/**
	 * @return array
	 */
	public static function Listar_Niveis() {
		return self::$_niveis;
	}

	/**
	 * Por_Sigla
	 *
	 * Carrega uma Disciplina pela sigla e, opcionamente, nivel
	 *
	 * @param $sigla
	 * @param string|array|null $nivel
	 * @param bool $vazio
	 * @return Disciplina|null|false
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public static function Por_Sigla($sigla, $nivel = null, $vazio = true) {
		if(empty($nivel)) {
			// Se o nivel nao foi fornecido, pegamos a "primeira" encontrada
			$Disciplinas = self::FindBy(array('sigla' => $sigla));
			if(count($Disciplinas) > 0)
				return $Disciplinas[0];
			elseif($vazio == true) {
				$Disciplina = new self;
				$Disciplina->setSigla($sigla);
				return $Disciplina;
			} else
				return null;
		} elseif((is_array($nivel)) && (count($nivel) > 1)) {
			// Se temos uma lista de niveis, fazemos uma consulta e retornamos a primeira encontrada
			$total = null;
			$Disciplinas = self::Consultar(array(
				'sigla' => $sigla,
				'nivel' => $nivel
			), null, $total, 1);
			if(count($Disciplinas) == 0) {
				if($vazio === true) {
					$Disciplina = new self;
					$Disciplina->markReadOnly();
					$Disciplina->setSigla($sigla);
					return $Disciplina;
				} else
					return null;
			} else
				return $Disciplinas[0];
		} else {
			if(is_array(($nivel)))
				$nivel = $nivel[0];
			// Se temos nivel podemos fazer a busca por unique
			$Disciplina = self::FindOneBy(array('sigla' => $sigla, 'nivel' => $nivel));
			if(($Disciplina === null) && ($vazio === true)) {
				$Disciplina = new self;
				$Disciplina->markReadOnly();
				$Disciplina->setSigla($sigla);
				return $Disciplina;
			}
			return $Disciplina;
		}
	}

	/**
	 * @param $param
	 * @param null $ordem
	 * @param int $total
	 * @param int $limit
	 * @param int $start
	 * @param string $tipo
	 * @return Disciplina[]
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public static function Consultar($param, $ordem = null, &$total = null, $limit = -1, $start = -1, $tipo = 'AND') {
		$qrs = $jns = array();
		if($ordem == null)
			$ordem = "D.sigla ASC";
		if(isset($param['sigla'])) {
			if(strpos($param['sigla'], '-') !== false) {
				$qrs[] = "D.sigla LIKE :sigla";
				$param['sigla'] = preg_replace('/[\-]+/', '%', $param['sigla']);
			} elseif(strlen($param['sigla']) == 5)
				$qrs[] = "D.sigla = :sigla";
			else {
				$qrs[] = "D.sigla LIKE :sigla";
				$param['sigla'] = '%'.$param['sigla'].'%';
			}
		}
		if(isset($param['nome']))
			$qrs[] = "D.nome LIKE :nome";
		if(isset($param['nivel'])) {
			if(is_array($param['nivel']))
				$qrs[] = "D.nivel IN (:nivel)";
			else
				$qrs[] = "D.nivel = :nivel";
		}
		if(isset($param['instituto']))
			$qrs[] = "D.instituto = :instituto";
		if(isset($param['creditos']))
			$qrs[] = "D.creditos = :creditos";
		if(isset($param['periodicidade']))
			$qrs[] = "D.periodicidade = :periodicidade";
		if(isset($param['ementa'])) {
			$qrs[] = "D.ementa LIKE :ementa";
			$param['ementa'] = '%'.$param['ementa'].'%';
		}
		$joins = (count($jns) > 0) ? implode(" ", $jns) : null;
		$where = (count($qrs) > 0) ? " WHERE ".implode(" ".$tipo." ", $qrs) : "";

		if($total !== null) {
			$dqlt = "SELECT COUNT(DISTINCT D.sigla) FROM ".get_class()." AS D ".$joins.$where;
			$queryt = self::_EM()->createQuery($dqlt)->setParameters($param);
			if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
				$queryt->enableResultCache(CONFIG_RESULT_CACHE_TTL);
			$total = $queryt->getSingleScalarResult();
		}
		$dql = "SELECT DISTINCT D FROM ".get_class()." AS D ".$joins.$where." ORDER BY ".$ordem;
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
	 * @return Disciplina[]
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public static function Consultar_Simples($q, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		// ToDo: Pegar nome da tabela das annotations
		$limit = intval($limit);
		$start = intval($start);
		if((preg_match('/^[a-z ]{2}\d{3}$/i', $q) > 0) || (mb_strlen($q) < CONFIG_FT_MIN_LENGTH)) {
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC')
				$ordem = ($ordem == 'rank DESC') ? 'D.`sigla` ASC' : 'D.`sigla` DESC';
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_disciplinas` AS D WHERE D.`sigla` LIKE :q";
			$sql = "SELECT D.* FROM `gde_disciplinas` AS D WHERE D.`sigla` LIKE :q ORDER BY " . $ordem;
			if($limit > 0) {
				if($start > 0)
					$sql .= " LIMIT " . $start . "," . $limit;
				else
					$sql .= " LIMIT " . $limit;
			}
			$q = $q . '%';
		} elseif(CONFIG_FTS_ENABLED === false) {
			$q = '%' . str_replace(' ', '%', $q) . '%';
			if($ordem == null)
				$ordem = 'rank DESC';
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC')
				$ordem = ($ordem != 'rank DESC') ? 'D.`sigla` ASC' : 'D.`sigla` DESC';
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_disciplinas` AS D WHERE D.`sigla` LIKE :q OR D.`nome` LIKE :q";
			$sql = "SELECT D.* FROM `gde_disciplinas` AS D WHERE D.`sigla` LIKE :q OR D.`nome` LIKE :q ORDER BY ".$ordem;
			if($limit > 0) {
				if($start > 0)
					$sql .= " LIMIT ".$start.",".$limit;
				else
					$sql .= " LIMIT ".$limit;
			}
		} else {
			$q = Util::String_FTS($q);
			if($ordem == null)
				$ordem = 'rank DESC';
			if($ordem == 'rank ASC' || $ordem == 'rank DESC') {
				$extra_select = ", MATCH(D.`sigla`, D.`nome`) AGAINST(:q) AS `rank`";
				if($ordem == 'rank ASC')
					$ordem = '`rank` ASC, D.`sigla` DESC';
				else
					$ordem = '`rank` DESC, D.`sigla` ASC';
			} else
				$extra_select = "";
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_disciplinas` AS D WHERE MATCH(D.`sigla`, D.`nome`) AGAINST(:q IN BOOLEAN MODE)";
			$sql = "SELECT D.*".$extra_select." FROM `gde_disciplinas` AS D WHERE MATCH(D.`sigla`, D.`nome`) AGAINST(:q IN BOOLEAN MODE) ORDER BY ".$ordem;
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
		$rsm->addRootEntityFromClassMetadata(get_class(), 'D');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('q', $q);
		if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$query->enableResultCache(CONFIG_RESULT_CACHE_TTL);
		return $query->getResult();
	}

	/**
	 * @param Disciplina $A
	 * @param Disciplina $B
	 * @return int
	 */
	public static function Organiza($A, $B) {
		if(
			(($A instanceof Disciplina) || ($A instanceof CurriculoEletivaConjunto)) &&
			(($B instanceof Disciplina) || ($B instanceof CurriculoEletivaConjunto))
		)
			return strnatcasecmp($A->getSigla(), $B->getSigla());
		else
			return 0;
	}

	/**
	 * @param $Conjuntos
	 * @return array
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	private static function Organiza_Pre_Conjuntos($Conjuntos) {
		$Pre_Requisitos = array();
		foreach($Conjuntos as $Conjunto) {
			if(!isset($Pre_Requisitos[$Conjunto->getCatalogo(false)]))
				$Pre_Requisitos[$Conjunto->getCatalogo(false)] = array();
			$Pre_Requisitos[$Conjunto->getCatalogo(false)][$Conjunto->getID()] = array();
			foreach($Conjunto->getLista() as $Lista) {
				$Pre_Requisitos[$Conjunto->getCatalogo(false)][$Conjunto->getID()][] = array(Disciplina::Por_Sigla($Lista->getSigla(false), Disciplina::$NIVEIS_GRAD), $Lista->getParcial(), $Lista->getSigla(false));
			}
		}
		return $Pre_Requisitos;
	}

	/**
	 * @param $Equivalentes
	 * @return string
	 */
	public static function Formata_Conjuntos($Equivalentes) {
		if(count($Equivalentes) == 0)
			return "-";
		$ret = array();
		foreach($Equivalentes as $Equivalente) {
			$siglas = array();
			foreach($Equivalente as $sigla => $Disciplina) {
				if($Disciplina === null)
					$siglas[] = htmlspecialchars($sigla)." (?)";
				else
					$siglas[] = "<a href=\"".CONFIG_URL."disciplina/".$Disciplina->getId()."/\" title=\"".$Disciplina->getNome()."\">".$Disciplina->getSigla()."</a> (".(($Disciplina->getCreditos() > 0)?$Disciplina->getCreditos():'?').")";
			}
			$ret[] = implode(" e ", $siglas);
		}
		return implode(" ou<br />", $ret);
	}

	/**
	 * @param $sigla
	 * @return string
	 */
	public static function URL_Disciplina($id, $sigla) {
		return CONFIG_URL.((strpos($sigla, '-') === false)
			? 'disciplina/'.$id.'/'
			: 'busca/?t=tab_disciplinas&sigla='.urlencode($sigla).'&buscar#tab_disciplinas');
	}

	// Metodo que passa no cheap check do ProxyGenerator
	public function getId_disciplina() {
		return $this->id_disciplina;
	}

	/**
	 * @param bool $html
	 * @param bool $vazio
	 * @return string
	 */
	public function getNome($html = false, $vazio = false) {
		$nome = parent::getNome($html);
		if(($nome == null) && ($html || $vazio))
			return self::NOME_VAZIO;
		return $nome;
	}

	/**
	 * @param bool $html
	 * @param bool $vazio
	 * @return string
	 */
	public function getCreditos($html = false, $vazio = false) {
		$creditos = parent::getCreditos($html);
		if(($creditos == null) && ($html || $vazio))
			return self::CREDITOS_VAZIO;
		return $creditos;
	}

	/**
	 * @param bool $html
	 * @param bool $no_html
	 * @return string
	 */
	public function getEmenta($html = false, $no_html = true) {
		$ementa = parent::getEmenta(false);
		if($no_html)
			$ementa = strip_tags(html_entity_decode($ementa, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'));
		if($html)
			$ementa = htmlspecialchars($ementa);
		return $ementa;
	}

	/**
	 * @param bool $nome
	 * @return mixed|string
	 */
	public function getNivel($nome = false) {
		if($this->nivel == null)
			return ($nome) ? 'Desconhecido' : '';
		return (($nome) && (isset(self::$_niveis[$this->nivel]))) ? self::$_niveis[$this->nivel] : $this->nivel;
	}

	/**
	 * @param bool $nome
	 * @return bool|mixed|null
	 */
	public function getPeriodicidade($nome = false) {
		if($this->periodicidade == null)
			return ($nome) ? self::$_periodicidades[0] : null;
		else
			return (($nome) && (isset(self::$_periodicidades[$this->periodicidade]))) ? self::$_periodicidades[$this->periodicidade] : $this->periodicidade;
	}

	/**
	 * @param $Usuario
	 * @param bool $formatado
	 * @param null $catalogo
	 * @return array|mixed|string
	 */
	public function getPre_Requisitos($Usuario, $formatado = false, $catalogo = null) {
		$de_pos = in_array($this->getNivel(false), self::$NIVEIS_POS);

		if($formatado === false) { // Se nao eh formatado, retorna soh os do Catalogo do Usuario
			if($catalogo == null)
				$catalogo = ($de_pos) ? self::NIVEL_POS : $Usuario->getCatalogo(false);
			// Carrega apenas os Conjuntos do Catalogo em questao
			$criteria = Criteria::create()->where(Criteria::expr()->eq("catalogo", $catalogo));
			$organizados = self::Organiza_Pre_Conjuntos($this->getPre_Conjuntos()->matching($criteria));
			return (isset($organizados[$catalogo])) ? $organizados[$catalogo] : array();
		}

		if($catalogo == null) {
			$conjuntos = $this->getPre_Conjuntos();
		} else {
			$criteria = Criteria::create()->where(Criteria::expr()->eq("catalogo", $catalogo));
			$conjuntos = $this->getPre_Conjuntos()->matching($criteria);
		}
		$organizados = self::Organiza_Pre_Conjuntos($conjuntos);
		$pres = array();
		if($de_pos) {
			if(!isset($organizados['P']))
				return 'N&atilde;o h&aacute; pr&eacute;-requisitos cadastrados.';
			foreach($organizados['P'] as $n => $lista) {
				$pres[$n] = array();
				foreach($lista as $pre) {
					if(($pre[0] === null) || ($pre[0]->getID() == null)) {
						$url_sigla = htmlspecialchars($pre[2])." (?)";
					} else {
						$cursada = $Usuario->Eliminou($pre[0], $pre[1]);
						$url_sigla = "<a href=\"" . CONFIG_URL . "disciplina/" . $pre[0]->getId() . "/\" class=\"" . (($cursada !== false) ? "disciplina_eliminada" : null) . "\" title=\"" . $pre[0]->getNome(true) . "\">" . $pre[0]->getSigla(true) . "</a>" . " (" . (($pre[0]->getCreditos() > 0) ? $pre[0]->getCreditos() : '?') . ")";
					}
					if($pre[1] === true)
						$pres[$n][] = "*".$url_sigla;
					else
						$pres[$n][] = $url_sigla;
				}
				$pres[$n] = implode(" e ", $pres[$n]);
			}
			sort($pres);
			return implode(" ou ", $pres);
		} else {
			$subfinal = array();
			$final = array();
			foreach($organizados as $catalogo => $conjuntos) {
				foreach($conjuntos as $n => $lista) {
					$pres[$catalogo][$n] = array();
					foreach($lista as $pre) {
						if(($pre[0] === null) || ($pre[0]->getID() == null)) {
							$url_sigla = htmlspecialchars($pre[2])." (?)";
						} else {
							$cursada = $Usuario->Eliminou($pre[0], $pre[1]);
							$url_sigla = "<a href=\"" . CONFIG_URL . "disciplina/" . $pre[0]->getId() . "/\" class=\"" . (($cursada !== false) ? "disciplina_eliminada" : null) . "\" title=\"" . $pre[0]->getNome(true) . "\">" . $pre[0]->getSigla(true) . "</a>" . " (" . (($pre[0]->getCreditos(false) > 0) ? $pre[0]->getCreditos(true) : '?') . ")";
						}
						if($pre[1] === true)
							$pres[$catalogo][$n][] = "*".$url_sigla;
						else
							$pres[$catalogo][$n][] = $url_sigla;
					}
					$pres[$catalogo][$n] = implode(" e ", $pres[$catalogo][$n]);
				}
				sort($pres[$catalogo]);
				$subfinal[$catalogo] = implode(" ou<br />", $pres[$catalogo]);
			}
			$last = $last_catalogo = $primeiro = null;
			$agrupando = false;
			foreach($subfinal as $catalogo => $lista) {
				if($agrupando === true) {
					if($last != $lista) {
						$final[] = (($Usuario->getCatalogo() >= $primeiro) && ($Usuario->getCatalogo() <= $last_catalogo)) ? "<tr><td nowrap><strong>De ".$primeiro." At&eacute; ".$last_catalogo."</strong></td><td nowrap><strong>".$last."</strong></td></tr>" : "<tr><td nowrap>De ".$primeiro." At&eacute; ".$last_catalogo."</td><td nowrap>".$last."</td></tr>";
						$agrupando = false;
					}
					if((!isset($subfinal[$catalogo+1])) && ($agrupando === true)) {
						$final[] = (($Usuario->getCatalogo() >= $primeiro) && ($Usuario->getCatalogo() <= $catalogo)) ? "<tr><td nowrap><strong>De ".$primeiro." At&eacute; ".$catalogo."</strong></td><td nowrap><strong>".$lista."</strong>" : "<tr><td nowrap>De ".$primeiro." At&eacute; ".$catalogo."</td><td nowrap>".$lista."</td></tr>";
					}
				}
				if($agrupando === false) {
					if((isset($subfinal[$catalogo+1])) && ($lista == $subfinal[$catalogo+1])) {
						$agrupando = true;
						$primeiro = $catalogo;
					} else {
						$final[] = ($Usuario->getCatalogo() == $catalogo) ? "<tr><td nowrap><strong>De ".$catalogo." At&eacute; ".$catalogo."</strong></td><td nowrap><strong>".$lista."</strong></td></tr>" : "<tr><td nowrap>De ".$catalogo." At&eacute; ".$catalogo."</td><td nowrap>".$lista."</td></tr>";
					}
				}
				$last = $lista;
				$last_catalogo = $catalogo;
			}
			return (count($final) > 0) ? "<table border=\"1\">".implode("", $final)."</table>" : 'N&atilde;o h&aacute; pr&eacute;-requisitos cadastrados.';
		}
	}

	/**
	 * @param bool $formatado
	 * @return array|string
	 */
	public function Equivalencias($formatado = false) {
		$Lista = array();
		foreach(parent::getEquivalencias() as $Equivalencia) {
			if(!isset($Lista[$Equivalencia->getID()]))
				$Lista[$Equivalencia->getID()] = array();
			foreach($Equivalencia->getEquivalentes() as $Equivalente)
				// ToDo: Usar ID da Disciplina ao inves de sigla
				$Lista[$Equivalencia->getID()][$Equivalente->getSigla(false)] = $Equivalente->getDisciplina(true);
		}
		return ($formatado) ? self::Formata_Conjuntos($Lista) : $Lista;
	}

	/**
	 * Desistencias
	 *
	 * Retorna o numero de Alunos que trancaram esta Disciplina
	 *
	 * @return integer
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Desistencias() {
		$dql = 'SELECT COUNT(A.ra) FROM GDE\\Aluno AS A '.
			'INNER JOIN A.trancados AS O '.
			'INNER JOIN O.disciplina AS D '.
			'WHERE D.sigla = ?1';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getSigla(false));

		if((!defined('FORCE_NO_CACHE')) && (defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && (RESULT_CACHE_AVAILABLE === true))
			$query->enableResultCache(CONFIG_RESULT_CACHE_TTL);

		return $query->getSingleScalarResult();
	}

	/**
	 * Limpar_Pre_Conjuntos
	 *
	 * Remove todos os pre conjuntos para o $catalogo
	 *
	 * @param $catalogo
	 * @param bool $flush
	 * @return bool
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function Limpar_Pre_Conjuntos($catalogo, $flush = false) {
		foreach($this->getPre_Conjuntos() as $Conjunto) {
			if($Conjunto->getCatalogo(false) == $catalogo)
				$Conjunto->Delete(false);
		}
		return $this->Save($flush);
	}

}
