<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sala
 *
 * @ORM\Table(name="vw_dimensao_salas")
 * @ORM\Entity(readOnly=true)
 */
class Sala extends Base {
	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CSALA", type="string", length=5, nullable=false, options={"fixed"=true})
	 */
	private $nome;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="MSALA", type="string", length=80, nullable=true, options={"fixed"=true})
	 */
	private $descricao;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="CUNIDLOC", type="string", length=4, nullable=true)
	 */
	private $cunidloc;

	/**
	 * @var \GDE\Sala
	 */
	private $_dado_gde = null;

	private static $_novos_dados_gde = array();

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\Sala|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Sala {
		if($this->_dado_gde === null) {
			$nome = trim($this->nome);
			$Sala = ($load) ? \GDE\Sala::Por_Nome($nome, false) : null;

			if($Sala === null) {
				$Sala = self::Novo_Dado_GDE($this->getNome(false));
				$dirty = true;
			}

			$descricao = trim($this->descricao);
			if($Sala->getDescricao(false) != $descricao) {
				$Sala->setDescricao($descricao);
				$dirty = true;
			}

			// ToDo: CUNIDLOC

			$this->_dado_gde = $Sala;
		}
		return $this->_dado_gde;
	}

	public static function Novo_Dado_GDE($nome): \GDE\Sala {
		$chave = $nome;
		if(!isset(self::$_novos_dados_gde[$chave])) {
			$Sala_GDE = new \GDE\Sala();
			$Sala_GDE->setNome($nome);
			self::$_novos_dados_gde[$chave] = $Sala_GDE;
		}
		return self::$_novos_dados_gde[$chave];
	}
}
