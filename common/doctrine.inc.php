<?php

require_once(__DIR__.'/config.inc.php');

use \Doctrine\Common\Annotations\AnnotationRegistry,
	\Doctrine\ORM\EntityManager,
	\Doctrine\ORM\Configuration,
	\Doctrine\Common\ClassLoader;

// Composer Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Default namespace
$_namespace = 'GDE';

// Register the class auto loading mechanism
$loader = new ClassLoader($_namespace, __DIR__.'/../classes');
$loader->setFileExtension('.inc.php');
$loader->register();
unset($loader);

// Initialize the caching mechanism
$availableCaches = array(new \Doctrine\Common\Cache\ArrayCache);

// Initialize the APCu caching mechanism
if((defined('CONFIG_APCU_ENABLED')) && (CONFIG_APCU_ENABLED === true)) {
	$availableCaches[] = new \Doctrine\Common\Cache\ApcuCache();
}

// Initialize the Redis caching mechanism
if((defined('CONFIG_REDIS_ENABLED')) && (CONFIG_REDIS_ENABLED === true)) {
	try {
		$redis = new \Redis();
		$redis->connect(CONFIG_REDIS_HOST, CONFIG_REDIS_PORT);
		$redisCache = new \Doctrine\Common\Cache\RedisCache;
		$redisCache->setRedis($redis);
		$availableCaches[] = $redisCache;
	} catch(\Exception $e) {

	}
}

// Initialize the PRedis caching mechanism
if((defined('CONFIG_PREDIS_ENABLED')) && (CONFIG_PREDIS_ENABLED === true)) {
	try {
		$client = new \Predis\Client(array(
			'scheme' => 'tcp',
			'host' => CONFIG_REDIS_HOST,
			'port' => CONFIG_REDIS_PORT,
		));
		$client->connect();
		$redisCache = new \Doctrine\Common\Cache\PredisCache($client);
		$availableCaches[] = $redisCache;
	} catch(\Exception $e) {

	}
}

// Add all available caches to the cache chain
$_cache = new \Doctrine\Common\Cache\ChainCache($availableCaches);
unset($arrayCache, $redisCache);

// Load Annotation Registry
AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

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
$driver = new Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();

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

// Set the appropriated Proxy auto-generating method
if((!defined('CONFIG_DEV_MODE')) || (CONFIG_DEV_MODE === true))
	$config->setAutoGenerateProxyClasses(\Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_ALWAYS);
else
	$config->setAutoGenerateProxyClasses(\Doctrine\Common\Proxy\AbstractProxyFactory::AUTOGENERATE_NEVER);

// Set the query logger
if((defined('CONFIG_DB_LOGGER')) && (CONFIG_DB_LOGGER === true))
	$config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());

// DB connection options
$connection = array(
	'driver' => 'pdo_mysql',
	'user' => CONFIG_DB_USER,
	'password' => CONFIG_DB_PASS,
	'dbname' => CONFIG_DB_NAME,
	'charset' => 'utf8',
	'driverOptions' => array(
		// ToDo: Remover a mudanca do SQL Mode
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8,sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'))'
	)
);
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
