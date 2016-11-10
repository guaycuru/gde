<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * GrupoEvento
 *
 * @ORM\Table(name="gde_grupo_eventos")
 * @ORM\Entity
 */
class GrupoEvento extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_grupo_evento", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_grupo_evento;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_grupo", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_grupo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=255, nullable=true)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="descricao", type="string", length=255, nullable=true)
	 */
	protected $descricao;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="local", type="string", length=255, nullable=true)
	 */
	protected $local;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data_inicio", type="datetime", nullable=false)
	 */
	protected $data_inicio;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data_fim", type="datetime", nullable=false)
	 */
	protected $data_fim;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="dia_todo", type="boolean", nullable=false)
	 */
	protected $dia_todo = true;


}
