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

	const TIPO_CAFE_DA_MANHA = 0;
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
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $observacoes;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $ultima_atualizacao;

	private static $_tipos = array(
		self::TIPO_CAFE_DA_MANHA => 'Caf&eacute; da Manh&atilde;',
		self::TIPO_ALMOCO => 'Almo&ccedil;o',
		self::TIPO_JANTAR => 'Jantar'
	);

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
	 * @param $data
	 * @param $tipo
	 * @return false|null|Cardapio
	 */
	public static function Por_Data_Tipo($data, $tipo) {
		return self::FindOneBy(array('data' => $data, 'tipo' => $tipo));
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
		$query = self::_EM()->createQuery("SELECT C FROM GDE\\Cardapio C WHERE C.data >= ?1 AND C.data <= ?2 ORDER BY C.data ASC, C.tipo ASC");
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
		$guarnicao = $this->getGuarnicao(true);
		$pts = $this->getPTS(true);
		$sobremesa = $this->getSobremesa(true);
		$salada = $this->getSalada(true);
		$suco = $this->getSuco(true);
		$vegetariano = $this->getVegetariano(true);
		$observacoes = $this->getObservacoes(true);

		$ret = "";
		if($cabecalho)
			$ret .= "<strong>".$this->getTipo(true)."</strong> de <strong>".$this->Dia_Da_Semana().", ".$this->getData("d/m/Y")."</strong>:<br />";
		$ret .= "<strong>Prato Principal:</strong> ".$this->getPrincipal(true)."<br />";
		if(!empty($guarnicao))
			$ret .= "<strong>Guarni&ccedil;&atilde;o:</strong> ".$guarnicao."<br />";
		if(!empty($pts))
			$ret .= "<strong>PTS:</strong> ".$pts."<br />";
		if(!empty($salada))
			$ret .= "<strong>Salada:</strong> ".$salada."<br />";
		if(!empty($sobremesa))
			$ret .= "<strong>Sobremesa:</strong> ".$sobremesa."<br />";
		if(!empty($suco))
			$ret .= "<strong>Suco:</strong> ".$suco."<br />";
		if(!empty($vegetariano))
			$ret .= "<strong>Vegetariano:</strong> ".$vegetariano."<br />";
		if(!empty($observacoes))
			$ret .= "<strong>Observa&ccedil;&otilde;es:</strong> ".$observacoes."<br />";

		return $ret;
	}

	public function Dia_Da_Semana() {
		return Util::Dia_Da_Semana($this->getData('w'));
	}

	public function getTipo($html = false) {
		$tipo = parent::getTipo(false);
		if($html === false)
			return $tipo;
		else
			return self::$_tipos[$tipo];
	}
}
