<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ColaboracaoOferecimento
 *
 * @ORM\Table(name="gde_colaboracao_oferecimentos",
 *     indexes={@ORM\Index(name="oferecimento_campo_status", columns={"id_oferecimento", "campo", "status"})}
 * )
 * @ORM\Entity
 */
class ColaboracaoOferecimento extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_colaboracao", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_colaboracao;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento")
	 * @ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento)
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
	 * @ORM\Column(name="campo", type="string", length=255, nullable=false)
	 */
	protected $campo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="valor", type="string", length=255, nullable=false)
	 */
	protected $valor;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="status", type="string", length=1, nullable=false)
	 */
	protected $status;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data", type="datetime", nullable=false)
	 */
	protected $data;

	const STATUS_RECUSADO = 'r';

	/**
	 * @param $id_oferecimento
	 * @param $campo
	 * @return bool
	 */
	public static function Existe_Colaboracao($id_oferecimento, $campo) {
		$dql = 'SELECT COUNT(C.id_colaboracao) FROM GDE\\ColaboracaoOferecimento AS C '.
			'WHERE C.oferecimento = ?1 AND C.campo = ?2 AND C.status != ?3';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $id_oferecimento)
			->setParameter(2, $campo)
			->setParameter(3, self::STATUS_RECUSADO);

		return ($query->getSingleScalarResult() > 0);
	}

	/**
	 * @param $id_oferecimento
	 * @param $campo
	 * @param bool $vazio
	 * @return ColaboracaoOferecimento|null
	 */
	public static function Pega_Colaboracao($id_oferecimento, $campo, $vazio = true) {
		$dql = 'SELECT COUNT(C.id_colaboracao) FROM GDE\\ColaboracaoOferecimento AS C '.
			'WHERE C.oferecimento = ?1 AND C.campo = ?2 AND C.status != ?3';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $id_oferecimento)
			->setParameter(2, $campo)
			->setParameter(3, self::STATUS_NOVA)
			->setMaxResults(1);

		$Colaboracao = $query->getOneOrNullResult();

		if($Colaboracao !== null)
			return $Colaboracao;
		elseif($vazio === true)
			return new self;
		else
			return null;
	}

}
