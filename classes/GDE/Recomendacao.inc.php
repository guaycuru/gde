<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Recomendacao
 *
 * @ORM\Table(
 *   name="gde_recomendacoes"
 * )
 * @ORM\Entity
 */
class Recomendacao extends Base {
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=16, nullable=false)
	 * @ORM\Id
	 */
	protected $chave;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumn(name="id_recomendante", referencedColumnName="id_usuario")
	 */
	protected $recomendante;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumn(name="id_recomendado", referencedColumnName="id_usuario")
	 */
	protected $recomendado;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", unique=true, options={"unsigned"=true}, nullable=false)
	 */
	protected $ra;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, unique=true, nullable=false)
	 */
	protected $email;

	/**
	 * getFinal
	 *
	 * Retorna o rodape do email
	 *
	 * @param Usuario $Usuario
	 * @return string
	 */
	public static function getFinal(Usuario $Usuario) {
		return "\r\n\r\nMensagem enviada por ".$Usuario->getNome(true)." pelo GDE - ".CONFIG_URL;
	}

	/**
	 * Define uma nova chave aleatoria e unica
	 *
	 * @return string
	 */
	public function setChave() {
		do {
			$chave = Util::Code(16);
		} while(self::FindOneBy(array('chave' => $chave)) !== null);
		return $this->chave = $chave;
	}

	/**
	 * Existe_Dados
	 *
	 * Verifica se ja existe um usuario ou recomendacao com o email ou RA informados
	 *
	 * @return bool
	 */
	public function Existe_Dados() {
		if(($this->getEmail() == null) && ($this->getRA() == null))
			return false;
		return (
			(Usuario::FindOneBy(array('email' => $this->getEmail(false))) !== null) ||
			(Usuario::FindOneBy(array('aluno' => $this->getRA(false))) !== null) ||
			(self::FindOneBy(array('email' => $this->getEmail(false))) !== null) ||
			(self::FindOneBy(array('ra' => $this->getRA(false))) !== null)
		);
	}

	/**
	 * Recomendar
	 *
	 * @param $Usuario
	 * @param $mensagem
	 * @return bool
	 */
	public function Recomendar($Usuario, $mensagem) {
		$res = mail($this->getEmail(true), 'Voce conhece o GDE?', strip_tags($mensagem).self::getFinal($Usuario, $this->getChave(true))."\n\n", 'From: '.$Usuario->getEmail(true) . "\r\n" .'Reply-To: '.$Usuario->getEmail(true) . "\r\n" . 'X-Mailer: PHP/' . phpversion());

		if($res !== false)
			return $this->Save(true);
		else
			return false;
	}

}
