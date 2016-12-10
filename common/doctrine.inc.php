<?php

require_once(__DIR__.'/config.inc.php');

use \Doctrine\Common\Annotations\AnnotationRegistry,
	\Doctrine\ORM\EntityManager,
	\Doctrine\ORM\Configuration,
	\Doctrine\Common\ClassLoader;

// Default namespace
$_namespace = 'GDE';

// Register the class auto loading mechanism
$loader = new ClassLoader($_namespace, __DIR__.'/../classes');
$loader->setFileExtension('.inc.php');
$loader->register();
unset($loader);

// Initialize the caching mechanism
$arrayCache = new \Doctrine\Common\Cache\ArrayCache;
if((defined('CONFIG_DEV_MODE')) && (CONFIG_DEV_MODE === false)) {
	$redis = new Redis();
	$redis->connect(CONFIG_REDIS_HOST, CONFIG_REDIS_PORT);
	$redisCache = new \Doctrine\Common\Cache\RedisCache;
	$redisCache->setRedis($redis);
	$_cache = new \Doctrine\Common\Cache\ChainCache([
		$arrayCache,
		$redisCache
	]);
	unset($redisCache);
} else
	$_cache = $arrayCache;
unset($arrayCache);

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
$driver = new Doctrine\ORM\Mapping\Driver\DriverChain();

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

// DB connection options
$connection = array(
	'driver' => 'pdo_mysql',
	'host' => CONFIG_DB_HOST,
	'user' => CONFIG_DB_USER,
	'password' => CONFIG_DB_PASS,
	'dbname' => CONFIG_DB_NAME,
	'charset' => 'utf8',
	'driverOptions' => array(
		1002 => 'SET NAMES utf8'
	)
);

// Create the Entity Manager
$_EM = EntityManager::create($connection, $config);

// Set the Entity Manager in the Base class
$_Base = ($_namespace != null) ? '\\'.$_namespace.'\\Base' : 'Base';
$_Base::_EM($_EM);

// Unset non-global variables
unset($config, $driver, $connection);
