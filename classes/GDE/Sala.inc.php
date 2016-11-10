<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sala
 *
 * @ORM\Table(name="gde_salas", uniqueConstraints={@ORM\UniqueConstraint(name="nome", columns={"nome"})})
 * @ORM\Entity
 */
class Sala extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_sala", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_sala;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=6, nullable=false)
	 */
	protected $nome;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="lugares", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $lugares;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_predio", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_predio;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="andar", type="string", length=255, nullable=true)
	 */
	protected $andar;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_unidade", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_unidade;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="topologia", type="string", length=255, nullable=true)
	 */
	protected $topologia;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="utilizacoes", type="string", length=255, nullable=true)
	 */
	protected $utilizacoes;

	/**
	 * Por_Nome
	 *
	 * Carrega uma sala pelo nome
	 *
	 * @param $nome
	 * @return mixed
	 */
	public static function Por_Nome($nome, $vazio = false) {
		$Sala = self::FindOneBy(array('nome' => $nome));
		if(($Sala === null) && ($vazio === true))
			$Sala = new self;
		return $Sala;
	}

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
