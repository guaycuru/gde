<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nota
 *
 * @ORM\Table(name="gde_notas")
 * @ORM\Entity
 */
class Nota extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_nota;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 */
	protected $usuario;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento")
	 * @ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")
	 */
	protected $oferecimento;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $sigla;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="decimal", precision=4, scale=2, nullable=false)
	 */
	protected $nota;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="decimal", precision=10, scale=5, options={"default"="1.00000"}, nullable=false)
	 */
	protected $peso = '1.00000';

	/**
	 * @param Usuario $Usuario
	 * @param Oferecimento $Oferecimento
	 * @return Nota[]|false
	 */
	public static function Listar(Usuario $Usuario, Oferecimento $Oferecimento) {
		return self::FindBy(array('usuario' => $Usuario->getID(), 'oferecimento' => $Oferecimento->getID()));
	}

}
