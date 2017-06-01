<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
	 * @ORM\Column(type="string", length=255, nullable=true)
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
	 * @return mixed
	 */
	public function Apelido_Ou_Nome($completo = false, $html = true) {
		if($this->getApelido(false) != null)
			return $this->getApelido($html);
		else
			return ($completo) ? $this->getAmigo(true)->getNome_Completo($html) : $this->getAmigo(true)->getNome($html);
	}

	public static function Ordenar_Por_Nome($Amigos) {
		$iterator = $Amigos->getIterator();
		$iterator->uasort(function ($A, $B) {
			return strcmp($A->Apelido_Ou_Nome(true, false), $B->Apelido_Ou_Nome(true, false));
		});
		return new ArrayCollection(iterator_to_array($iterator));
	}

}
