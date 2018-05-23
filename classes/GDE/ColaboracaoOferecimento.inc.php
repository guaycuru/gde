<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ColaboracaoOferecimento
 *
 * @ORM\Table(name="gde_colaboracao_oferecimentos",
 *  indexes={
 *     @ORM\Index(name="oferecimento_campo_status", columns={"id_oferecimento", "campo", "status"}),
 *     @ORM\Index(name="status", columns={"status"})
 *  }
 * )
 * @ORM\Entity
 */
class ColaboracaoOferecimento extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_colaboracao;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento")
	 * @ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")
	 */
	protected $oferecimento;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="usuario")
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 */
	protected $usuario;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $campo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $valor;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $status;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

	const CAMPO_PAGINA = 'pagina';
	private static $_campos = array(self::CAMPO_PAGINA);

	const STATUS_AUTORIZADA = 'a';
	const STATUS_PENDENTE = 'p';
	const STATUS_RECUSADA = 'r';

	/**
	 * @param $id_oferecimento
	 * @param $campo
	 * @return bool
	 */
	public static function Existe_Colaboracao($id_oferecimento, $campo) {
		$dql = 'SELECT COUNT(C.id_colaboracao) FROM '.get_class().' AS C '.
			'WHERE C.oferecimento = ?1 AND C.campo = ?2 AND C.status != ?3';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $id_oferecimento)
			->setParameter(2, $campo)
			->setParameter(3, self::STATUS_RECUSADA);

		return ($query->getSingleScalarResult() > 0);
	}

	public static function Campo_Valido($campo) {
		return in_array($campo, self::$_campos);
	}

	public static function Pendentes() {
		return self::FindBy(array('status' => self::STATUS_PENDENTE));
	}

	public static function Numero($status = null) {
		$dql = 'SELECT COUNT(C.id_colaboracao) FROM '.get_class().' AS C';
		if($status !== null)
			$dql .= ' WHERE C.status = ?1';

		$query = self::_EM()->createQuery($dql);
		if($status !== null)
			$query->setParameter(1, $status);

		return $query->getSingleScalarResult();
	}

	public function Copiar($flush = true) {
		if($this->getStatus(false) == self::STATUS_RECUSADA)
			return false;
		$Oferecimento = $this->getOferecimento();
		switch($this->getCampo(false)) {
			case self::CAMPO_PAGINA:
				$Oferecimento->setPagina($this->getValor(false));
				break;
		}
		return $Oferecimento->Save($flush);
	}

}
