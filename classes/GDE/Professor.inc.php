<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Professor
 *
 * @ORM\Table(
 *   name="gde_professores",
 *   indexes={
 *     @ORM\Index(name="nome", columns={"nome"}, flags={"fulltext"})
 *   }
 * )
 * @ORM\Entity
 */
class Professor extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_professor;

	/**
	 * @var Usuario
	 *
	 * @ORM\OneToOne(targetEntity="Usuario", mappedBy="professor")
	 */
	protected $usuario;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\OneToMany(targetEntity="Oferecimento", mappedBy="professor")
	 */
	protected $oferecimentos;

	/**
	 * @var Instituto
	 *
	 * @ORM\ManyToOne(targetEntity="Instituto")
	 * @ORM\JoinColumn(name="id_instituto", referencedColumnName="id_instituto")
	 */
	protected $instituto;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", unique=true, options={"unsigned"=true}, nullable=true)
	 */
	protected $matricula;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $sala;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $pagina;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $lattes;

	// ToDo: Remover isso!
	public static $ordens_nome = array('Relev&acirc;ncia', 'Nome');
	public static $ordens_inte = array('rank', 'P.nome');

	/**
	 * @param null|string $periodo
	 * @param bool $formatado
	 * @param bool $links
	 * @return string
	 */
	public function getOferecimentos($periodo = null, $formatado = false, $links = true) {
		if($periodo == null) {
			$Oferecimentos = parent::getOferecimentos();
		} else {
			$criteria = Criteria::create()->where(Criteria::expr()->eq("id_periodo", $periodo));
			$Oferecimentos = parent::getOferecimentos()->matching($criteria);
		}
		if($formatado === false)
			return $Oferecimentos;
		else {
			$lista = array();
			foreach(parent::$Oferecimentos as $Oferecimento)
				$lista[] = ($links) ? "<a href=\"".CONFIG_URL."oferecimento/".$Oferecimento->getID()."/\" title=\"".$Oferecimento->getDisciplina(true)->getNome(true)."\">".$Oferecimento->getSigla().$Oferecimento->getTurma(true)."</a> (".$Oferecimento->getDisciplina(true)->getCreditos(true).")" : $Oferecimento->getSigla(true).$Oferecimento->getTurma(true)." (".$Oferecimento->getDisciplina(true)->getCreditos(true).")";
			return (count($lista) > 0) ? implode(", ", $lista) : '-';
		}
	}

	/**
	 * @param null $periodo
	 * @return array
	 */
	public function Monta_Horario($periodo = null) {
		$Lista = array();
		foreach($this->getOferecimentos($periodo) as $Oferecimento)
			foreach($Oferecimento->getDimensoes() as $Dimensao)
				$Lista[$Dimensao->getDia()][$Dimensao->getHorario()][] = array($Oferecimento, $Dimensao->getSala(true)->getNome());
		return $Lista;
	}

	/**
	 * @param $q
	 * @param null $ordem
	 * @param null $total
	 * @param int $limit
	 * @param int $start
	 * @return ArrayCollection|Professor[]
	 */
	public static function Consultar_Simples($q, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		// ToDo: Pegar nome da tabela das annotations
		$limit = intval($limit);
		$start = intval($start);
		if(strlen($q) < CONFIG_FT_MIN_LENGTH) {
			if($ordem == null || $ordem == 'rank ASC' || $ordem == 'rank DESC')
				$ordem = ($ordem != 'rank DESC') ? 'P.`nome` ASC' : 'P.`nome` DESC';
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_professores` AS P WHERE P.`nome` LIKE :q";
			$sql = "SELECT P.* FROM `gde_professores` AS P WHERE P.`nome` LIKE :q ORDER BY ".$ordem." LIMIT ".$start.",".$limit;
			$q = '%'.$q.'%';
		} else {
			$q = preg_replace('/(\w{'.CONFIG_FT_MIN_LENGTH.',})/', '+$1*', $q);
			if($ordem == null)
				$ordem = 'rank DESC';
			if($ordem == 'rank ASC' || $ordem == 'rank DESC') {
				$extra_select = ", MATCH(P.`nome`) AGAINST(:q) AS `rank`";
				if($ordem == 'rank ASC')
					$ordem = '`rank` ASC, P.`nome` DESC';
				else
					$ordem = '`rank` DESC, P.`nome` ASC';
			} else
				$extra_select = "";
			if($total !== null)
				$sqlt = "SELECT COUNT(*) AS `total` FROM `gde_professores` AS P WHERE MATCH(P.`nome`) AGAINST(:q IN BOOLEAN MODE)";
			$sql = "SELECT P.*".$extra_select." FROM `gde_professores` AS P WHERE MATCH(P.`nome`) AGAINST(:q IN BOOLEAN MODE) ORDER BY ".$ordem." LIMIT ".$start.",".$limit;
		}

		if($total !== null) {
			$rsmt = new ResultSetMappingBuilder(self::_EM());
			$rsmt->addScalarResult('total', 'total');
			$queryt = self::_EM()->createNativeQuery($sqlt, $rsmt);
			$queryt->setParameter('q', $q);
			$total = $queryt->getSingleScalarResult();
		}

		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata(get_class(), 'P');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('q', $q);
		return $query->getResult();
	}

}
