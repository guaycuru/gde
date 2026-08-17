<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * CurriculoPleno
 *
 * @ORM\Table(name="vw_curriculo_pleno")
 * @ORM\Entity(readOnly=true)
 */
class CurriculoPleno extends Base {
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="DANOCAT", type="smallint", nullable=false)
	 */
	protected $catalogo = '0';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CCURSO", type="smallint", nullable=false)
	 */
	protected $codigo_curso = '0';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CNIVCUR", type="string", length=1, nullable=false, options={"fixed"=true})
	 */
	protected $nivel_curso = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CMODAL", type="string", length=2, nullable=false)
	 */
	protected $sigla_modalidade = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CDISC", type="string", length=5, nullable=false, options={"fixed"=true})
	 */
	protected $sigla_disciplina = '';

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(name="TNIVDIS", type="string", length=1, nullable=false)
	 */
	protected $nivel_disciplina = '';

	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(name="CBLODISC", type="integer", nullable=false)
	 */
	protected $numero_bloco = '0';

	/**
	 * @var int
	 *
	 * @ORM\Column(name="NCREDBLO", type="integer", nullable=false)
	 */
	protected $creditos_bloco = '0';

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso", inversedBy="modalidades")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CCURSO", referencedColumnName="CCURSO"),
	 *     @ORM\JoinColumn(name="CNIVCUR", referencedColumnName="CNIVCUR")
	 * })
	 */
	protected $curso;

	/**
	 * @var Modalidade
	 *
	 * @ORM\ManyToOne(targetEntity="Modalidade")
	 * @ORM\JoinColumns({
	 *     @ORM\JoinColumn(name="CCURSO", referencedColumnName="CCURSO"),
	 *     @ORM\JoinColumn(name="CNIVCUR", referencedColumnName="CNIVCUR"),
	 *     @ORM\JoinColumn(name="DANOCAT", referencedColumnName="DANOCAT"),
	 *     @ORM\JoinColumn(name="CMODAL", referencedColumnName="CMODAL")
	 * })
	 */
	protected $modalidade;

	/**
	 * @param $catalogo
	 * @param $codigo_curso
	 * @param $nivel_curso
	 * @param $sigla_modalidade
	 * @return CurriculoPleno[]
	 */
	public static function Listar_Eletivas($catalogo, $codigo_curso, $nivel_curso, $sigla_modalidade = '') {
		$dql = "SELECT C FROM ".static::class." AS C WHERE C.catalogo = ?1 AND C.codigo_curso = ?2 AND ".
			"C.nivel_curso = ?3 AND C.sigla_modalidade = ?4 AND C.numero_bloco > 0 AND C.creditos_bloco > 0 ".
			"ORDER BY C.numero_bloco";
		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $catalogo)
			->setParameter(2, $codigo_curso)
			->setParameter(3, $nivel_curso)
			->setParameter(4, $sigla_modalidade ?? '');
		return $query->getResult();
	}

	/**
	 * @param $catalogo
	 * @param \GDE\Curso $Curso
	 * @param \GDE\Modalidade|null $Modalidade
	 * @return \GDE\CurriculoEletiva[]
	 */
	public static function Conjuntos_GDE($catalogo, \GDE\Curso $Curso, ?\GDE\Modalidade $Modalidade): array {
		$Blocos = array();
		$ultimo_bloco = 0;
		$Bloco = null;
		$Somente_Eletivas = self::Listar_Eletivas($catalogo, $Curso->getNumero(false), $Curso->getNivel(false),
			($Modalidade !== null) ? $Modalidade->getSigla(false) : '');
		foreach($Somente_Eletivas as $CurriculoPleno) {
			if($CurriculoPleno->numero_bloco != $ultimo_bloco) {
				$ultimo_bloco = $CurriculoPleno->numero_bloco;
				$Bloco = new \GDE\CurriculoEletiva();
				$Bloco->setCurso($Curso);
				$Bloco->setModalidade($Modalidade);
				$Bloco->setCatalogo($catalogo);
				$Bloco->setCreditos($CurriculoPleno->creditos_bloco);
				$Blocos[] = $Bloco;
			}
			$Eletiva = new \GDE\CurriculoEletivaConjunto();
			if(strpos($CurriculoPleno->sigla_disciplina, '-') === false) {
				$Disciplina_DAC = Disciplina::FindOneBy(array('sigla' => $CurriculoPleno->sigla_disciplina, 'nivel' => $CurriculoPleno->nivel_disciplina));
				if($Disciplina_DAC !== null) {
					$Disciplina_GDE = $Disciplina_DAC->Dado_GDE(true, null);
					$Eletiva->setDisciplina($Disciplina_GDE);
				}
			}
			$Eletiva->setSigla($CurriculoPleno->sigla_disciplina);
			$Bloco->addConjuntos($Eletiva);
		}
		return $Blocos;
	}

	/**
	 * @param bool $load
	 * @param \GDE\Base|null $Parent
	 * @param false $dirty
	 * @return null
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): ?\GDE\Base {
		// Não existe equivalencia direta
		return null;
	}
}
