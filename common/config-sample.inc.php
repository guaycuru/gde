<?php

// Dev Mode
define('CONFIG_DEV_MODE', true);

// DB
define('CONFIG_DB_TYPE', 'mysqli');
define('CONFIG_DB_HOST', '127.0.0.1');
define('CONFIG_DB_USER', 'Web');
define('CONFIG_DB_PASS', '');
define('CONFIG_DB_NAME', 'gde');

// Redis (desnecessario em Dev Mode)
define('CONFIG_REDIS_HOST', '');
define('CONFIG_REDIS_PORT', '');

// URL base do sistema, tem que ter trailing slash
define('CONFIG_URL', 'http://localhost/Web/gde/');
define('CONFIG_URL_LOGIN', 'login/');
define('CONFIG_URL_LOGIN_DAC', 'https://www.daconline.unicamp.br/pckAcadGDE/AppAcadGDE.jsp');

// Tempo em segundos entre atualizacoes do ultimo acesso do usuario
define('CONFIG_ACESSSO_ATUALIZAR', 300);

define('CONFIG_SALT', '');
define('CONFIG_SALT_SENHA_ANTIGA', '');

// Nome do cookie de login
define('CONFIG_COOKIE_NOME', 'GDE');

// Tempo em dias para o cookie expirar quando "Lembrar" for selecionado
define('CONFIG_COOKIE_DIAS', 30);

define('CONFIG_COOKIE_KEY', '');

define('CONFIG_OLD_LOGIN', true);

// Periodo de timeout do ultimo acesso pra contagem de usuarios online
define('CONFIG_TIME_ONLINE', 600); // 600

// Periodo de atualizacao do numero de usuarios online
define('CONFIG_UPDATE_ONLINE', 60); // 60 -> 300

// Periodo de atualizacao do ultimo acesso do usuario
define('CONFIG_ONLINE_UPDATE', 30); // 10 -> 120

// Periodo de timeout do ultimo acesso (para ser considerado offline, geralmente 3x o anterior)
define('CONFIG_ONLINE_TIMEOUT', 90); // 30 -> 180

// Periodo de espera por mensagens novas
define('CONFIG_TIMEOUT_CHAT', 60);

// Tempo de intervalo entre checagens quando tem amigos online
define('CONFIG_CHAT_SLEEPT', 2); // 2 -> 3

// Tempo de intervalo entre checagens quando NAO tem amigos online
define('CONFIG_CHAT_SLEEPN', 15); // 15 -> 20

// Periodo de espera antes que uma mensagem seja considerada offline (a.k.a. no limbo)
define('CONFIG_TIMEOUT_MSG', 30);

// Chat ativo?
define('CONFIG_CHAT_ATIVO', false);

// Multiplicador do M
define('CONFIG_AVALIACAO_M_MUL', 0.001);

define('CONFIG_AVALIACAO_MINIMO', 3);

define('CONFIG_NIVEL_LOG', 3);

define('CONFIG_FT_MIN_LENGTH', 3);
