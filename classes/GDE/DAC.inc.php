<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * DAC
 *
 * @ORM\Table(
 *  name="gde_dac_tokens",
 *   indexes={
 *     @ORM\Index(name="criacao", columns={"criacao"})
 *   }
 * )
 * @ORM\Entity
 */
class DAC extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id;

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
	protected $criacao;
	
	// Validade do token da DAC
	const VALIDADE_TOKEN = 1800;

	/**
	 * Validar_Token_DAC
	 *
	 * Valida o token fornecido pela DAC
	 *
	 * @param string $token O token fornecido pela DAC
	 * @param bool $salvar_token Se deve marcar o token como usado
	 * @return array
	 */
	public static function Validar_Token($token, $salvar_token = true) {
		// Retorna sempre false
		return array(false, 0, 0);
	}

	/**
	 * @param $token
	 * @param bool $flush
	 * @return mixed
	 */
	public static function Novo_Token($token, $flush = true) {
		$Novo = new self;
		$Novo->setToken($token);
		$Novo->setCriacao();
		return $Novo->Save($flush);
	}

	/**
	 * @param $token
	 * @return bool
	 */
	public static function Existe_Token($token) {
		return is_object(self::FindOneBy(array('token' => $token)));
	}

	/**
	 * @return void
	 */
	public static function Remover_Tokens_Antigos() {
		$Data = new \DateTime();
		$Data->modify('-'.(self::VALIDADE_TOKEN*2).' seconds');
		$dql = "DELETE ".get_class()." AS D WHERE D.criacao < ?1";
		self::_EM()->createQuery($dql)->setParameters(array(1 => $Data))->execute();
	}
}
