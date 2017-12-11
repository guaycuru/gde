<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cardapio
 *
 * @ORM\Table(name="gde_cardapios", uniqueConstraints={@ORM\UniqueConstraint(name="data", columns={"data", "tipo"})})
 * @ORM\Entity
 */
class Cardapio extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_cardapio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=false)
	 */
	protected $data;

	const TIPO_ALMOCO = 1;
	const TIPO_JANTAR = 2;
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $principal;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $vegetariano;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $guarnicao;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $pts;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $salada;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $sobremesa;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $suco;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $ultima_atualizacao;

	/**
	 * @return Cardapio
	 */
	public static function Atual() {
		$hora = date('H');
		$dia = date('d');
		$sem = date('w');
		$tipo = (($sem == 0) || ($sem == 6) || ($hora < 14) || ($hora >= 20)) ? 1 : 2;
		if(($sem == 0) || (($sem != 6) && ($hora >= 20)))
			$dia++;
		if($sem == 6)
			$dia += 2;
		$data = date('Y-m-d', mktime(12, 0, 0, date('m'), $dia));

		$query = self::_EM()->createQuery("SELECT C FROM GDE\\Cardapio C WHERE C.data >= ?1 AND C.tipo = ?2 ORDER BY C.data ASC");
		$query->setParameter(1, $data);
		$query->setParameter(2, $tipo);
		$query->setMaxResults(1);
		try {
			return $query->getSingleResult();
		} catch(\Doctrine\ORM\NoResultException $e) {
			return new self;
		}
	}

	/**
	 * @param null $time
	 * @return Cardapio[]
	 */
	public static function Cardapios_Semana($time = null) {
		if($time == null)
			$time = time();
		$ds = date('w', $time);

		if($ds == 6)
			$df = 2;
		elseif($ds == 0)
			$df = 1;
		else
			$df = 1 - $ds;

		$inicio = mktime(12, 0, 0, date('m', $time), date('d', $time)+$df);
		$fim = mktime(12, 0, 0, date('m', $time), date('d', $time)+$df+4);
		return self::Cardapios_Por_Datas(\DateTime::createFromFormat('U', $inicio), \DateTime::createFromFormat('U', $fim));
	}

	/**
	 * @param \DateTime $inicio
	 * @param \DateTime $fim
	 * @return Cardapio[]
	 */
	public static function Cardapios_Por_Datas(\DateTime $inicio, \DateTime $fim) {
		$inicio->setTime(0, 0, 0);
		$fim->setTime(0, 0, 0);
		$query = self::_EM()->createQuery("SELECT C FROM GDE\\Cardapio C WHERE C.data >= ?1 AND C.data <= ?2 ORDER BY C.data ASC");
		$query->setParameter(1, $inicio);
		$query->setParameter(2, $fim);
		return $query->getResult();
	}

	/**
	 * @return int
	 */
	public static function Ultimo_ID() {
		$dql = "SELECT MAX(C.id_cardapio) FROM GDE\\Cardapio C";
		$query = self::_EM()->createQuery($dql);
		try {
			return $query->getSingleScalarResult();
		} catch(\Doctrine\ORM\NoResultException $e) {
			return PHP_INT_MAX;
		}
	}

	/**
	 * @return bool|int
	 */
	public function ID_Anterior() {
		if($this->getID() == null)
			return false;
		return ($this->getID() > 1) ? $this->getID() - 1 : false;
	}

	/**
	 * @return bool|int
	 */
	public function ID_Proximo() {
		if($this->getID() == null)
			return false;
		return ($this->getID() < self::Ultimo_ID()) ? $this->getID() + 1 : false;
	}

	/**
	 * @param bool $cabecalho
	 * @return string
	 */
	public function Formatado($cabecalho = true) {
		return (($cabecalho)?"<strong>".(($this->getTipo(false) == self::TIPO_ALMOCO)?"ALMO&Ccedil;O":"JANTAR")."</strong> de <strong>".$this->Dia_Da_Semana().", ".$this->getData("d/m/Y")."</strong>:<br />":null)."<strong>Prato Principal:</strong> ".$this->getPrincipal(true)."<br />".
		((!empty($this->getGuarnicao(false)))? "<strong>Guarni&ccedil;&atilde;o:</strong> ".$this->getGuarnicao(true)."<br />" : null).
		((!empty($this->getPTS(false)))? "<strong>PTS:</strong> ".$this->getPTS(true)."<br />" : null).
		"<strong>Salada:</strong> ".$this->getSalada(true)."<br />".
		"<strong>Sobremesa:</strong> ".$this->getSobremesa(true)."<br />".
		"<strong>Suco:</strong> ".$this->getSuco(true)."<br />".
		((!empty($this->getVegetariano(false)))? "<strong>Vegetariano:</strong> ".$this->getVegetariano(true)."<br />" : null);
	}

	public function Dia_Da_Semana() {
		return Util::Dia_Da_Semana($this->getData('w'));
	}
}
