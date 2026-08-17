<?php

namespace GDE\Tests\Unit;

use DAC\Base;
use PHPUnit\Framework\TestCase;

/**
 * Mapeamento das entidades da DAC. Elas apontam para views, que nao tem chave
 * primaria, entao o identificador e sempre uma chave natural composta
 * escolhida a mao - e por isso da para errar.
 *
 * Sempre pelo DAC\Base::_EM(), que e quem responde por estas entidades desde
 * que common/doctrine.inc.php passou a escanear so classes/GDE. O EM da
 * aplicacao ainda resolve um getClassMetadata() explicito (o driver de
 * anotacoes so restringe a enumeracao, nao a carga sob demanda), mas depender
 * disso seria acidente. No bootstrap dos testes os dois apontam para o mesmo
 * EM de qualquer forma.
 */
class DacMappingTest extends TestCase {

	private function metadata($classe) {
		return Base::_EM()->getClassMetadata($classe);
	}

	/**
	 * O Doctrine recusa NULL em identificador (UnitOfWork::getIdHashByEntity
	 * faz in_array(null, $identifier, true) e lanca
	 * ORMInvalidArgumentException). ANO_FIM_PRE_REQU e null em 34,6% das linhas
	 * da view - e a regra ainda vigente, sem ano de termino - entao inclui-la
	 * no identificador derrubava o cron/dac_disciplinas.php ao hidratar a
	 * primeira dessas linhas.
	 */
	public function testAnoFimNaoEntraNoIdentificadorDosPreRequisitos() {
		$meta = $this->metadata(\DAC\DisciplinaPreRequisitos::class);

		$this->assertNotContains('ano_fim', $meta->getIdentifierFieldNames());
	}

	/**
	 * A chave que sobrou continua identificando as linhas uma a uma: medido na
	 * view, as 447.902 linhas seguem distintas por estas 5 colunas (ANO_INI e
	 * DESC_ATIV_CAD ja separam os conjuntos alternativos do mesmo periodo).
	 */
	public function testIdentificadorDosPreRequisitosEAChaveNaturalCompleta() {
		$meta = $this->metadata(\DAC\DisciplinaPreRequisitos::class);

		$this->assertSame(
			array('sigla', 'nivel', 'catalogo', 'ano_inicio', 'lista_siglas'),
			$meta->getIdentifierFieldNames()
		);
	}

	/** A coluna continua mapeada e legivel: Pre_Requisitos_Por_Ano() filtra por ela */
	public function testAnoFimContinuaMapeadoComoColunaNormal() {
		$meta = $this->metadata(\DAC\DisciplinaPreRequisitos::class);

		$this->assertArrayHasKey('ano_fim', $meta->fieldMappings);
		$this->assertSame('ANO_FIM_PRE_REQU', $meta->getColumnName('ano_fim'));
		$this->assertTrue($meta->fieldMappings['ano_fim']['nullable']);
	}

	/**
	 * Rede de seguranca para o resto do mapeamento da DAC: hoje ANO_FIM_PRE_REQU
	 * era a unica coluna de identificador que de fato trazia NULL na view (as
	 * demais marcadas como nullable nunca trazem). Se alguem incluir outra
	 * coluna nullable num identificador, que seja uma decisao consciente.
	 */
	public function testIdentificadoresDaDacConhecidos() {
		$nullables = array();
		foreach(Base::_EM()->getMetadataFactory()->getAllMetadata() as $meta) {
			if(($meta->isMappedSuperclass) || (strpos($meta->getName(), 'DAC\\') !== 0))
				continue;
			foreach($meta->getIdentifierFieldNames() as $campo) {
				if(!isset($meta->fieldMappings[$campo]))
					continue;
				if(!empty($meta->fieldMappings[$campo]['nullable']))
					$nullables[$meta->getName()][] = $campo;
			}
		}
		// getAllMetadata() nao garante ordem
		ksort($nullables);

		$this->assertSame(
			array(
				\DAC\Disciplina::class => array('nivel'),
				\DAC\DisciplinaContinencias::class => array('sigla'),
				\DAC\DisciplinaPreRequisitos::class => array('ano_inicio', 'lista_siglas'),
				\DAC\OferecimentoAluno::class => array('periodo', 'tipo_periodo', 'ano'),
				\DAC\OferecimentoDocente::class => array('ano', 'periodo', 'tipo_periodo', 'sigla_disciplina', 'turma', 'matricula'),
				\DAC\OferecimentoReserva::class => array('numero_curso', 'nivel_curso'),
			),
			$nullables,
			'mudou algum identificador da DAC - confira se a coluna nova pode ser NULL na view'
		);
	}
}
