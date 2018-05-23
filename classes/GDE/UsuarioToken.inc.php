<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioToken
 *
 * @ORM\Table(
 *  name="gde_usuarios_tokens",
 *   indexes={
 *     @ORM\Index(name="data_criacao", columns={"data_criacao"})
 *   }
 * )
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
	 * @ORM\Column(type="string", unique=true, nullable=false)
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
	 * @param $token
	 * @return bool
	 */
	public static function Existe($token) {
		return self::FindOneBy(array('token' => $token)) !== null;
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
		do {
			$valor = self::Gerar_Valor();
		} while (self::Existe($valor));
		$Novo->setToken($valor);
		if(($salvar === true) && ($Novo->Save(true) === false))
			return false;
		return $Novo;
	}

	/**
	 * Dados
	 *
	 * Extrai os dados da strng
	 *
	 * @param string $string
	 * @return array|false
	 */
	private static function Dados($string) {
		$dados = explode(self::SEPARADOR, $string);
		if(count($dados) != 3)
			return false;
		return array(
			'id_token' => $dados[0],
			'id_usuario' => $dados[1],
			'token' => $dados[2]
		);
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
		$dados = self::Dados($string);
		if($dados === false)
			return false;
		$Token = self::Load($dados['id_token']);
		if($Token->Conferir_Dados($dados['id_usuario'], $dados['token']) === false)
			return false;
		return $Token->getUsuario();
	}

	/**
	 * Excluir
	 *
	 * Exclui o token pelos dados do cookie
	 *
	 * @param string $string Dados do cookie
	 * @param bool $flush
	 * @return bool
	 */
	public static function Excluir($string, $flush = true) {
		$dados = self::Dados($string);
		if($dados === false)
			return true;
		$Token = self::Load($dados['id_token']);
		if($Token->Conferir_Dados($dados['id_usuario'], $dados['token']) === false)
			return false;
		return $Token->Delete($flush);
	}

	/**
	 * Conferir_Dados
	 *
	 * Confere se os dados fornecidos sao iguais aos deste Token
	 *
	 * @param integer $id_usuario
	 * @param string $token
	 * @return bool
	 */
	private function Conferir_Dados($id_usuario, $token) {
		return (
			($this->getID() != null) && // Token nao encontrado
			($this->getUsuario(false) !== null) && // Usuario vazio!?
			($this->getUsuario()->getID() == $id_usuario) || // ID de usuario nao confere
			($this->getToken(false) == $token) // Token nao confere
		);
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

	/**
	 * @return void
	 */
	public static function Remover_Tokens_Antigos() {
		$Data = new \DateTime();
		$Data->modify('-'.(CONFIG_COOKIE_DIAS+1).' days');
		$dql = "DELETE ".get_class()." AS T WHERE T.data_criacao < ?1";
		self::_EM()->createQuery($dql)->setParameters(array(1 => $Data))->execute();
	}
}
