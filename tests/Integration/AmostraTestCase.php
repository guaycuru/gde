<?php

namespace GDE\Tests\Integration;

/**
 * Base dos testes que precisam de dados realistas: carrega tests/Fixtures/
 * gde_amostra.sql, uma fatia real e anonimizada de um banco GDE com o "mundo"
 * do usuario 1 (login1, RA 70812) - curriculo, pre-requisitos, 74 eliminadas,
 * historico de 92 oferecimentos cursados, 33 planejados e 37 compromissos
 * extras.
 *
 * A amostra e carregada uma vez por classe de teste, FORA da transacao, e
 * removida no fim (tearDownAfterClass) para que as outras classes continuem
 * vendo o banco vazio. Cada teste segue rodando dentro da transacao com
 * rollback herdada de IntegrationTestCase, entao um teste nunca ve as
 * escritas de outro - so a amostra, que e imutavel.
 *
 * Para regerar a amostra (ver tests/Fixtures/extrair.php):
 *   FIXTURE_DB_NAME=gde42_prod php tests/Fixtures/extrair.php
 */
abstract class AmostraTestCase extends IntegrationTestCase {

	/** Usuario 1 do pacote de exemplo */
	const ID_USUARIO = 1;
	const RA = 70812;
	const ID_CURSO = 31;
	const ID_MODALIDADE = 1069;
	const CATALOGO = 2007;
	/** Periodos do planejador na amostra (gde_dados) */
	const PERIODO_ATUAL = 20232;
	const PERIODO_PROXIMO = 20241;

	private static $_amostra = null;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		if(static::_Indisponivel() !== null)
			return;

		$arquivo = __DIR__.'/../Fixtures/gde_amostra.sql';
		if(!is_file($arquivo))
			throw new \RuntimeException(
				'Amostra nao encontrada: '.$arquivo.' - rode php tests/Fixtures/extrair.php'
			);

		self::$_amostra = self::Statements($arquivo);
		self::Executar(self::$_amostra);
	}

	public static function tearDownAfterClass(): void {
		if((static::_Indisponivel() !== null) || (self::$_amostra === null))
			return;
		// Deixa o banco vazio de novo para as demais classes de teste
		$limpeza = array('SET FOREIGN_KEY_CHECKS=0');
		foreach(self::$_amostra as $sql)
			if(strncmp($sql, 'DELETE FROM ', 12) === 0)
				$limpeza[] = $sql;
		$limpeza[] = 'SET FOREIGN_KEY_CHECKS=1';
		self::Executar($limpeza);
		self::$_amostra = null;
	}

	// O extrator gera um statement por linha justamente para dispensar um
	// parser de SQL aqui (valores tem \n e \r escapados)
	private static function Statements($arquivo) {
		$out = array();
		foreach(file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
			$linha = trim($linha);
			if(($linha === '') || (strncmp($linha, '--', 2) === 0))
				continue;
			$out[] = rtrim($linha, ';');
		}
		return $out;
	}

	private static function Executar(array $statements) {
		$conn = static::_EM_Teste()->getConnection();
		foreach($statements as $sql)
			$conn->executeStatement($sql);
	}
}
