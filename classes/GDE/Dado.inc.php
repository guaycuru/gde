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
	 * @ORM\Column(name="id", type="boolean", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="alunos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $alunos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="alunos_grad", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $alunos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="alunos_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $alunos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="ativos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $ativos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="ativos_grad", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $ativos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="ativos_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $ativos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="materias", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $materias;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="materias_grad", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $materias_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="materias_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $materias_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="disciplinas", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $disciplinas;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="disciplinas_grad", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $disciplinas_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="disciplinas_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $disciplinas_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="professores", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $professores;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="salas", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $salas;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_grad", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_ativos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_ativos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_ativos_grad", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_ativos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_ativos_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_ativos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_acesso", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_acesso;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_acesso_grad", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_acesso_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_acesso_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_acesso_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="usuarios_atividade", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $usuarios_atividade;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="ultima_atualizacao", type="date", nullable=false)
	 */
	protected $ultima_atualizacao;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="min_catalogo", type="smallint", nullable=false)
	 */
	protected $min_catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="max_catalogo", type="smallint", nullable=false)
	 */
	protected $max_catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="planejador_periodo_atual", type="smallint", nullable=true)
	 */
	protected $planejador_periodo_atual;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="planejador_periodo_proximo", type="smallint", nullable=true)
	 */
	protected $planejador_periodo_proximo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="max_online", type="smallint", nullable=true)
	 */
	protected $max_online;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="max_online_ts", type="datetime", nullable=true)
	 */
	protected $max_online_ts;


}
