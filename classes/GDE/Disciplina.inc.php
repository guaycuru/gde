<?php

namespace GDE;

use Doctrine\Common\Collections\Collection;
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
 *     @ORM\Index(name="sigla_nome_ementa_fts", columns={"sigla", "nome", "ementa"}, flags={"fulltext"}),
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
	 * @var string
	 *
	 * @ORM\Column(type="string", length=5, nullable=false)
	 * @ORM\Id
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
	 * @var PreConjunto
	 *
	 * @ORM\OneToMany(targetEntity="PreConjunto", mappedBy="disciplina")
	 * @ORM\JoinColumn(name="sigla", referencedColumnName="sigla")
	 * @ORM\OrderBy({"catalogo" = "ASC"})
	 */
	protected $pre_conjuntos;

	/**
	 * @var Equivalente
	 *
	 * @ORM\OneToMany(targetEntity="Equivalente", mappedBy="disciplina")
	 * @ORM\JoinColumn(name="sigla", referencedColumnName="sigla")
	 */
	protected $equivalentes;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
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
	const NIVEL_S = 'S';
	const NIVEL_TEC = 'T';
	private static $_niveis = array(
		self::NIVEL_TEC => 'Tecnologia',
		self::NIVEL_GRAD => 'Gradua&ccedil;&atilde;o',
		self::NIVEL_POS => 'P&oacute;s-Gradua&ccedil;&atilde;o',
		self::NIVEL_S => 'Mestrado Profissional'
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
	 * @param string|null $nivel
	 * @param bool $vazio
	 * @return self
	 */
	public static function Por_Sigla($sigla, $nivel = null, $vazio = true) {
		if($nivel != null) {
			// Se temos nivel podemos fazer a busca por unique
			$Disciplina = self::FindOneBy(array('sigla' => $sigla, 'nivel' => $nivel));
			if(($Disciplina === null) && ($vazio === true))
				return new self;
			return $Disciplina;
		} else {
			// Se o nivel nao foi fornecido, pegamos a primeira encontrada
			$Disciplinas = self::FindBy(array('sigla' => $sigla));
			if(count($Disciplinas) > 0)
				return array_pop($Disciplinas);
			elseif($vazio == true)
				return new self;
			else
				return null;
		}
	}

	/**
	 * @param $param
	 * @param null $ordem
	 * @param int $total
	 * @param int $limit
	 * @param int $start
	 * @param string $tipo
	 * @return Collection|Disciplina[]
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
		if(isset($param['nivel']))
			$qrs[] = "D.nivel ".((is_array($param['nivel'])) ? "IN ('".implode("','", $param['nivel'])."')" : "= '".$param['nivel'][0]."'")."";
		if(isset($param['instituto'])) {
			$jns[] = "D.instituto AS I";
			$qrs[] = "INNER JOIN I.id_instituto = :instituto";
		}
		if(isset($param['creditos']))
			$qrs[] = "D.creditos = :creditos";
		if(isset($param['periodicidade']))
			$qrs[] = "D.periodicidade = :periodicidade";
		if(isset($param['ementa'])) {
			$qrs[] = "D.ementa LIKE :ementa";
			$param['ementa'] = '%'.$param['ementa'].'%';
		}
		$where = (count($qrs) > 0) ? " WHERE ".implode(" ".$tipo." ", $qrs) : "";
		$joins = (count($jns) > 0) ? implode(" ", $jns) : null;

		if($total !== null) {
			$dqlt = "SELECT COUNT(DISTINCT D.sigla) FROM ".get_class()." AS D ".$joins.$where;
			$total = self::_EM()->createQuery($dqlt)->setParameters($param)->getSingleScalarResult();
		}
		$dql = "SELECT DISTINCT D FROM ".get_class()." AS D ".$joins.$where." ORDER BY ".$ordem;
		$query = self::_EM()->createQuery($dql)->setParameters($param);
		if($limit > 0)
			$query->setMaxResults($limit);
		if($start > -1)
			$query->setFirstResult($start);
		return $query->getResult();
	}

	/**
	 * @param $q
	 * @param null $ordem
	 * @param null $total
	 * @param int $limit
	 * @param int $start
	 * @return Collection|Disciplina[]
	 */
	public static function Consultar_Simples($q, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		// ToDo: Pegar nome da tabela das annotations
		if((preg_match('/^[a-z ]{2}\d{3}$/i', $q) > 0) || (mb_strlen($q) < CONFIG_FT_MIN_LENGTH)) {
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC')
				$ordem = ($ordem != 'rank DESC') ? 'D.`sigla` ASC' : 'D.`sigla` DESC';
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_disciplinas` AS D WHERE D.`sigla` LIKE :q";
			$sql = "SELECT D.* FROM `gde_disciplinas` AS D WHERE D.`sigla` LIKE :q ORDER BY ".$ordem." LIMIT ".$start.",".$limit;
			$q = '%'.$q.'%';
		} else {
			$q = preg_replace('/(\w{'.CONFIG_FT_MIN_LENGTH.',})/', '+$1*', $q);
			if($ordem == null)
				$ordem = 'rank DESC';
			if($ordem == 'rank ASC' || $ordem == 'rank DESC') {
				$extra_select = ", MATCH(D.`sigla`, D.`nome`, D.`ementa`) AGAINST(:q) AS `rank`";
				if($ordem == 'rank ASC')
					$ordem = '`rank` ASC, D.`sigla` DESC';
				else
					$ordem = '`rank` DESC, D.`sigla` ASC';
			} else
				$extra_select = "";
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_disciplinas` AS D WHERE MATCH(D.`sigla`, D.`nome`, D.`ementa`) AGAINST(:q IN BOOLEAN MODE)";
			$sql = "SELECT D.*".$extra_select." FROM `gde_disciplinas` AS D WHERE MATCH(D.`sigla`, D.`nome`, D.`ementa`) AGAINST(:q IN BOOLEAN MODE) ORDER BY ".$ordem." LIMIT ".$start.",".$limit;
		}

		if($total !== null) {
			$rsmt = new ResultSetMappingBuilder(self::_EM());
			$rsmt->addScalarResult('total', 'total');
			$queryt = self::_EM()->createNativeQuery($sqlt, $rsmt);
			$queryt->setParameter('q', $q);
			$total = $queryt->getSingleScalarResult();
		}

		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata(get_class(), 'D');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('q', $q);
		return $query->getResult();
	}

	/**
	 * @param Disciplina $A
	 * @param Disciplina $B
	 * @return int
	 */
	public static function Organiza(Disciplina $A, Disciplina $B) {
		return strnatcasecmp($A->getSigla(), $B->getSigla());
	}

	/**
	 * @param $Conjuntos
	 * @return array
	 */
	private static function Organiza_Pre_Conjuntos($Conjuntos) {
		$Pre_Requisitos = array();
		foreach($Conjuntos as $Conjunto) {
			if(!isset($Pre_Requisitos[$Conjunto->getCatalogo(false)]))
				$Pre_Requisitos[$Conjunto->getCatalogo(false)] = array();
			$Pre_Requisitos[$Conjunto->getCatalogo(false)][$Conjunto->getID()] = array();
			foreach($Conjunto->getLista() as $Lista) {
				$Pre_Requisitos[$Conjunto->getCatalogo(false)][$Conjunto->getID()][] = array(Disciplina::Por_Sigla($Lista->getSigla(false)), $Lista->getParcial(), $Lista->getSigla(false));
			}
		}
		return $Pre_Requisitos;
	}

	/**
	 * @param $Conjuntos
	 * @return string
	 */
	public static function Formata_Conjuntos($Conjuntos) {
		if(count($Conjuntos) == 0)
			return "-";
		$ret = array();
		foreach($Conjuntos as $Conjunto) {
			$siglas = array();
			foreach($Conjunto as $sigla => $Equivalente) {
				if($Equivalente === null)
					$siglas[] = htmlspecialchars($sigla)." (?)";
				else
					$siglas[] = "<a href=\"".CONFIG_URL."disciplina/".$Equivalente->getSigla(true)."/\" title=\"".$Equivalente->getNome()."\">".$Equivalente->getSigla()."</a> (".(($Equivalente->getCreditos() > 0)?$Equivalente->getCreditos():'?').")";
			}
			$ret[] = implode(" e ", $siglas);
		}
		return implode(" ou<br />", $ret);
	}

	/**
	 * @param $sigla
	 * @return string
	 */
	public static function URL_Disciplina($sigla) {
		return CONFIG_URL.((strpos($sigla, '-') === false)
			? 'disciplina/'.urlencode($sigla).'/'
			: 'busca/?t=tab_disciplinas&sigla='.urlencode($sigla).'&buscar#tab_disciplinas');
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
			$ementa = html_entity_decode(strip_tags($ementa));
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
		$de_pos = in_array($this->getNivel(false), array('M', 'D', 'S'));

		if($formatado === false) { // Se nao eh formatado, retorna soh os do Catalogo do Usuario
			$catalogo = ($de_pos) ? self::NIVEL_POS : $Usuario->getCatalogo(false);
			// Carrega apenas os Conjuntos do Catalogo em questao
			$criteria = Criteria::create()->where(Criteria::expr()->eq("catalogo", $catalogo));
			$organizados = self::Organiza_Pre_Conjuntos($this->getPre_Conjuntos()->matching($criteria));
			return (isset($organizados[$catalogo])) ? $organizados[$catalogo] : array();
		}

		$organizados = self::Organiza_Pre_Conjuntos($this->getPre_Conjuntos());
		$pres = array();
		if($de_pos) {
			if(!isset($organizados['P']))
				return 'N&atilde;o h&aacute; pr&eacute;-requisitos cadastrados.';
			foreach($organizados['P'] as $n => $lista) {
				$pres[$n] = array();
				foreach($lista as $pre) {
					if($pre[0] === null) {
						$url_sigla = htmlspecialchars($pre[2])." (?)";
					} else {
						$cursada = $Usuario->Eliminou($pre[0], $pre[1]);
						$url_sigla = "<a href=\"" . CONFIG_URL . "disciplina/" . $pre[0]->getSigla(true) . "/\" class=\"" . (($cursada !== false) ? "disciplina_eliminada" : null) . "\" title=\"" . $pre[0]->getNome(true) . "\">" . $pre[0]->getSigla(true) . "</a>" . " (" . (($pre[0]->getCreditos() > 0) ? $pre[0]->getCreditos() : '?') . ")";
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
						if($pre[0] === null) {
							$url_sigla = htmlspecialchars($pre[2])." (?)";
						} else {
							$cursada = $Usuario->Eliminou($pre[0], $pre[1]);
							$url_sigla = "<a href=\"" . CONFIG_URL . "disciplina/" . $pre[0]->getSigla(true) . "/\" class=\"" . (($cursada !== false) ? "disciplina_eliminada" : null) . "\" title=\"" . $pre[0]->getNome(true) . "\">" . $pre[0]->getSigla(true) . "</a>" . " (" . (($pre[0]->getCreditos(false) > 0) ? $pre[0]->getCreditos(true) : '?') . ")";
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
						$final[] = (($Usuario->getCatalogo() >= $primeiro) && ($Usuario->getCatalogo() <= $last_catalogo)) ? "<tr><td><strong>De ".$primeiro." At&eacute; ".$last_catalogo."</strong></td><td><strong>".$last."</strong></td></tr>" : "<tr><td>De ".$primeiro." At&eacute; ".$last_catalogo."</td><td>".$last."</td></tr>";
						$agrupando = false;
					}
					if((!isset($subfinal[$catalogo+1])) && ($agrupando === true)) {
						$final[] = (($Usuario->getCatalogo() >= $primeiro) && ($Usuario->getCatalogo() <= $catalogo)) ? "<tr><td><strong>De ".$primeiro." At&eacute; ".$catalogo."</strong></td><td><strong>".$lista."</strong>" : "<tr><td>De ".$primeiro." At&eacute; ".$catalogo."</td><td>".$lista."</td></tr>";
					}
				}
				if($agrupando === false) {
					if((isset($subfinal[$catalogo+1])) && ($lista == $subfinal[$catalogo+1])) {
						$agrupando = true;
						$primeiro = $catalogo;
					} else {
						$final[] = ($Usuario->getCatalogo() == $catalogo) ? "<tr><td><strong>De ".$catalogo." At&eacute; ".$catalogo."</strong></td><td><strong>".$lista."</strong></td></tr>" : "<tr><td>De ".$catalogo." At&eacute; ".$catalogo."</td><td>".$lista."</td></tr>";
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
	public function getEquivalentes($formatado = false) {
		$Lista = array();
		foreach(parent::getEquivalentes() as $Equivalente) {
			if(!isset($Lista[$Equivalente->getID()]))
				$Lista[$Equivalente->getID()] = array();
			foreach($Equivalente->getConjuntos() as $Conjunto)
				$Lista[$Equivalente->getID()][$Conjunto->getSigla(false)] = Disciplina::Por_Sigla($Conjunto->getSigla(false));
		}
		return ($formatado) ? self::Formata_Conjuntos($Lista) : $Lista;
	}

	/**
	 * Desistencias
	 *
	 * Retorna o numero de Alunos que trancaram esta Disciplina
	 *
	 * @return integer
	 */
	public function Desistencias() {
		$dql = 'SELECT COUNT(A.ra) FROM GDE\\Aluno AS A '.
			'INNER JOIN A.trancadas AS O '.
			'INNER JOIN O.disciplina AS D '.
			'WHERE D.sigla = ?1';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getSigla(false));

		return $query->getSingleScalarResult();
	}

}
