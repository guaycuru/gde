<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlanejadoExtra
 *
 * @ORM\Table(name="gde_planejado_extras", indexes={@ORM\Index(name="id_planejado", columns={"id_planejado"})})
 * @ORM\Entity
 */
class PlanejadoExtra extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_planejado_extra;

	/**
	 * @var Planejado
	 *
	 * @ORM\ManyToOne(targetEntity="Planejado", inversedBy="extras")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_planejado", referencedColumnName="id_planejado")
	 * })
	 */
	protected $planejado;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $dia;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="time", nullable=false)
	 */
	protected $inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="time", nullable=false)
	 */
	protected $fim;

	private static $_cores = array('#604F99', '#D47F1E', '#4CB052', '#AD2D2D', '#536CA6');

	public static function getCores() {
		return self::$_cores;
	}

	public function Evento($cor = null, $editable = true) {
		$ret = array('id' => 'extra_'.$this->getID(), 'title' => $this->getNome(true), 'start' => '2003-12-0'.$this->getDia().'T'.$this->getInicio('H:i:s').'-03:00', 'end' => '2003-12-0'.$this->getDia().'T'.$this->getFim('H:i:s').'-03:00', 'editable' => $editable);
		if($cor != null)
			$ret['color'] = $cor;
		return $ret;
	}

	public function Mover($dias, $minutos, $inteiro) {
		if($inteiro) {
			$h = $this->getInicio('H');
			$m = $this->getInicio('i');
			$s = $this->getInicio('s');
			$h += floor($minutos / 60);
			$m += $minutos % 60;
			if($h < 7)
				$h = 7;
			$this->setInicio(sprintf("%02d", $h).':'.sprintf("%02d", $m).':'.$s);
		}
		$h = $this->getFim('H');
		$m = $this->getFim('i');
		$s = $this->getFim('s');
		$h += floor($minutos / 60);
		if($h < 7)
			$h = 7;
		$m += $minutos % 60;
		$this->setFim(sprintf("%02d", $h).':'.sprintf("%02d", $m).':'.$s);
		$this->setDia($this->getDia() + $dias);
	}

}
