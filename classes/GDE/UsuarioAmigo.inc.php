<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * UsuarioAmigo
 *
 * @ORM\Table(
 *   name="gde_usuarios_amigos",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="id_usuario_amigo", columns={"id_usuario", "id_amigo"})
 *   }
 * )
 * @ORM\Entity
 */
class UsuarioAmigo extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_amizade;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumn(name="id_amigo", referencedColumnName="id_usuario")
	 */
	protected $amigo;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 */
	protected $usuario;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $apelido;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $ativo = false;

	/**
	 * @param bool $completo
	 * @param bool $html
	 * @return string
	 */
	public function Apelido_Ou_Nome($completo = false, $html = true) {
		if($this->getApelido(false) != null)
			return $this->getApelido($html);
		else
			return ($completo) ? $this->getAmigo(true)->getNome_Completo($html) : $this->getAmigo(true)->getNome($html);
	}

	/**
	 * @param UsuarioAmigo $A
	 * @param UsuarioAmigo $B
	 * @return int
	 */
	public static function Order_Por_Nome_Sort(UsuarioAmigo $A, UsuarioAmigo $B) {
		return strcasecmp($A->Apelido_Ou_Nome(true, false), $B->Apelido_Ou_Nome(true, false));
	}

	/**
	 * @param $Amigos
	 * @return ArrayCollection|UsuarioAmigo[]
	 */
	public static function Ordenar_Por_Nome($Amigos) {
		$iterator = $Amigos->getIterator();
		$iterator->uasort(function (UsuarioAmigo $A, UsuarioAmigo $B) {
			return strcasecmp($A->Apelido_Ou_Nome(true, false), $B->Apelido_Ou_Nome(true, false));
		});
		return new ArrayCollection(iterator_to_array($iterator));
	}

	/**
	 * @param Usuario $Usuario
	 * @param \DateTime $Inicio
	 * @param \DateTime $Fim
	 * @return UsuarioAmigo[]
	 */
	public static function Listar_Aniversarios(Usuario $Usuario, \DateTime $Inicio, \DateTime $Fim) {
		$sql = "SELECT UA.* FROM `gde_usuarios_amigos` AS UA ".
			"INNER JOIN `gde_usuarios` AS A  ON (A.`id_usuario` = UA.`id_amigo`)".
			"WHERE UA.`id_usuario` = :id_usuario AND UA.`ativo` = TRUE ".
			"AND DATE_FORMAT(A.`data_nascimento`,'%m-%d') BETWEEN DATE_FORMAT(:inicio, '%m-%d') AND DATE_FORMAT(:fim, '%m-%d')";
		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata(get_class(), 'UA');
		$query = self::_EM()->createNativeQuery($sql, $rsm)
			->setParameter('id_usuario', $Usuario->getID())
			->setParameter('inicio', $Inicio->format('Y-m-d'))
			->setParameter('fim', $Fim->format('Y-m-d'));
		return $query->getResult();
	}

}
