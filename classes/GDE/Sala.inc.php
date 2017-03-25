<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sala
 *
 * @ORM\Table(name="gde_salas")
 * @ORM\Entity
 */
class Sala extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_sala;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=6, unique=true, nullable=false)
	 */
	protected $nome;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
	 */
	protected $lugares;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
	 */
	protected $id_predio;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $andar;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
	 */
	protected $id_unidade;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $topologia;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $utilizacoes;

	// ToDo: Remover isto!
	public static $ordens_nome = array('Nome');
	public static $ordens_inte = array('S.nome');

	/**
	 * Por_Nome
	 *
	 * Carrega uma sala pelo nome
	 *
	 * @param $nome
	 * @param $vazio
	 * @return mixed
	 */
	public static function Por_Nome($nome, $vazio = false) {
		$Sala = self::FindOneBy(array('nome' => $nome));
		if(($Sala === null) && ($vazio === true))
			$Sala = new self;
		return $Sala;
	}

	/**
	 * Consultar_Simples
	 *
	 * @param $q
	 * @param null $ordem
	 * @param null $total
	 * @param int $limit
	 * @param int $start
	 * @return Sala[]
	 */
	public static function Consultar_Simples($q, $ordem = null, &$total = null, $limit = -1, $start = -1) {
		$param = array("%".str_replace(' ', '%', $q)."%");
		if($total !== null) {
			$dqlt = "SELECT COUNT(DISTINCT S.id_sala) FROM GDE\\Sala AS S WHERE S.nome LIKE ?1";
			$total = self::_EM()->createQuery($dqlt)->setParameters($param)->getSingleScalarResult();
		}
		$dql = "SELECT DISTINCT S FROM GDE\\Sala AS S WHERE S.nome LIKE ?1";
		if($ordem != null)
			$dql .= " ORDER BY ".$ordem;
		$query = self::_EM()->createQuery($dql)->setParameters($param);
		if($limit > 0)
			$query->setMaxResults($limit);
		if($start > -1)
			$query->setFirstResult($start);
		return $query->getResult();
	}

	/**
	 * @param null $periodo
	 * @return array
	 */
	public function Oferecimentos($periodo = null) {
		$Lista = array();
		$dql = 'SELECT O FROM GDE\\Oferecimento AS O '.
			'JOIN O.dimensoes AS D ';
		if($periodo != null)
			'JOIN O.periodo AS P ';
		$dql .= 'WHERE D.sala = ?1';
		if($periodo != null)
			$dql .= ' AND O.periodo = ?2';
		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getID());
		if($periodo != null) {
			$query->setParameter(2, $periodo);
			foreach($query->getResult() as $Oferecimento)
				$Lista[] = $Oferecimento;
		} else {
			foreach($query->getResult() as $Oferecimento)
				$Lista[$Oferecimento->getPeriodo(true)->getID()][] = $Oferecimento;
		}
		return $Lista;
	}

	/**
	 * Monta_Horario
	 *
	 * Monta o horario desta sala
	 *
	 * @param $periodo
	 * @return array
	 */
	public function Monta_Horario($periodo) {
		$Lista = array();
		if($this->getID() == null)
			return $Lista;
		foreach($this->Oferecimentos($periodo) as $Oferecimento)
			foreach($Oferecimento->getDimensoes() as $Dimensao)
				if($Dimensao->getSala(true)->getID() == $this->getID())
					$Lista[$Dimensao->getDia()][$Dimensao->getHorario()][] = $Oferecimento;
		return $Lista;
	}
}
