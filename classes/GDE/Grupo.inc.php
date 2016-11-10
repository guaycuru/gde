<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Grupo
 *
 * @ORM\Table(name="gde_grupos", uniqueConstraints={@ORM\UniqueConstraint(name="apelido", columns={"apelido"})})
 * @ORM\Entity
 */
class Grupo extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_grupo", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_grupo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="apelido", type="string", length=16, nullable=false)
	 */
	protected $apelido;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="descricao", type="string", length=255, nullable=true)
	 */
	protected $descricao;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=1, nullable=false)
	 */
	protected $tipo = 't';

	/**
	 * @var string
	 *
	 * @ORM\Column(name="foto", type="string", length=16, nullable=true)
	 */
	protected $foto;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="site", type="string", length=255, nullable=true)
	 */
	protected $site;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="facebook", type="string", length=255, nullable=true)
	 */
	protected $facebook;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="orkut", type="string", length=255, nullable=true)
	 */
	protected $orkut;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="twitter", type="string", length=255, nullable=true)
	 */
	protected $twitter;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="ativo", type="boolean", nullable=false)
	 */
	protected $ativo = false;


}
