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
	 * @var boolean
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

		$alunos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos")->fetch();
		if($alunos === false)
			return false;
		$Dados->setAlunos($alunos['total']);

		$alunos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE nivel IS NOT NULL")->fetch();
		if($alunos_grad === false)
			return false;
		$Dados->setAlunos_Grad($alunos_grad['total']);

		$alunos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE nivel_pos IS NOT NULL")->fetch();
		if($alunos_pos === false)
			return false;
		$Dados->setAlunos_Pos($alunos_pos['total']);

		$ativos = $connection->executeQuery("SELECT COUNT(DISTINCT ra) AS total FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?)", array($periodo))->fetch();
		if($ativos === false)
			return false;
		$Dados->setAtivos($ativos['total']);

		$ativos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?)) AND nivel IS NOT NULL", array($periodo))->fetch();
		if($ativos_grad === false)
			return false;
		$Dados->setAtivos_Grad($ativos_grad['total']);

		$ativos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_alunos WHERE ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?)) AND nivel_pos IS NOT NULL", array($periodo))->fetch();
		if($ativos_pos === false)
			return false;
		$Dados->setAtivos_Pos($ativos_pos['total']);

		$oferecimentos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_oferecimentos WHERE id_periodo = ?", array($periodo))->fetch();
		if($oferecimentos === false)
			return false;
		$Dados->setOferecimentos($oferecimentos['total']);

		$oferecimentos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_oferecimentos AS O JOIN gde_disciplinas AS D ON (D.id_disciplina = O.id_disciplina) WHERE O.id_periodo = ? AND D.nivel IN (?, ?)", array($periodo, Disciplina::NIVEL_GRAD, Disciplina::NIVEL_TEC))->fetch();
		if($oferecimentos_grad === false)
			return false;
		$Dados->setOferecimentos_Grad($oferecimentos_grad['total']);

		$oferecimentos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_oferecimentos AS O JOIN gde_disciplinas AS D ON (D.id_disciplina = O.id_disciplina) WHERE O.id_periodo = ? AND D.nivel IN (?, ?)", array($periodo, Disciplina::NIVEL_POS, Disciplina::NIVEL_MP))->fetch();
		if($oferecimentos_pos === false)
			return false;
		$Dados->setOferecimentos_Pos($oferecimentos_pos['total']);

		$disciplinas = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_disciplinas")->fetch();
		if($disciplinas === false)
			return false;
		$Dados->setDisciplinas($disciplinas['total']);

		$disciplinas_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_disciplinas WHERE nivel IN (?, ?)", array(Disciplina::NIVEL_GRAD, Disciplina::NIVEL_TEC))->fetch();
		if($disciplinas_grad === false)
			return false;
		$Dados->setDisciplinas_Grad($disciplinas_grad['total']);

		$disciplinas_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_disciplinas WHERE nivel IN (?, ?)", array(Disciplina::NIVEL_POS, Disciplina::NIVEL_MP))->fetch();
		if($disciplinas_pos === false)
			return false;
		$Dados->setDisciplinas_Pos($disciplinas_pos['total']);

		$professores = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_professores")->fetch();
		if($professores === false)
			return false;
		$Dados->setProfessores($professores['total']);

		$salas = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_salas")->fetch();
		if($salas === false)
			return false;
		$Dados->setSalas($salas['total']);

		$usuarios = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE")->fetch();
		if($usuarios === false)
			return false;
		$Dados->setUsuarios($usuarios['total']);

		$usuarios_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel IS NOT NULL)")->fetch();
		if($usuarios_grad === false)
			return false;
		$Dados->setUsuarios_Grad($usuarios_grad['total']);

		$usuarios_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel_pos IS NOT NULL)")->fetch();
		if($usuarios_pos === false)
			return false;
		$Dados->setUsuarios_Pos($usuarios_pos['total']);

		$usuarios_ativos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?))", array($periodo))->fetch();
		if($usuarios_ativos === false)
			return false;
		$Dados->setUsuarios_Ativos($usuarios_ativos['total']);

		$usuarios_ativos_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel IS NOT NULL) AND ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?))", array($periodo))->fetch();
		if($usuarios_ativos_grad === false)
			return false;
		$Dados->setUsuarios_Ativos_Grad($usuarios_ativos_grad['total']);

		$usuarios_ativos_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ra IN (SELECT ra FROM gde_alunos WHERE nivel_pos IS NOT NULL) AND ra IN (SELECT ra FROM gde_r_alunos_oferecimentos WHERE id_oferecimento IN (SELECT id_oferecimento FROM gde_oferecimentos WHERE id_periodo = ?))", array($periodo))->fetch();
		if($usuarios_ativos_pos === false)
			return false;
		$Dados->setUsuarios_Ativos_Pos($usuarios_ativos_pos['total']);

		$usuarios_acesso = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ultimo_acesso >= ?", array($seis_meses))->fetch();
		if($usuarios_acesso === false)
			return false;
		$Dados->setUsuarios_Acesso($usuarios_acesso['total']);

		$usuarios_acesso_grad = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ultimo_acesso >= ? AND ra IN (SELECT ra FROM gde_alunos WHERE nivel IS NOT NULL)", array($seis_meses))->fetch();
		if($usuarios_acesso_grad === false)
			return false;
		$Dados->setUsuarios_Acesso_Grad($usuarios_acesso_grad['total']);

		$usuarios_acesso_pos = $connection->executeQuery("SELECT COUNT(*) AS total FROM gde_usuarios WHERE ativo = TRUE AND ultimo_acesso >= ? AND ra IN (SELECT ra FROM gde_alunos WHERE nivel_pos IS NOT NULL)", array($seis_meses))->fetch();
		if($usuarios_acesso_pos === false)
			return false;
		$Dados->setUsuarios_Acesso_Pos($usuarios_acesso_pos['total']);

		$usuarios_atividade = $connection->executeQuery("SELECT COUNT(DISTINCT id_origem) AS total FROM gde_acontecimentos WHERE data >= ?", array($seis_meses))->fetch();
		if($usuarios_atividade === false)
			return false;
		$Dados->setUsuarios_Atividade($usuarios_atividade['total']);

		$Dados->setUltima_Atualizacao();

		return $Dados->Save($flush);
	}

}
