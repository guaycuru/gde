<?php

namespace GDE\Tests\Integration;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use GDE\Base;
use PHPUnit\Framework\TestCase;

/**
 * Base dos testes de integracao: conecta no banco de teste (docker compose
 * up -d db-test), cria o schema das entidades GDE e roda cada teste dentro
 * de uma transacao com rollback. Se o banco nao estiver disponivel, os
 * testes sao marcados como skipped.
 */
abstract class IntegrationTestCase extends TestCase {
	/** @var EntityManager|null */
	private static $_IntEM = null;
	/** @var EntityManager|null */
	private static $_UnitEM = null;
	private static $_schema_criado = false;
	private static $_indisponivel = null;

	/**
	 * EntityManager do banco de teste, ou null se ele nao estiver disponivel.
	 * Para subclasses que precisam falar SQL direto (ver AmostraTestCase).
	 *
	 * @return EntityManager|null
	 */
	protected static function _EM_Teste() {
		return self::$_IntEM;
	}

	/**
	 * Motivo pelo qual o banco de teste esta indisponivel, ou null.
	 *
	 * @return string|null
	 */
	protected static function _Indisponivel() {
		return self::$_indisponivel;
	}

	// getenv com default que respeita valores vazios (ex: senha em branco)
	private static function Env($nome, $padrao) {
		$valor = getenv($nome);
		return ($valor === false) ? $padrao : $valor;
	}

	public static function setUpBeforeClass(): void {
		if((self::$_indisponivel !== null) || (self::$_IntEM !== null))
			return;

		$proxyDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'gde42_test_proxies';
		$config = ORMSetup::createAnnotationMetadataConfiguration(array(__DIR__.'/../../classes'), true, $proxyDir);
		$config->setProxyNamespace('Proxies');

		// Defaults apontam para o db-test do docker-compose; tests/db.local.php
		// (nao commitado) sobrescreve; variaveis TEST_DB_* tem precedencia final
		$local = (is_file(__DIR__.'/../db.local.php')) ? require __DIR__.'/../db.local.php' : array();
		$connection = array(
			'driver' => 'pdo_mysql',
			'host' => self::Env('TEST_DB_HOST', $local['host'] ?? '127.0.0.1'),
			'port' => self::Env('TEST_DB_PORT', $local['port'] ?? '3307'),
			'user' => self::Env('TEST_DB_USER', $local['user'] ?? 'gde'),
			'password' => self::Env('TEST_DB_PASS', $local['password'] ?? 'gde'),
			'dbname' => self::Env('TEST_DB_NAME', $local['dbname'] ?? 'gde_test'),
			'charset' => 'utf8',
		);

		try {
			$EM = EntityManager::create($connection, $config);
			$EM->getConnection()->connect();

			if(!self::$_schema_criado) {
				// Cria/atualiza somente as tabelas das entidades GDE (as DAC\*
				// pertencem ao banco institucional e nao sao testadas aqui)
				$metas = array();
				foreach($EM->getMetadataFactory()->getAllMetadata() as $meta) {
					if(strpos($meta->getName(), 'GDE\\') === 0)
						$metas[] = $meta;
				}
				(new SchemaTool($EM))->updateSchema($metas, true);
				self::$_schema_criado = true;
			}

			self::$_IntEM = $EM;
		} catch(\Throwable $e) {
			self::$_indisponivel = $e->getMessage();
		}
	}

	protected function setUp(): void {
		if(self::$_indisponivel !== null)
			$this->markTestSkipped(
				'Banco de teste indisponivel (docker compose up -d db-test): '.self::$_indisponivel
			);
		self::$_UnitEM = Base::_EM();
		Base::_EM(self::$_IntEM);
		Base::StartTrans();
	}

	protected function tearDown(): void {
		if(self::$_indisponivel !== null)
			return;
		Base::CompleteTrans(false);
		self::$_IntEM->clear();
		Base::_EM(self::$_UnitEM);
	}
}
