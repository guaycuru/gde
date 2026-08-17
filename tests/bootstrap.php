<?php

// Bootstrap dos testes: autoload + EntityManager somente-metadados.
// NAO inclui common/common.inc.php (sessao, auth, HTML) nem config.inc.php -
// as constantes minimas usadas pelas classes testadas sao definidas aqui.

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\Platforms\MySQLPlatform;

require_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('America/Sao_Paulo');

define('CONFIG_DEV_MODE', true);
define('CONFIG_URL', 'http://localhost/gde/');
define('CONFIG_FT_MIN_LENGTH', 3);
define('CONFIG_FTS_ENABLED', false); // FULLTEXT exige indices que o SchemaTool nao cria
define('CONFIG_SALT_SENHA_ANTIGA', 'salt_de_teste');
// Valor qualquer: o algoritmo do GDE\DAC_Token nao depende do conteudo do
// salt, so de haver um. Sem ele a classe falha fechada (Gerar devolve null).
define('CONFIG_DAC_TOKEN_SALT', 'salt_de_teste_dac');
define('CONFIG_ONLINE_TIMEOUT', 90);
define('CONFIG_AVALIACAO_MINIMO', 3);
define('RESULT_CACHE_AVAILABLE', false);

$proxyDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'gde42_test_proxies';
if(!is_dir($proxyDir))
	mkdir($proxyDir, 0777, true);

$config = ORMSetup::createAnnotationMetadataConfiguration(array(__DIR__.'/../classes'), true, $proxyDir);
$config->setProxyNamespace('Proxies');

// Platform explicita e sem host: nenhuma conexao real e aberta (mesma tecnica
// do build da imagem Docker em common/doctrine.inc.php) - suficiente para
// getClassMetadata() e os getters/setters magicos de Base. Qualquer teste que
// tente Load()/FindBy()/Save() vai falhar na conexao, o que e intencional:
// testes de unidade nao devem tocar o banco.
$connection = array(
	'driver' => 'pdo_mysql',
	'charset' => 'utf8',
	'platform' => new MySQLPlatform(),
);

$_EM = EntityManager::create($connection, $config);
\GDE\Base::_EM($_EM);
\DAC\Base::_EM($_EM);
