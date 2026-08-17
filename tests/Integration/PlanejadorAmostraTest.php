<?php

namespace GDE\Tests\Integration;

use GDE\Disciplina;
use GDE\Planejado;
use GDE\Usuario;

/**
 * Caracterizacao do planejador com dados REAIS (ver AmostraTestCase): o
 * usuario 1 e aluno de Engenharia de Computacao com curriculo cadastrado, 74
 * eliminadas e historico de 92 oferecimentos cursados.
 *
 * Cobre o que PlanejadoMontarDadosTest nao alcanca: la o usuario e simulado e
 * sem curriculo, entao a Arvore classifica tudo como Extra-Curricular, nenhum
 * pre-requisito e avaliado e nao ha reserva de vagas para violar.
 *
 * Planejado 28155: 2o semestre de 2011 (periodo atual 20111), 12
 * oferecimentos planejados, 3 eliminadas marcadas e 1 compromisso extra.
 *
 * ATENCAO: Montar_Dados/Montar_Arvore ALTERAM o Planejado e o Usuario que
 * recebem (ver testMontarDadosFiltraAsEliminadasDoUsuarioEmMemoria), entao
 * cada teste recarrega os dois no setUp e nunca chama dois deles em sequencia.
 */
class PlanejadorAmostraTest extends AmostraTestCase {

	const ID_PLANEJADO = 28155;
	/** F 429 - Fisica Experimental IV, obrigatoria do 4o semestre, 14 turmas */
	const ID_F429 = 2754;
	/** AM042 - fora do curriculo, 4 turmas, cursavel */
	const ID_AM042 = 8756;
	/** Planejado com 7 compromissos extras */
	const ID_PLANEJADO_EXTRAS = 86974;

	/** @var Usuario */
	private $Usuario;
	/** @var Planejado */
	private $Planejado;

	protected function setUp(): void {
		parent::setUp();
		$this->Usuario = Usuario::Load(self::ID_USUARIO);
		$this->Planejado = Planejado::Load(self::ID_PLANEJADO);
	}

	// -- o cenario -----------------------------------------------------------

	public function testAAmostraTemOAlunoComCurriculoEHistorico() {
		$Aluno = $this->Usuario->getAluno(false);
		$this->assertNotNull($Aluno, 'sem Aluno a Arvore nao roda');
		$this->assertSame(self::RA, $Aluno->getRA());
		$this->assertSame(self::ID_CURSO, $this->Usuario->getCurso()->getId());
		$this->assertSame(self::CATALOGO, $this->Usuario->getCatalogo());
		$this->assertCount(92, $Aluno->getOferecimentos());
		$this->assertCount(74, $this->Usuario->getEliminadas());
		$this->assertFalse($this->Planejado->getSimulado());
	}

	// -- Montar_Dados --------------------------------------------------------

	public function testMontarDadosDescreveOPlanejado() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$this->assertSame(20112, $Ret['Planejado']['periodo']);
		$this->assertSame(20111, $Ret['Planejado']['periodo_atual']);
		$this->assertSame('t', $Ret['Planejado']['compartilhado']);
		$this->assertSame('f', $Ret['Planejado']['simulado']);
	}

	/**
	 * O ponto do dataset real: com curriculo cadastrado cada disciplina ganha o
	 * semestre em que aparece na grade e as eletivas ganham 'E'. Com o usuario
	 * simulado de PlanejadoMontarDadosTest seria tudo 'N'.
	 */
	public function testMontarDadosUsaOSemestreDoCurriculo() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$semestres = array();
		foreach($Ret['Oferecimentos'] as $Bloco)
			$semestres[] = $Bloco['Disciplina']['semestre'];

		$this->assertSame(
			array('N', 'N', 'N', 'N', 'N', 'N', 'N', 3, 3, 4, 4, 4, 5, 6, 7, 7, 8, 8, 8, 9, 'E', 'E', 'E', 'E'),
			$semestres,
			"fora do curriculo vem 'N' na frente, obrigatorias numeradas, eletivas 'E' no fim"
		);
	}

	public function testMontarDadosClassificaAsDisciplinasNaArvore() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		// Os tres tipos aparecem, chaveados pelo id da disciplina
		$this->assertSame(
			array(
				8756 => 'Extra-Curricular',
				4233 => 'Eletiva',
				2748 => 'Obrigat&oacute;ria',
				4188 => 'Obrigat&oacute;ria',
				896 => 'Obrigat&oacute;ria',
				897 => 'Obrigat&oacute;ria',
				890 => 'Obrigat&oacute;ria',
			),
			$Ret['Arvore']['tipos']
		);
	}

	public function testMontarDadosTrazCoeficientesEIntegralizacaoReais() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		// Formatados com 4 casas e virgula decimal
		$this->assertSame('0,7884', $Ret['Arvore']['cp']);
		$this->assertSame('0,8714', $Ret['Arvore']['cpf']);
		$this->assertStringContainsString('Engenharia de Computa', $Ret['Arvore']['integralizacao']);
		$this->assertStringContainsString('070812', $Ret['Arvore']['integralizacao']);
	}

	/**
	 * Pre-requisitos de verdade sendo avaliados - o caminho que o usuario
	 * simulado nunca alcanca, porque sem curriculo tudo e 'pode'. O 'obs' traz
	 * um motivo generico ou a sigla que esta faltando.
	 */
	public function testMontarDadosMarcaOMotivoDeNaoPoderCursar() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$obs = array();
		foreach($Ret['Oferecimentos'] as $Bloco)
			if($Bloco['Disciplina']['obs'] !== null)
				$obs[$Bloco['Disciplina']['sigla']] = $Bloco['Disciplina']['obs'];

		$this->assertSame(
			array(
				'EE531' => 'ja_cursou',
				'MC039' => 'ja_cursou',
				'MC722' => 'ja_cursou',
				'MC822' => 'ja_cursou',
				'MC919' => 'AA200',
				'MC930' => 'ja_cursou',
				'EM423' => 'falta_pre',
				'F 428' => 'falta_pre',
				'MC019' => 'AA200',
				'MC030' => 'AA200',
				'MC040' => 'AA200',
				'MC041' => 'falta_pre',
			),
			$obs,
			"'ja_cursou'/'falta_pre' sao genericos; uma sigla e o pre-requisito que falta"
		);

		// Ter 'obs' NAO implica estar barrada: os motivos genericos
		// ('ja_cursou'/'falta_pre') bloqueiam, mas uma sigla em 'obs' e so um
		// aviso de qual pre-requisito falta - a disciplina segue cursavel
		// (MC919 esta ate entre as planejadas)
		foreach($Ret['Oferecimentos'] as $Bloco) {
			$D = $Bloco['Disciplina'];
			$bloqueia = in_array($D['obs'], array('ja_cursou', 'falta_pre'), true);
			$this->assertSame(!$bloqueia, $D['pode'], $D['sigla'].' obs='.var_export($D['obs'], true));
		}
	}

	public function testMontarDadosTrazAsTurmasComHorariosEProfessores() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);
		$Bloco = $Ret['Oferecimentos']['D'.self::ID_F429];

		$this->assertSame('F 429', $Bloco['Disciplina']['sigla']);
		$this->assertSame('F_429', $Bloco['Disciplina']['siglan']);
		$this->assertSame(4, $Bloco['Disciplina']['semestre']);
		$this->assertSame(2, $Bloco['Disciplina']['creditos']);
		$this->assertSame(
			array('A', 'B', 'C', 'D', 'E', 'G', 'H', 'K', 'L', 'M', 'O', 'P', 'U', 'Y'),
			array_keys($Bloco['Oferecimentos'])
		);

		$A = $Bloco['Oferecimentos']['A'];
		$this->assertSame(24, $A['vagas']); // int no planejador inteiro
		$this->assertFalse($A['fechado']);
		$this->assertTrue($A['possivel']);
		$this->assertSame(array('414', '415'), $A['horarios']);
		$this->assertSame('F 429 A (24)', $A['link']);
		$this->assertCount(1, $A['professores']);
		$this->assertSame('Professor 2694', $A['professores'][0]['nome']);
		// Sem avaliacoes cadastradas na amostra as medias vem como -1
		$this->assertSame(-1, $A['professores'][0]['mediap']);
		$this->assertCount(1, $A['eventSources']['events']);
	}

	/**
	 * Reserva de vagas por curso/catalogo: so da para exercitar com as
	 * gde_oferecimentos_reservas reais.
	 */
	public function testMontarDadosMarcaViolacaoDeReservaPorTurma() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$viola = array();
		foreach($Ret['Oferecimentos']['D'.self::ID_F429]['Oferecimentos'] as $t => $O)
			$viola[$t] = $O['viola_reserva'];

		$this->assertSame(
			array(
				'A' => false, 'B' => false, 'C' => false, 'D' => false,
				'E' => false, 'G' => false, 'H' => true, 'K' => false,
				'L' => false, 'M' => false, 'O' => false, 'P' => false,
				'U' => true, 'Y' => true,
			),
			$viola
		);
	}

	public function testMontarDadosMarcaComoAdicionadosOsOferecimentosPlanejados() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$adicionados = array();
		foreach($Ret['Oferecimentos'] as $Bloco)
			foreach($Bloco['Oferecimentos'] as $t => $O)
				if($O['adicionado'] === true)
					$adicionados[] = $Bloco['Disciplina']['sigla'].' '.$t;

		// Dos 12 planejados so aparecem 7: as disciplinas ja cursadas nao
		// listam turma nenhuma (ver o 'ja_cursou' em obs)
		$this->assertSame(
			array('AM042 B', 'MC919 A', 'F 315 B', 'MC548 A', 'CE838 A', 'CE839 B', 'CE738 B'),
			$adicionados
		);
	}

	public function testMontarDadosDevolveOsProximosIndicesDeCor() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$this->assertCount(24, $Ret['Oferecimentos']);
		// Os indices dao a volta na paleta: 24 disciplinas e 1 extra
		$this->assertSame(3, $Ret['c']);
		$this->assertSame(1, $Ret['ce']);
	}

	/** Compromissos pessoais do planejador, que o dataset sintetico nao tem */
	public function testMontarDadosTrazOsCompromissosExtras() {
		$Ret = $this->Planejado->Montar_Dados($this->Usuario);

		$this->assertCount(1, $Ret['Extras']);
		$this->assertSame(
			array(
				'id' => 'extra_16',
				'title' => 'Compromisso 1',
				'start' => '2003-12-05T12:00:00-03:00',
				'end' => '2003-12-05T18:00:00-03:00',
				'editable' => true,
				'color' => '#604F99',
			),
			array_values($Ret['Extras'])[0]
		);
	}

	public function testMontarDadosTrazVariosCompromissosExtras() {
		$Planejado = Planejado::Load(self::ID_PLANEJADO_EXTRAS);
		$Ret = $Planejado->Montar_Dados($this->Usuario);

		$this->assertCount(7, $Ret['Extras']);
		$this->assertSame(2, $Ret['ce']); // 7 extras dando a volta numa paleta de 5
		$primeiro = array_values($Ret['Extras'])[0];
		$this->assertSame('extra_14502', $primeiro['id']);
		$this->assertSame('Compromisso 14', $primeiro['title']);
		$this->assertTrue($primeiro['editable']);
	}

	/**
	 * Efeito colateral real: a Arvore reescreve a colecao de eliminadas do
	 * objeto Usuario em memoria, mantendo so as de graduacao ate o periodo
	 * planejado (Arvore.php:163 e :291). Nao vai ao banco porque
	 * Usuario_Para_Arvore marca o usuario como read-only antes.
	 */
	public function testMontarDadosFiltraAsEliminadasDoUsuarioEmMemoria() {
		$this->assertCount(74, $this->Usuario->getEliminadas());

		$this->Planejado->Montar_Dados($this->Usuario);

		$this->assertCount(54, $this->Usuario->getEliminadas());
	}

	// -- Montar_Arvore -------------------------------------------------------

	public function testMontarArvoreClassificaPorSigla() {
		$Arvore = $this->Planejado->Montar_Arvore($this->Usuario);

		$this->assertFalse($Arvore->getErro());
		$this->assertSame('0,7884', $Arvore->getCP(4));
		$this->assertSame('0,8714', $Arvore->getCPF(4));
		$this->assertSame('Obrigat&oacute;ria', $Arvore->getTipo('F 328', false));
		$this->assertSame('Obrigat&oacute;ria', $Arvore->getTipo('MC102', false));
	}

	// -- Montar_Dados_Disciplina ---------------------------------------------

	public function testMontarDadosDisciplinaDivergeDoPlanejadorInteiro() {
		$Ret = $this->Planejado->Montar_Dados_Disciplina($this->Usuario, Disciplina::Load(self::ID_AM042));

		$this->assertSame(array('Oferecimentos'), array_keys($Ret));
		$Bloco = $Ret['Oferecimentos'][self::ID_AM042];
		$this->assertSame('AM042', $Bloco['Disciplina']['sigla']);
		$this->assertTrue($Bloco['Disciplina']['pode']);
		$this->assertNull($Bloco['Disciplina']['obs']);
		// Sempre 'N': esta consulta nao olha o curriculo
		$this->assertSame('N', $Bloco['Disciplina']['semestre']);
		$this->assertSame(array('A', 'B', 'C', 'D'), array_keys($Bloco['Oferecimentos']));

		// Mesmas divergencias ja pinadas em PlanejadoMontarDadosTest, agora com
		// dados reais: vagas viram string, nunca marca adicionado e nao checa
		// reserva de vagas
		$A = $Bloco['Oferecimentos']['A'];
		$this->assertSame('25', $A['vagas']);
		$this->assertFalse($A['adicionado']);
		$this->assertArrayNotHasKey('viola_reserva', $A);
	}
}
