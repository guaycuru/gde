<?php

use Doctrine\ORM\EntityManager;
use GDE\EchoSQLLogger;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

/**
 * DAC
 */

// Create a simple "default" Doctrine ORM configuration for Annotations
$dac_config = ORMSetup::createAnnotationMetadataConfiguration(
	array(__DIR__.'/../classes/DAC'),
	true,//CONFIG_DEV_MODE,
	__DIR__.'/../proxies'
);
$dac_config->setQueryCache(new PhpFilesAdapter('doctrine_dac_query'));

if(defined('CONFIG_DAC_USE_RESULT_CACHE') && CONFIG_DAC_USE_RESULT_CACHE === true) {
	$dacResultCache = new PhpFilesAdapter('doctrine_dac_results');
	$dac_config->setResultCache($dacResultCache);
	unset($dacResultCache);
}

// Set the query logger
if(
	((defined('CONFIG_DAC_DB_LOGGER')) && (CONFIG_DAC_DB_LOGGER === true)) ||
	((defined('DEBUG_DB_LOGGER')) && (DEBUG_DB_LOGGER === true))
) {
	$dac_config->setSQLLogger(new EchoSQLLogger());
}

// DB connection options
$dac_connection = array(
	'driver' => CONFIG_DAC_DB_DRIVER,
	'host' => CONFIG_DAC_DB_HOST,
	'port' => CONFIG_DAC_DB_PORT,
	'user' => CONFIG_DAC_DB_USER,
	'password' => CONFIG_DAC_DB_PASS,
	'dbname' => CONFIG_DAC_DB_NAME,
	'charset' => 'utf8mb4',
	'driverOptions' => array()
);
if(defined('CONFIG_DAC_ENABLE_SSL') && (CONFIG_DAC_ENABLE_SSL === true)) {
	$dac_connection['driverOptions'][Pdo\Mysql::ATTR_SSL_CA] = defined('CONFIG_DAC_SSL_CA') ? CONFIG_DAC_SSL_CA : null;
	if(defined('CONFIG_DAC_SKIP_CERT_CHECK') && (CONFIG_DAC_SKIP_CERT_CHECK === true))
		$dac_connection['driverOptions'][Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = false;
	// Client cert/key sao opcionais dentro do modo SSL - so faz sentido montar isso se o SSL
	// realmente estiver habilitado acima; antes rodava sempre que as 3 constantes estivessem
	// definidas, mesmo com CONFIG_DAC_ENABLE_SSL desligado
	if(defined('CONFIG_DAC_SSL_KEY') && defined('CONFIG_DAC_SSL_CERT') && defined('CONFIG_DAC_SSL_CA')) {
		$dac_connection['driverOptions'][Pdo\Mysql::ATTR_SSL_KEY] = CONFIG_DAC_SSL_KEY;
		$dac_connection['driverOptions'][Pdo\Mysql::ATTR_SSL_CERT] = CONFIG_DAC_SSL_CERT;
		$dac_connection['driverOptions'][Pdo\Mysql::ATTR_SSL_CA] = CONFIG_DAC_SSL_CA;
	}
}

if(
	((!defined('CONFIG_DB_SOCKET')) || (empty(CONFIG_DB_SOCKET))) &&
	((!defined('CONFIG_DB_HOST')) || (empty(CONFIG_DB_HOST)))
)
	$dac_connection['platform'] = new \Doctrine\DBAL\Platforms\MySQLPlatform();

// Create the Entity Manager
$_DAC_EM = EntityManager::create($dac_connection, $dac_config);

// Set the Entity Manager in the Base class
DAC\Base::_EM($_DAC_EM);

// Unset non-global variables
unset($dac_config, $dac_connection);
