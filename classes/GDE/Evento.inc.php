<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Evento
 *
 * @ORM\Table(
 *   name="gde_eventos",
 *   indexes={
 *     @ORM\Index(name="data_inicio_fim", columns={"data_inicio", "data_fim"})
 *   }
 * )
 * @ORM\Entity
 */
class Evento extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_evento;

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
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descricao;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $local;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data_inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_fim;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $dia_todo = false;

	const TIPO_FERIADO = 'f';
	const TIPO_GRADUACAO = 'g';
	const TIPO_OUTRO = 'o';
	const TIPO_PROVA = 'p';
	const TIPO_TRABALHO = 't';

	private static $_tipos = array(
		self::TIPO_FERIADO => 'Feriado',
		self::TIPO_GRADUACAO => 'Graduação',
		self::TIPO_PROVA => 'Prova',
		self::TIPO_TRABALHO => 'Trabalho',
		self::TIPO_OUTRO => 'Outro'
	);

	/**
	 * @return array
	 */
	public static function Listar_Tipos() {
		return self::$_tipos;
	}

	/**
	 * @param Usuario $Usuario
	 * @param \DateTime $Inicio
	 * @param \DateTime $Fim
	 * @return Evento[]
	 */
	public static function Listar_Por_Usuario_Datas(Usuario $Usuario, \DateTime $Inicio, \DateTime $Fim) {
		$dql = "SELECT E FROM ".get_class()." E ".
			"WHERE (E.usuario = :usuario OR E.usuario IS NULL) AND E.data_inicio <= :fim AND E.data_fim >= :inicio";
		$query = self::_EM()->createQuery($dql)
			->setParameter('usuario', $Usuario)
			->setParameter('inicio', $Inicio)
			->setParameter('fim', $Fim);
		return $query->getResult();
	}

	/**
	 * @param Usuario $Usuario
	 * @return bool
	 */
	public function Pode_Alterar(Usuario $Usuario) {
		if($this->getID() == null)
			return true;
		if(in_array($this->getTipo(false), array(self::TIPO_FERIADO, self::TIPO_GRADUACAO)))
			return false;
		if(($this->getUsuario(false) === null) || ($this->getUsuario()->getID() != $Usuario->getID()))
			return false;
		return true;
	}

}
