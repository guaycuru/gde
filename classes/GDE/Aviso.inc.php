<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aviso
 *
 * @ORM\Table(name="gde_avisos", uniqueConstraints={@ORM\UniqueConstraint(name="id_evento", columns={"id_evento"})}, indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"}), @ORM\Index(name="data", columns={"data"})})
 * @ORM\Entity
 */
class Aviso extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_aviso;

	/**
	 * @var \GDE\Evento
	 *
	 * @ORM\ManyToOne(targetEntity="Evento")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_evento", referencedColumnName="id_evento")
	 * })
	 */
	protected $evento;

	/**
	 * @var \GDE\Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $usuario;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_postagem;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $titulo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $tipo;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $aba = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $email = true;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $lido = false;

	public static function QuantidadeAvisos($id_usuario) {
		$query = self::_EM()->createQuery("SELECT COUNT(A.id_aviso) FROM GDE\\Aviso A WHERE A.usuario = ?1 AND A.lido = FALSE AND A.aba = TRUE");
		$query->setParameter(1, $id_usuario);
		return $query->getSingleScalarResult();
	}

	public static function Avisos($id_usuario) {
		$query = self::_EM()->createQuery("SELECT A FROM GDE\\Aviso A WHERE A.usuario = ?1 AND A.lido = FALSE AND A.aba = TRUE ORDER BY A.lido ASC, A.data DESC, A.id_aviso ASC");
		$query->setParameter(1, $id_usuario);
		return $query->getResults();
	}

}
