<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ColaboracaoProfessor
 *
 * @ORM\Table(name="gde_colaboracao_professores",
 *  indexes={
 *     @ORM\Index(name="professor_campo_status", columns={"id_professor", "campo", "status"}),
 *     @ORM\Index(name="status", columns={"status"})
 *  }
 * )
 * @ORM\Entity
 */
class ColaboracaoProfessor extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_colaboracao;

	/**
	 * @var Professor
	 *
	 * @ORM\ManyToOne(targetEntity="Professor")
	 * @ORM\JoinColumn(name="id_professor", referencedColumnName="id_professor")
	 */
	protected $professor;

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
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $campo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
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

	const CAMPO_EMAIL = 'email';
	const CAMPO_INSTITUTO = 'instituto';
	const CAMPO_LATTES = 'lattes';
	const CAMPO_PAGINA = 'pagina';
	const CAMPO_SALA = 'sala';
	private static $_campos = array(
		self::CAMPO_EMAIL,
		self::CAMPO_INSTITUTO,
		self::CAMPO_LATTES,
		self::CAMPO_PAGINA,
		self::CAMPO_SALA
	);

	const STATUS_AUTORIZADA = 'a';
	const STATUS_PENDENTE = 'p';
	const STATUS_RECUSADA = 'r';

	/**
	 * @param $id_professor
	 * @param $campo
	 * @return bool
	 */
	public static function Existe_Colaboracao($id_professor, $campo) {
		$dql = 'SELECT COUNT(C.id_colaboracao) FROM '.get_class().' AS C '.
			'WHERE C.professor = ?1 AND C.campo = ?2 AND C.status != ?3';

		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $id_professor)
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

}
