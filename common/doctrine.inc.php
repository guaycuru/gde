<?php

use \Doctrine\Common\Annotations\AnnotationRegistry,
	\Doctrine\ORM\EntityManager,
	\Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\PredisCache;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Logging\EchoSQLLogger;

// Composer Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Configuration
require_once(__DIR__.'/config.inc.php');

// Uncaught exception handler
require_once(__DIR__.'/exceptions.inc.php');

// Default namespace
$_namespace = 'GDE';

// Initialize the caching mechanism
$availableCaches = array(new ArrayCache());

if((defined('CONFIG_APCU_ENABLED')) && (CONFIG_APCU_ENABLED === true) && (function_exists('apcu_fetch'))) {
	// Initialize the APCu caching mechanism
	$availableCaches[] = new ApcuCache();
}

// Initialize the Redis caching mechanism
$resultCache = null;
if((defined('CONFIG_REDIS_ENABLED')) && (CONFIG_REDIS_ENABLED === true) && (class_exists('\Redis', false))) {
	try {
		$redis = new \Redis();
		$redis->connect(CONFIG_REDIS_HOST, CONFIG_REDIS_PORT);
		$redisCache = new \Doctrine\Common\Cache\RedisCache();
		$redisCache->setRedis($redis);
		$availableCaches[] = $redisCache;
		$resultCache = $redisCache;
		unset($redis);
	} catch(\Exception $e) {
		// Nao foi possivel conectar ao REDIS
	}
} elseif((defined('CONFIG_PREDIS_ENABLED')) && (CONFIG_PREDIS_ENABLED === true)) {
	// Initialize the PRedis caching mechanism, only if Redis is not already initialized
	try {
		$redis = new \Predis\Client(array(
			'scheme' => 'tcp',
			'host' => CONFIG_REDIS_HOST,
			'port' => CONFIG_REDIS_PORT,
		));
		$redis->connect();
		$redisCache = new PredisCache($redis);
		$availableCaches[] = $redisCache;
		$resultCache = $redisCache;
		unset($redis);
	} catch(\Predis\Connection\ConnectionException $e) {
		// Nao foi possivel conectar ao REDIS
	}
}

// Add all available caches to the cache chain
$_cache = new ChainCache($availableCaches);
unset($arrayCache, $redisCache, $availableCaches);

// Load Annotation Registry
AnnotationRegistry::registerLoader('class_exists');

// Create standard annotation reader
$reader = new Doctrine\Common\Annotations\AnnotationReader;

// Create the cached annotation reader
$cachedAnnotationReader = new Doctrine\Common\Annotations\CachedReader(
	$reader, // use reader
	$_cache // and a cache driver
);
unset($reader);

// Now we want to register our application entities, for that we need another metadata driver used for Entity namespace
$annotationDriver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver(
	$cachedAnnotationReader, // our cached annotation reader
	array(__DIR__.'/../classes') // paths to look in
);

// Create a driver chain for metadata reading
$driver = new Doctrine\Persistence\Mapping\Driver\MappingDriverChain();

// Register annotation driver for our application Entity namespace
$driver->addDriver($annotationDriver, $_namespace);
unset($annotationDriver);
unset($cachedAnnotationReader);

// Create the configuration, set the Metadata and Query caches and the Proxy dir
$config = new Configuration;
$config->setMetadataCacheImpl($_cache);
$config->setMetadataDriverImpl($driver);
$config->setQueryCacheImpl($_cache);
$config->setProxyDir(__DIR__.'/../proxies');
$config->setProxyNamespace('Proxies');
if((defined('CONFIG_RESULT_CACHE')) && (CONFIG_RESULT_CACHE === true) && ($resultCache !== null)) {
	$config->setResultCacheImpl($resultCache);
	define('RESULT_CACHE_AVAILABLE', true);
} else
	define('RESULT_CACHE_AVAILABLE', false);
unset($resultCache);

// Set the appropriated Proxy auto-generating method
if((!defined('CONFIG_DEV_MODE')) || (CONFIG_DEV_MODE === true))
	$config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_ALWAYS);
else
	$config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_NEVER);

// Set the query logger
if(
	((defined('CONFIG_DB_LOGGER')) && (CONFIG_DB_LOGGER === true)) ||
	((defined('DEBUG_DB_LOGGER')) && (DEBUG_DB_LOGGER === true))
) {
	$config->setSQLLogger(new EchoSQLLogger());
}

// DB connection options
$connection = array(
	'driver' => CONFIG_DB_DRIVER,
	'user' => CONFIG_DB_USER,
	'password' => CONFIG_DB_PASS,
	'dbname' => CONFIG_DB_NAME,
	'charset' => 'utf8'
);
if(CONFIG_DB_DRIVER == 'mysql_pdo') {
	$connection['driverOptions'] = array(
		// ToDo: Remover a mudanca do SQL Mode
		PDO::MYSQL_ATTR_INIT_COMMAND => 'sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'))'
	);
}
if((defined('CONFIG_DB_SOCKET')) && (!empty(CONFIG_DB_SOCKET)))
	$connection['unix_socket'] = CONFIG_DB_SOCKET;
elseif((defined('CONFIG_DB_HOST')) && (!empty(CONFIG_DB_HOST))) {
	$connection['host'] = CONFIG_DB_HOST;
	if((defined('CONFIG_DB_PORT')) && (!empty(CONFIG_DB_PORT)))
		$connection['port'] = CONFIG_DB_PORT;
}

// Create the Entity Manager
$_EM = EntityManager::create($connection, $config);

// Set the Entity Manager in the Base class
$_Base = ($_namespace != null) ? '\\'.$_namespace.'\\Base' : 'Base';
$_Base::_EM($_EM);

// Unset non-global variables
unset($config, $driver, $connection);
