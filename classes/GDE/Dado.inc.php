<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dado
 *
 * @ORM\Table(name="gde_dados")
 * @ORM\Entity
 */
class Dado extends Base {
	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $alunos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $alunos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $alunos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $ativos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $ativos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $ativos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $materias;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $materias_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $materias_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $disciplinas;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $disciplinas_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $disciplinas_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $professores;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $salas;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_ativos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_ativos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_ativos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_acesso;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_acesso_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_acesso_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_atividade;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=false)
	 */
	protected $ultima_atualizacao;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $min_catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $max_catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $planejador_periodo_atual;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $planejador_periodo_proximo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $max_online;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $max_online_ts;

	/**
	 * @param null $campo
	 * @return array|null
	 */
	public static function Pega_Dados($campo = null) {
		$Dados = self::Load(1);
		if($Dados === null)
			return array();
		$array = get_object_vars($Dados);
		if($campo == null)
			return $array;
		return (isset($array[$campo])) ? $array[$campo] : null;
	}

	/**
	 * @return array
	 */
	public static function Limites_Catalogo() {
		$Dados = self::Load(1);
		return array(
			'min' => $Dados->getMin_Catalogo(false),
			'max' => $Dados->getMax_Catalogo(false)
		);
	}

}
