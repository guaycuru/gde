<?php

namespace DAC;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aluno
 *
 * @ORM\Table(name="vw_alunos")
 * @ORM\Entity(readOnly=true)
 */
class Aluno extends Base {
	/**
	 * @var int RA
	 *
	 * @ORM\Id
	 * @ORM\Column(name="NREGALUN", type="integer", nullable=false)
	 */
	protected $ra;

	/**
	 * @var string|null Nome do Aluno
	 *
	 * @ORM\Column(name="NOME_ALUNO", type="string", nullable=true)
	 */
	protected $nome;

	/**
	 * @var int|null Codigo do Curso
	 *
	 * @ORM\Column(name="CCURSO", type="smallint", nullable=true)
	 */
	protected $codigo_curso;

	/**
	 * @var string|null Codigo Nivel do Curso
	 *
	 * @ORM\Column(name="CNIVCUR", type="string", length=1, nullable=true, options={"fixed"=true})
	 */
	protected $nivel_curso;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(name="PINGR", type="integer", nullable=true)
	 */
	protected $pingr;

	/**
	 * @var int|null Ano Ingresso Atual
	 *
	 * @ORM\Column(name="ANOING", type="integer", nullable=true)
	 */
	protected $ano_ingresso;

	/**
	 * @var string|null Situacao do Curso
	 *
	 * @ORM\Column(name="SITUACAO_CURSO", type="string", length=7, nullable=true)
	 */
	protected $situacao_curso;

	/**
	 * @var int|null Ano Catalogo
	 *
	 * @ORM\Column(name="DANOCAT", type="integer", nullable=true)
	 */
	protected $catalogo;

	/**
	 * @var int Tipo do Aluno
	 *
	 * @ORM\Column(name="TIPO", type="integer", nullable=false)
	 */
	protected $tipo = '0';

	/**
	 * @var string Codigo da Modalidade
	 *
	 * @ORM\Column(name="CMODAL", type="string", length=2, nullable=false, options={"fixed"=true})
	 */
	protected $sigla_modalidade = '';

	/**
	 * @var \GDE\Aluno
	 */
	private $_dado_gde;

	private static $_novos_dados_gde = array();

	public static function Novo($ra, $nome, $nivel_curso, $codigo_curso, $modalidade, $catalogo): self {
		$Aluno = new Aluno();
		$Aluno->ra = $ra;
		$Aluno->nome = $nome;
		$Aluno->nivel_curso = $nivel_curso;
		$Aluno->codigo_curso = $codigo_curso;
		$Aluno->sigla_modalidade = $modalidade;
		$Aluno->catalogo = $catalogo;
		return $Aluno;
	}

	/**
	 * @param bool $load Se deve carregar do DB
	 * @param \GDE\Base|null $Parent
	 * @param bool $dirty Se o objeto foi modificado
	 * @return \GDE\Aluno
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Dado_GDE($load = true, ?\GDE\Base $Parent = null, &$dirty = false): \GDE\Aluno {
		if($this->_dado_gde === null) {
			$Aluno = ($load) ? \GDE\Aluno::Load($this->ra) : null;
			if($Aluno->getId() == null) {
				$Aluno = self::Novo_Dado_GDE($this->ra);
				$dirty = true;
			}

			$nome = trim($this->nome);
			if($nome != null && $Aluno->getNome(false) != $nome) {
				$Aluno->setNome($nome);
				$dirty = true;
			}

			// Curso
			$Curso = ($load) ? \GDE\Curso::Por_Numero($this->codigo_curso, $this->nivel_curso) : null;
			if($Curso !== null) {
				if(in_array($this->nivel_curso, \GDE\Aluno::Listar_Niveis_Grad(true))) { // Grad
					// Curso
					if($Aluno->getCurso(false) === null || $Aluno->getCurso()->getNumero(false) != $this->codigo_curso) {
						$Aluno->setCurso($Curso);
						$dirty = true;
					}

					// Modalidade
					if($Aluno->getModalidade(false) != $this->sigla_modalidade) {
						$Aluno->setModalidade($this->sigla_modalidade);
						$dirty = true;
					}

					// Catalogo
					if($this->catalogo != null && $Aluno->getCatalogo(false) != $this->catalogo) {
						$Aluno->setCatalogo($this->catalogo);
						$dirty = true;
					}

					// Ingresso
					if($this->ano_ingresso != null && $Aluno->getIngresso(false) != $this->ano_ingresso) {
						$Aluno->setIngresso($this->ano_ingresso);
						$dirty = true;
					}

					// Situacao
					if($this->situacao_curso != null && $Aluno->getSituacao(false) != $this->situacao_curso) {
						$Aluno->setSituacao($this->situacao_curso);
						$dirty = true;
					}
				} elseif(in_array($this->nivel_curso, \GDE\Aluno::Listar_Niveis_Pos(true))) { // Pos
					// Curso
					if($Aluno->getCurso_Pos(false) === null || $Aluno->getCurso_Pos()->getNumero(false) != $this->codigo_curso) {
						$Aluno->setCurso_Pos($Curso);
						$dirty = true;
					}

					// Modalidade
					if($Aluno->getModalidade_Pos(false) != $this->sigla_modalidade) {
						$Aluno->setModalidade_Pos($this->sigla_modalidade);
						$dirty = true;
					}

					// Catalogo
					if($this->catalogo != null && $Aluno->getCatalogo_Pos(false) != $this->catalogo) {
						$Aluno->setCatalogo_Pos($this->catalogo);
						$dirty = true;
					}

					// Ingresso
					if($this->ano_ingresso != null && $Aluno->getIngresso_Pos(false) != $this->ano_ingresso) {
						$Aluno->setIngresso_Pos($this->ano_ingresso);
						$dirty = true;
					}

					// Situacao
					if($this->situacao_curso != null && $Aluno->getSituacao_Pos(false) != $this->situacao_curso) {
						$Aluno->setSituacao_Pos($this->situacao_curso);
						$dirty = true;
					}
				}
			}
			$this->_dado_gde = $Aluno;
		}

		return $this->_dado_gde;
	}

	public static function Novo_Dado_GDE($ra): \GDE\Aluno {
		$chave = $ra;
		if(!isset(self::$_novos_dados_gde[$chave])) {
			$Aluno_GDE = new \GDE\Aluno();
			$Aluno_GDE->setRA($ra);
			self::$_novos_dados_gde[$chave] = $Aluno_GDE;
		}
		return self::$_novos_dados_gde[$chave];
	}

}
