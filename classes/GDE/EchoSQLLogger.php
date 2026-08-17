<?php

namespace GDE;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * EchoSQLLogger
 *
 * Reposicao do Doctrine\DBAL\Logging\EchoSQLLogger, removido no DBAL 3. O
 * comportamento e o mesmo da classe original: imprime cada consulta e, se
 * houver, os parametros e tipos.
 *
 * Usado apenas quando CONFIG_DB_LOGGER / CONFIG_DAC_DB_LOGGER / DEBUG_DB_LOGGER
 * estao ligados (ver common/doctrine.inc.php e common/doctrine_dac.inc.php).
 *
 * A interface SQLLogger esta deprecada no DBAL a partir da 3.5 em favor de
 * Doctrine\DBAL\Logging\Middleware com um logger PSR-3. Como isto so roda em
 * depuracao manual, manter a interface antiga evita mexer na montagem da
 * Configuration; migrar para o middleware fica para quando o logger virar algo
 * que se queira usar em producao.
 */
class EchoSQLLogger implements SQLLogger {

	/**
	 * @param string $sql
	 * @param mixed[]|null $params
	 * @param int[]|string[]|null $types
	 * @return void
	 */
	public function startQuery($sql, ?array $params = null, ?array $types = null) {
		echo $sql.PHP_EOL;
		if(!empty($params))
			var_dump($params);
		if(!empty($types))
			var_dump($types);
	}

	/**
	 * @return void
	 */
	public function stopQuery() {
	}
}
