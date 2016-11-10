<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Professore
 *
 * @ORM\Table(name="gde_professores", uniqueConstraints={@ORM\UniqueConstraint(name="matricula", columns={"matricula"})}, indexes={@ORM\Index(name="nome", columns={"nome"})})
 * @ORM\Entity
 */
class Professor extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_professor", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_professor;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="matricula", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $matricula;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_instituto", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_instituto;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="sala", type="string", length=255, nullable=true)
	 */
	protected $sala;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="email", type="string", length=255, nullable=true)
	 */
	protected $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="pagina", type="string", length=255, nullable=true)
	 */
	protected $pagina;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="lattes", type="string", length=255, nullable=true)
	 */
	protected $lattes;


}
