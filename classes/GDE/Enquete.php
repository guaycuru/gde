<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Enquete
 *
 * @ORM\Table(
 *  name="gde_enquetes",
 *   indexes={
 *     @ORM\Index(name="ativa", columns={"ativa"})
 *   }
 * )
 * @ORM\Entity
 */
class Enquete extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_enquete;

	/**
	 * @var EnqueteOpcao
	 *
	 * @ORM\OneToMany(targetEntity="EnqueteOpcao", mappedBy="enquete")
	 */
	protected $opcoes;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $chamada;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $pergunta;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=false)
	 */
	protected $data;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $ativa = false;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", options={"default"=1}, nullable=false)
	 */
	protected $max_votos = 1;

	/**
	 * @return Enquete|null
	 */
	public static function Ativa() {
		$Enquetes = self::FindBy(array('ativa' => true), null, 1);
		return (count($Enquetes) > 0) ? $Enquetes[0] : null;
	}

	/**
	 * @param Usuario $Usuario
	 * @return integer
	 */
	public function Usuario_Quantos_Votos(Usuario $Usuario) {
		$dql = "SELECT COUNT(O.id_opcao) FROM ".get_class()." AS E ".
			"JOIN E.opcoes AS O ".
			"WHERE E.id_enquete = :id_enquete ".
			"AND :usuario MEMBER OF O.usuarios";
		return self::_EM()->createQuery($dql)
			->setParameter('id_enquete', $this->getID())
			->setParameter('usuario', $Usuario)
			->getSingleScalarResult();
	}

	/**
	 * @param Usuario $Usuario
	 * @return bool
	 */
	public function Ja_Votou(Usuario $Usuario) {
		return ($this->Usuario_Quantos_Votos($Usuario) > 0);
	}

	/**
	 * @return integer
	 */
	public function Numero_Votos() {
		$dql = "SELECT COUNT(U.id_usuario) FROM ".get_class()." AS E ".
			"JOIN E.opcoes AS O JOIN O.usuarios AS U ".
			"WHERE E.id_enquete = :id_enquete";
		return self::_EM()->createQuery($dql)
			->setParameter('id_enquete', $this->getID())
			->getSingleScalarResult();
	}

	/**
	 * @return integer
	 */
	public function Numero_Usuarios() {
		$dql = "SELECT COUNT(DISTINCT U.id_usuario) FROM ".get_class()." AS E ".
			"JOIN E.opcoes AS O JOIN O.usuarios AS U ".
			"WHERE E.id_enquete = :id_enquete";
		return self::_EM()->createQuery($dql)
			->setParameter('id_enquete', $this->getID())
			->getSingleScalarResult();
	}

}
