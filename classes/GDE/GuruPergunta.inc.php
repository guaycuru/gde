<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * GuruPergunta
 *
 * @ORM\Table(name="gde_guru_perguntas", indexes={@ORM\Index(name="id_usuario", columns={"id_usuario"})})
 * @ORM\Entity
 */
class GuruPergunta extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_pergunta", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_pergunta;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="pergunta", type="string", length=255, nullable=false)
	 */
	protected $pergunta;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="resposta", type="string", length=255, nullable=false)
	 */
	protected $resposta;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="hora", type="datetime", nullable=false)
	 */
	protected $hora;

	/**
	 * @var \GDEGdeUsuarios
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $id_usuario;


}
