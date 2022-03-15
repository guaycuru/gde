<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dado
 *
 * @ORM\Table(name="gde_dados")
 * @ORM\Entity
 */
class Dado extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $alunos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $alunos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $alunos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $ativos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $ativos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $ativos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $oferecimentos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $oferecimentos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $oferecimentos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $disciplinas;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $disciplinas_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $disciplinas_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $professores;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $salas;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_ativos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_ativos_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_ativos_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_acesso;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_acesso_grad;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_acesso_pos;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 */
	protected $usuarios_atividade;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=false)
	 */
	protected $ultima_atualizacao;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $min_catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=false)
	 */
	protected $max_catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $planejador_periodo_atual;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $planejador_periodo_proximo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $max_online;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $max_online_ts;

	const ID = 1;

	/**
	 * @param null $campo
	 * @return array|null
	 */
	public static function Pega_Dados($campo = null) {
		$Dados = self::Load(self::ID);
		if($Dados === null)
			return array();
		$array = get_object_vars($Dados);
		if($campo == null)
			return $array;
		return (isset($array[$campo])) ? $array[$campo] : null;
	}

	/**
	 * @return array
	 */
	public static function Limites_Catalogo() {
		$Dados = self::Load(self::ID);
		return array(
			'min' => $Dados->getMin_Catalogo(false),
			'max' => $Dados->getMax_Catalogo(false)
		);
	}

	public static function Atualizar($periodo, $flush = true) {
		$seis_meses = date('Y-m-d', mktime(0, 0, 0, date('m')-6));
		$connection = self::_EM()->getConnection();
		$Dados = self::Load(self::ID);

		$alunos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos")->fetchOne();
		if($alunos === false)
			return false;
		$Dados->setAlunos($alunos);

		$alunos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE nivel IS NOT NULL")->fetchOne();
		if($alunos_grad === false)
			return false;
		$Dados->setAlunos_Grad($alunos_grad);

		$alunos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE nivel_pos IS NOT NULL")->fetchOne();
		if($alunos_pos === false)
			return false;
		$Dados->setAlunos_Pos($alunos_pos);

		$ativos = $connection->executeQuery("SELECT COUNT(DISTINCT ra) AS total FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?)", array($periodo))->fetchOne();
		if($ativos === false)
			return false;
		$Dados->setAtivos($ativos);

		$ativos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?)) AND nivel IS NOT NULL", array($periodo))->fetchOne();
		if($ativos_grad === false)
			return false;
		$Dados->setAtivos_Grad($ativos_grad);

		$ativos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?)) AND nivel_pos IS NOT NULL", array($periodo))->fetchOne();
		if($ativos_pos === false)
			return false;
		$Dados->setAtivos_Pos($ativos_pos);

		$oferecimentos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_oferecimentos WHERE id_periodo = ?", array($periodo))->fetchOne();
		if($oferecimentos === false)
			return false;
		$Dados->setOferecimentos($oferecimentos);

		$oferecimentos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_oferecimentos AS O JOIN gde_disciplinas AS D ON (D.id_disciplina = O.id_disciplina) WHERE O.id_periodo = ? AND D.nivel = ?", array($periodo, Disciplina::NIVEL_GRAD))->fetchOne();
		if($oferecimentos_grad === false)
			return false;
		$Dados->setOferecimentos_Grad($oferecimentos_grad);

		$oferecimentos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_oferecimentos AS O JOIN gde_disciplinas AS D ON (D.id_disciplina = O.id_disciplina) WHERE O.id_periodo = ? AND D.nivel IN (?, ?)", array($periodo, Disciplina::NIVEL_POS, Disciplina::NIVEL_MP))->fetchOne();
		if($oferecimentos_pos === false)
			return false;
		$Dados->setOferecimentos_Pos($oferecimentos_pos);

		$disciplinas = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_disciplinas")->fetchOne();
		if($disciplinas === false)
			return false;
		$Dados->setDisciplinas($disciplinas);

		$disciplinas_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_disciplinas WHERE nivel = ?", array(Disciplina::NIVEL_GRAD))->fetchOne();
		if($disciplinas_grad === false)
			return false;
		$Dados->setDisciplinas_Grad($disciplinas_grad);

		$disciplinas_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_disciplinas WHERE nivel IN (?, ?)", Disciplina::$NIVEIS_POS)->fetchOne();
		if($disciplinas_pos === false)
			return false;
		$Dados->setDisciplinas_Pos($disciplinas_pos);

		$professores = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_professores")->fetchOne();
		if($professores === false)
			return false;
		$Dados->setProfessores($professores);

		$salas = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_salas")->fetchOne();
		if($salas === false)
			return false;
		$Dados->setSalas($salas);

		$usuarios = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE")->fetchOne();
		if($usuarios === false)
			return false;
		$Dados->setUsuarios($usuarios);

		$usuarios_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel IS NOT NULL)")->fetchOne();
		if($usuarios_grad === false)
			return false;
		$Dados->setUsuarios_Grad($usuarios_grad);

		$usuarios_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel_pos IS NOT NULL)")->fetchOne();
		if($usuarios_pos === false)
			return false;
		$Dados->setUsuarios_Pos($usuarios_pos);

		$usuarios_ativos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?))", array($periodo))->fetchOne();
		if($usuarios_ativos === false)
			return false;
		$Dados->setUsuarios_Ativos($usuarios_ativos);

		$usuarios_ativos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel IS NOT NULL) AND ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?))", array($periodo))->fetchOne();
		if($usuarios_ativos_grad === false)
			return false;
		$Dados->setUsuarios_Ativos_Grad($usuarios_ativos_grad);

		$usuarios_ativos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel_pos IS NOT NULL) AND ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?))", array($periodo))->fetchOne();
		if($usuarios_ativos_pos === false)
			return false;
		$Dados->setUsuarios_Ativos_Pos($usuarios_ativos_pos);

		$usuarios_acesso = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ultimo_acesso >= ?", array($seis_meses))->fetchOne();
		if($usuarios_acesso === false)
			return false;
		$Dados->setUsuarios_Acesso($usuarios_acesso);

		$usuarios_acesso_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ultimo_acesso >= ? AND ra IN (SELECT ra FROM gde_alunos WHERE nivel IS NOT NULL)", array($seis_meses))->fetchOne();
		if($usuarios_acesso_grad === false)
			return false;
		$Dados->setUsuarios_Acesso_Grad($usuarios_acesso_grad);

		$usuarios_acesso_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ultimo_acesso >= ? AND ra IN (SELECT ra FROM gde_alunos WHERE nivel_pos IS NOT NULL)", array($seis_meses))->fetchOne();
		if($usuarios_acesso_pos === false)
			return false;
		$Dados->setUsuarios_Acesso_Pos($usuarios_acesso_pos);

		$usuarios_atividade = $connection->executeQuery("SELECT COUNT(DISTINCT id_origem) AS total FROM gde_acontecimentos WHERE data >= ?", array($seis_meses))->fetchOne();
		if($usuarios_atividade === false)
			return false;
		$Dados->setUsuarios_Atividade($usuarios_atividade);

		$Dados->setUltima_Atualizacao();

		return $Dados->Save($flush);
	}

}
