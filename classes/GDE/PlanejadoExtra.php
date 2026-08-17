<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlanejadoExtra
 *
 * @ORM\Table(name="gde_planejados_extras", indexes={@ORM\Index(name="id_planejado", columns={"id_planejado"})})
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
	 * @ORM\Column(type="string", nullable=false)
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

	/**
	 * @return array
	 */
	public static function getCores() {
		return self::$_cores;
	}

	/**
	 * @param null $cor
	 * @param bool $editable
	 * @return array
	 */
	public function Evento($cor = null, $editable = true) {
		$ret = array('id' => 'extra_'.$this->getID(), 'title' => $this->getNome(true), 'start' => '2003-12-0'.$this->getDia().'T'.$this->getInicio('H:i:s').'-03:00', 'end' => '2003-12-0'.$this->getDia().'T'.$this->getFim('H:i:s').'-03:00', 'editable' => $editable);
		if($cor != null)
			$ret['color'] = $cor;
		return $ret;
	}

	/**
	 * Cria um extra para o planejado informado, validando os dados
	 *
	 * O objeto e criado mesmo quando ha erros de validacao; cabe a quem chama
	 * verificar $erros antes de salvar
	 *
	 * @param Planejado $Planejado
	 * @param string $nome
	 * @param integer $dia Dia da semana, de 1 a 7
	 * @param string $inicio Hora no formato H:i:s
	 * @param string $fim Hora no formato H:i:s
	 * @param array $erros Recebe as mensagens de erro encontradas
	 * @return PlanejadoExtra
	 */
	public static function Criar(Planejado $Planejado, $nome, $dia, $inicio, $fim, &$erros = null) {
		$erros = array();
		if(strlen($nome) < 2)
			$erros[] = "O Nome informado &eacute; inv&aacute;lido.";
		if(($dia < 1) || ($dia > 7))
			$erros[] = "O dia informado &eacute; inv&aacute;lido.";
		if(preg_match('/^\d{1,2}:\d{2}:\d{2}$/i', $inicio) == 0)
			$erros[] = "O in&iacute;cio informado &eacute; inv&aacute;lido.";
		if(preg_match('/^\d{1,2}:\d{2}:\d{2}$/i', $fim) == 0)
			$erros[] = "O fim informado &eacute; inv&aacute;lido.";
		$Extra = new PlanejadoExtra();
		$Extra->setPlanejado($Planejado);
		$Extra->setNome($nome);
		$Extra->setDia($dia);
		$Extra->setInicio($inicio);
		$Extra->setFim($fim);
		return $Extra;
	}

	/**
	 * Verifica se este extra existe e pertence ao planejado informado
	 *
	 * @param Planejado $Planejado
	 * @return boolean
	 */
	public function Pertence_A(Planejado $Planejado) {
		if($this->getID() == null)
			return false;
		if($this->getPlanejado(false) === null)
			return false;
		return ($this->getPlanejado()->getID() == $Planejado->getID());
	}

	/**
	 * @param $dias
	 * @param $minutos
	 * @param $inteiro
	 */
	public function Mover($dias, $minutos, $inteiro) {
		if($inteiro)
			$this->setInicio(self::Soma_Minutos($this->getInicio('H:i:s'), $minutos));
		$this->setFim(self::Soma_Minutos($this->getFim('H:i:s'), $minutos));
		$this->setDia($this->getDia() + $dias);
	}

	/**
	 * Soma $minutos (pode ser negativo) a uma hora H:i:s, sem deixar a hora
	 * resultante ser menor que 7
	 *
	 * @param string $hora Hora no formato H:i:s
	 * @param integer $minutos Minutos a somar
	 * @return string A nova hora, no formato H:i:s
	 */
	private static function Soma_Minutos($hora, $minutos) {
		list($h, $m, $s) = explode(':', $hora);
		$total = ($h * 60) + $m + $minutos;
		$h = (int)floor($total / 60);
		$m = $total - ($h * 60);
		if($h < 7)
			$h = 7;
		return sprintf('%02d:%02d:%s', $h, $m, $s);
	}

}
