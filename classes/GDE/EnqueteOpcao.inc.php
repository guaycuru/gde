<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * EnqueteOpcao
 *
 * @ORM\Table(name="gde_enquetes_opcoes")
 * @ORM\Entity
 */
class EnqueteOpcao extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_opcao;

	/**
	 * @var Enquete
	 *
	 * @ORM\ManyToOne(targetEntity="Enquete")
	 * @ORM\JoinColumn(name="id_enquete", referencedColumnName="id_enquete")
	 */
	protected $enquete;

	/**
	 * @var ArrayCollection|Usuario[]
	 *
	 * @ORM\ManyToMany(targetEntity="Usuario", mappedBy="enquetes_opcoes")
	 * @ORM\JoinTable(name="gde_r_usuarios_enquetes_opcoes",
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")},
	 *      joinColumns={@ORM\JoinColumn(name="id_opcao", referencedColumnName="id_opcao")}
	 * )
	 */
	protected $usuarios;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $opcao;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $ativa = false;

	/**
	 * @return integer
	 */
	public function Numero_Votos() {
		$dql = "SELECT COUNT(DISTINCT U.id_usuario) FROM ".get_class()." AS O ".
			"JOIN O.usuarios AS U ".
			"WHERE O.id_opcao = :id_opcao";
		return self::_EM()->createQuery($dql)
			->setParameter('id_opcao', $this->getID())
			->getSingleScalarResult();
	}

	/**
	 * @param int $decimais
	 * @return string
	 */
	public function Porcentagem($decimais = 2) {
		$total_usuarios = $this->getEnquete()->Numero_Usuarios();
		return ($total_usuarios > 0)
			? number_format(($this->Numero_Votos() / $total_usuarios)*100, $decimais)
			: '0.'.str_repeat('0', $decimais);
	}

}
