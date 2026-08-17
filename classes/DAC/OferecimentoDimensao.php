<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dimensao
 *
 * @ORM\Table(name="vw_dimensao_horarios")
 * @ORM\Entity(readOnly=true)
 */
class OferecimentoDimensao extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="ANOTURMA", type="smallint", nullable=false)
	 */
	private $ano = '0';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="PTURMA", type="smallint", nullable=false)
	 */
	private $periodo = '0';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TPER", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	private $tipo_periodo = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=false, options={"fixed"=true})
	 */
	private $sigla_disciplina = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=false)
	 */
	private $nivel_disciplina = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CTURMA", type="string", length=3, nullable=false, options={"fixed"=true})
	 */
	private $turma = '';

	/**
	 * @var int
	 *
	 * 2: segunda, ..., 7: sabado
	 * @ORM\Id
	 * @ORM\Column(name="DSEM", type="smallint", nullable=false)
	 */
	private $dia = '0';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="HINIAULA", type="decimal", precision=4, scale=2, nullable=false, options={"default"="0.00"})
	 */
	private $horario = '0.00';

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="CSALA", type="string", length=5, nullable=true, options={"fixed"=true})
	 */
	private $nome_sala;

	/**
	 * @var Oferecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Oferecimento", inversedBy="dimensoes")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="ANOTURMA", referencedColumnName="ANOTURMA"),
	 *     @ORM\JoinColumn(name="PTURMA", referencedColumnName="PTURMA"),
	 *     @ORM\JoinColumn(name="CDISC", referencedColumnName="CDISC"),
	 *     @ORM\JoinColumn(name="TNIVDIS", referencedColumnName="TNIVDIS"),
	 *     @ORM\JoinColumn(name="CTURMA", referencedColumnName="CTURMA")
	 * })
	 */
	protected $oferecimento;

	/**
	 * @var Sala
	 *
	 * @ORM\ManyToOne(targetEntity="Sala")
	 * @ORM\JoinColumns({
	 *	  @ORM\JoinColumn(name="CSALA", referencedColumnName="CSALA")
	 * })
	 */
	protected $sala;

	/**
	 * @var \GDE\Dimensao
	 */
	private $_dado_gde = null;

	private static $_novos_dados_gde = array();

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return \GDE\Dimensao|null
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Dimensao {
		if($this->_dado_gde === null) {
			$Sala = null;
			if($load && $this->nome_sala != null)
				$Sala = \GDE\Sala::FindOneBy(array('nome' => $this->nome_sala));

			if($Sala === null && $this->nome_sala != null) {
				$Sala = Sala::Novo_Dado_GDE($this->nome_sala);
				$dirty = true;
			}

			$horario_gde = floor($this->horario);

			$Dimensao = ($load && ($Sala === null || $Sala->getId() != null)) ? \GDE\Dimensao::FindOneBy(array(
				'sala' => ($Sala !== null) ? $Sala->getId() : null,
				'dia' => $this->dia,
				'horario' => $horario_gde
			)) : null;

			if($Dimensao === null) {
				$Dimensao = self::Novo_Dado_GDE($Sala, $this->dia, $horario_gde);
				$dirty = true;
			}

			$this->_dado_gde = $Dimensao;
		}
		return $this->_dado_gde;
	}

	public static function Novo_Dado_GDE(\GDE\Sala $Sala, $dia, $horario) {
		$chave = (($Sala !== null) ? $Sala->getNome(false) : '') . $dia . $horario;
		if(!isset(self::$_novos_dados_gde[$chave])) {
			$Dimensao_GDE = new \GDE\Dimensao();
			$Dimensao_GDE->setSala($Sala);
			$Dimensao_GDE->setDia($dia);
			$Dimensao_GDE->setHorario($horario);
			self::$_novos_dados_gde[$chave] = $Dimensao_GDE;
		}
		return self::$_novos_dados_gde[$chave];
	}
}
