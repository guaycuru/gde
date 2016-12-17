<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Recomendacao
 *
 * @ORM\Table(
 *   name="gde_recomendacoes",
 *   indexes={@ORM\Index(name="login", columns={"login"}), @ORM\Index(name="recomendado", columns={"recomendado"})}
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
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $login;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $recomendado;

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
		return (
			(Usuario::FindOneBy(array('email' => $this->getEmail(false))) !== null) ||
			(Usuario::FindOneBy(array('ra' => $this->getRA(false))) !== null) ||
			(self::FindOneBy(array('email' => $this->getEmail(false))) !== null) ||
			(self::FindOneBy(array('ra' => $this->getRA(false))) !== null)
		);
	}

	public function Verificar() {
		$erros = array();

		if((strlen($this->login) < 3) || (strlen($this->login) > 16))
			$erros[] = "O login deve ter no m&iacute;nimo 3 e no m&aacute;ximo 16 caracteres.";

		if(preg_match('/^[A-Z0-9_]+/i', $this->login) == 0)
			$erros[] = "O login digitado &eacute; inv&aacute;lido. Deve conter apenas letras, n&uacute;meros e underscores.";

		if((strlen($this->email) < 2) || (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $this->email) == 0))
			$erros[] = "O email digitado &eacute; inv&aacute;lido.";

		if($this->Existe_Dados() === true)
			$erros[] = "O email ou o RA digitado j&aacute; est&aacute; cadastrado no sistema.";

		if(count($erros) == 0)
			return true;
		else
			return $erros;
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
