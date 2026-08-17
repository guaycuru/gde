<?php

namespace GDE\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * TRUNCATE e DDL: o MySQL da commit implicito ao executa-lo, encerrando
 * qualquer transacao aberta. O contador de aninhamento do DBAL nao percebe e
 * segue achando que ha transacao, o que produz tres efeitos silenciosos:
 *
 *  - tudo que vem depois roda em autocommit, sem rollback possivel;
 *  - a transacao que o flush() do Doctrine abre por dentro vira no-op, entao
 *    uma falha no meio do flush deixa metade das linhas gravadas;
 *  - um "dry run" que termina em CompleteTrans(false) apaga as tabelas de
 *    verdade, porque o TRUNCATE ja foi commitado.
 *
 * Aconteceu em producao em 2026-08-16 com cron/dac_disciplinas.php. A correcao
 * e usar DELETE, que e transacional no InnoDB.
 */
class CronTransacaoTest extends TestCase {

	/**
	 * Le o fonte do cron. A pasta cron/ nao existe no espelho publico do
	 * GitHub (os pega*.php sao scrapers da DAC e ficam fora), entao la o
	 * teste pula em vez de quebrar a suite inteira.
	 */
	private static function fonte($script) {
		$caminho = __DIR__.'/../../cron/'.$script;
		if(!is_file($caminho))
			self::markTestSkipped('cron/ indisponivel nesta copia do repositorio');
		return file_get_contents($caminho);
	}

	public static function scriptsTransacionais() {
		return array(
			'dac_disciplinas' => array('dac_disciplinas.php'),
		);
	}

	#[DataProvider('scriptsTransacionais')]
	public function testNaoUsaTruncateDentroDeTransacao($script) {
		$fonte = self::fonte($script);

		$this->assertStringContainsString('Base::StartTrans()', $fonte, 'o script deveria abrir transacao');
		$this->assertStringNotContainsStringIgnoringCase(
			'TRUNCATE TABLE',
			$fonte,
			'TRUNCATE encerra a transacao por commit implicito - use DELETE FROM'
		);
	}

	#[DataProvider('scriptsTransacionais')]
	public function testFechaATransacaoNosDoisModos($script) {
		$fonte = self::fonte($script);

		// Sem o commit a transacao morre com o processo e as escritas se perdem
		$this->assertStringContainsString('Base::CompleteTrans(true)', $fonte);
		$this->assertStringContainsString('Base::CompleteTrans(false)', $fonte);
		// E o commit nao pode estar comentado, como ficou entre f584600 e 2026-08-16
		$this->assertDoesNotMatchRegularExpression(
			'~/\*\s*if\s*\(\s*Base::CompleteTrans~',
			$fonte,
			'o commit da transacao esta comentado'
		);
	}

	public function testLimpaAsEquivalenciasComDelete() {
		$fonte = self::fonte('dac_disciplinas.php');

		foreach(array('gde_equivalencias_equivalentes', 'gde_equivalencias') as $tabela)
			$this->assertStringContainsString('DELETE FROM `'.$tabela.'`', $fonte);
	}
}
