<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioToken
 *
 * @ORM\Table(name="gde_usuarios_tokens")
 * @ORM\Entity
 */
class UsuarioToken extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_token;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $token;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data_criacao;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $usuario;

	const SEPARADOR = '.';

	/**
	 * Gerar_Valor
	 *
	 * Gera um novo valor "aleatorio" para ser usado no Token
	 *
	 * @return string
	 */
	public static function Gerar_Valor() {
		return sha1(mt_rand()).sha1(mt_rand()).sha1(mt_rand());
	}

	/**
	 * Novo
	 *
	 * Gera um novo token para o $Usuario
	 *
	 * @param Usuario $Usuario
	 * @param bool $salvar
	 * @return bool|UsuarioToken
	 */
	public static function Novo(Usuario $Usuario, $salvar = true) {
		if($Usuario->getID() == null)
			return false;
		$Novo = new self;
		$Novo->setUsuario($Usuario);
		$Novo->setData_Criacao();
		$Novo->setToken(self::Gerar_Valor());
		if(($salvar === true) && ($Novo->Save(true) === false))
			return false;
		return $Novo;
	}

	/**
	 * Verificar
	 *
	 * Verifica o token em formato string
	 * Retorna false em caso de erro ou $Usuario em caso de sucesso
	 *
	 * @param $string
	 * @return false|Usuario
	 */
	public static function Verificar($string) {
		$dados = explode(self::SEPARADOR, $string);
		if(count($dados) != 3)
			return false;
		$Token = self::Load($dados[0]);
		if(
			($Token->getID() == null) || // Token nao encontrado
			($Token->getUsuario(false) === null) || // Usuario vazio!?
			($Token->getUsuario()->getID() != $dados[1]) || // ID de usuario nao confere
			($Token->getToken(false) != $dados[2]) // Token nao confere
		)
			return false;
		return $Token->getUsuario();
	}

	/**
	 * Em_String
	 *
	 * Retorna o valor a ser salvo no cookie para este token
	 *
	 * @return string
	 */
	public function Em_String() {
		return $this->getID().self::SEPARADOR.$this->getUsuario()->getID().self::SEPARADOR.$this->getToken();
	}
}
