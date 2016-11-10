<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForumPostagem
 *
 * @ORM\Table(name="gde_forum_postagens")
 * @ORM\Entity
 */
class ForumPostagem extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_postagem", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_postagem;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="titulo", type="string", length=255, nullable=false)
	 */
	protected $titulo;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="corpo", type="text", nullable=false)
	 */
	protected $corpo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_topico", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $id_topico;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_autor", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_autor;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_forum", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $id_forum;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data", type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="votos_pos", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $votos_pos = '0';

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="votos_neg", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $votos_neg = '0';

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Usuario", mappedBy="idPostagem")
	 */
	protected $id_usuario;


}
