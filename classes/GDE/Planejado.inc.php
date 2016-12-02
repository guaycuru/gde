<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Planejado
 *
 * @ORM\Table(name="gde_planejados", indexes={@ORM\Index(name="compartilhado", columns={"compartilhado"}), @ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class Planejado extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_planejado", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_planejado;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $usuario;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo", referencedColumnName="id_periodo")
	 */
	protected $periodo;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo_atual", referencedColumnName="id_periodo")
	 */
	protected $periodo_atual;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="compartilhado", type="boolean", nullable=false)
	 */
	protected $compartilhado = false;

	/**
	 * Por_Usuario
	 *
	 * @param Usuario $Usuario
	 * @param $periodo
	 * @param bool $compartilhados
	 * @return mixed
	 */
	public static function Por_Usuario(Usuario $Usuario, $periodo, $compartilhados = true) {
		if($periodo instanceof Periodo)
			$periodo = $periodo->getPeriodo();

		$params = array('usuario' => $Usuario->getID(), 'periodo' => $periodo);
		if($compartilhados === true)
			$params['compartilhado'] = true;

		return self::FindBy($params, array('id_planejado' => 'ASC'));
	}

}
