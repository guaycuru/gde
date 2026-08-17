<?php

// Dev Mode
define('CONFIG_DEV_MODE', false);

// DB
define('CONFIG_DB_DRIVER', 'pdo_mysql');
define('CONFIG_DB_HOST', getenv('DB_HOST'));
define('CONFIG_DB_PORT', getenv('DB_PORT') ?: '3306');
define('CONFIG_DB_USER', getenv('DB_USER'));
define('CONFIG_DB_PASS', getenv('DB_PASS'));
define('CONFIG_DB_NAME', getenv('DB_NAME'));
define('CONFIG_FTS_ENABLED', true);
define('CONFIG_DB_LOGGER', false);

// DB - DAC
define('CONFIG_DAC_DB_DRIVER', 'pdo_mysql');
define('CONFIG_DAC_DB_HOST', getenv('DAC_DB_HOST'));
define('CONFIG_DAC_DB_PORT', getenv('DAC_DB_PORT') ?: '3306');
define('CONFIG_DAC_DB_USER', getenv('DAC_DB_USER'));
define('CONFIG_DAC_DB_PASS', getenv('DAC_DB_PASS'));
define('CONFIG_DAC_DB_NAME', getenv('DAC_DB_NAME'));
define('CONFIG_DAC_ENABLE_SSL', true);
define('CONFIG_DAC_SSL_CA', '/run/secrets/dac-ca-cert.pem');
define('CONFIG_DAC_DB_LOGGER', false);
define('CONFIG_DAC_USE_RESULT_CACHE', true);
// Ping do healthchecks.io usado por cron/dac_oferecimentos.php. O UUID e
// segredo (quem o tiver manda "estou vivo" no lugar do cron), entao vem do
// ambiente. Vazia = monitoramento desligado.
define('CONFIG_DAC_MONITOR_URL', getenv('HC_PING_URL') ?: '');

// Redis
define('CONFIG_REDIS_ENABLED', true);
define('CONFIG_REDIS_HOST', getenv('REDIS_HOST') ?: 'redis');
define('CONFIG_REDIS_PORT', getenv('REDIS_PORT') ?: '6379');

// Cache
define('CONFIG_RESULT_CACHE', true);
define('CONFIG_RESULT_CACHE_TTL', 10800);

// URL
define('CONFIG_URL', getenv('APP_URL'));
define('CONFIG_URL_LOGIN', 'login/');
define('CONFIG_URL_LOGIN_DAC', 'https://sistemas.dac.unicamp.br/siga/nucleo/login.xhtml?gde=1');

define('CONFIG_ACESSSO_ATUALIZAR', 300);

define('CONFIG_SALT', getenv('APP_SALT'));
define('CONFIG_SALT_SENHA_ANTIGA', getenv('APP_SALT_ANTIGA'));

// Segredo compartilhado com a DAC (ver GDE\DAC_Token). Sem esta variavel o
// login por token da DAC para de funcionar - falha fechada, de proposito.
define('CONFIG_DAC_TOKEN_SALT', getenv('DAC_TOKEN_SALT') ?: '');

// Endereco usado como From/Reply-To nos emails enviados pelo sistema (ver GDE\Util::Enviar_Email)
define('CONFIG_MAIL_FROM', getenv('MAIL_FROM') ?: 'gde@guaycuru.net');

define('CONFIG_COOKIE_NOME', 'gde_token');
define('CONFIG_COOKIE_DIAS', 30);
define('CONFIG_COOKIE_KEY', getenv('APP_COOKIE_KEY'));

define('CONFIG_OLD_LOGIN', true);

define('CONFIG_TIME_ONLINE', 600);
define('CONFIG_UPDATE_ONLINE', 60);
define('CONFIG_ONLINE_UPDATE', 30);
define('CONFIG_ONLINE_TIMEOUT', 90);
define('CONFIG_TIMEOUT_CHAT', 60);
define('CONFIG_CHAT_SLEEPT', 2);
define('CONFIG_CHAT_SLEEPN', 15);
define('CONFIG_TIMEOUT_MSG', 30);
define('CONFIG_CHAT_ATIVO', false);

define('CONFIG_AVALIACAO_M_MUL', 0.001);
define('CONFIG_AVALIACAO_MINIMO', 3);

define('CONFIG_NIVEL_LOG', 3);
define('CONFIG_FT_MIN_LENGTH', 3);

define('CONFIG_GOOGLE_CREDENTIALS', '/run/secrets/google-credentials.json');

// Tag da imagem atualmente deployada (ver k8s/app-deployment.yaml)
define('CONFIG_APP_VERSION', getenv('APP_VERSION') ?: null);
