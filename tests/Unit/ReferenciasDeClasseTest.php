<?php

namespace GDE\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * O projeto e desenvolvido no Windows e roda em Linux. O filesystem do Windows
 * e case-insensitive e nomes de classe em PHP tambem sao, entao uma referencia
 * com o casing errado (\Doctrine\DBAL\Platforms\MySqlPlatform quando a classe
 * se chama MySQLPlatform) carrega normalmente aqui e explode no build:
 *
 *   Class "Doctrine\DBAL\Platforms\MySqlPlatform" not found
 *
 * Foi o que aconteceu ao subir para o DBAL 3, que renomeou o arquivo de
 * MySqlPlatform.php para MySQLPlatform.php. O autoloader PSR-4 no Linux procura
 * pelo nome exato e nao encontra.
 *
 * getShortName() devolve o nome como a classe foi declarada, nao como foi
 * pedida, entao a comparacao abaixo detecta a divergencia mesmo no Windows.
 */
class ReferenciasDeClasseTest extends TestCase {

	private static function arquivos() {
		$raiz = dirname(__DIR__, 2);
		$out = array();
		foreach(array($raiz.'/common', $raiz.'/classes', $raiz.'/cron') as $dir) {
			// cron/ nao existe no espelho publico do GitHub; sem esta guarda o
			// RecursiveDirectoryIterator lanca UnexpectedValueException e
			// derruba a suite la
			if(!is_dir($dir))
				continue;
			$it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
			foreach($it as $f)
				if(($f->isFile()) && ($f->getExtension() === 'php'))
					$out[] = $f->getPathname();
		}
		return $out;
	}

	/**
	 * Referencias totalmente qualificadas a classes de vendor (Doctrine,
	 * Symfony). As do proprio projeto ficam de fora: o autoload delas ja e
	 * exercitado pelo resto da suite.
	 */
	public function testReferenciasAVendorTemOCasingCorreto() {
		$divergentes = array();
		$ausentes = array();

		foreach(self::arquivos() as $arquivo) {
			$fonte = file_get_contents($arquivo);
			if(preg_match_all('~\\\\?((?:Doctrine|Symfony)(?:\\\\[A-Za-z0-9_]+)+)~', $fonte, $m) === 0)
				continue;
			foreach(array_unique($m[1]) as $classe) {
				if((!class_exists($classe)) && (!interface_exists($classe)) && (!trait_exists($classe))) {
					// Namespaces parciais (ex: Doctrine\ORM\Mapping usado como alias
					// em anotacoes) nao sao classes - so reporta o que parece classe
					$curto = substr($classe, strrpos($classe, '\\') + 1);
					if(preg_match('~^[A-Z][A-Za-z0-9_]*$~', $curto))
						$ausentes[$classe] = basename($arquivo);
					continue;
				}
				$r = new \ReflectionClass($classe);
				$pedido = substr($classe, strrpos($classe, '\\') + 1);
				if($r->getShortName() !== $pedido)
					$divergentes[$classe] = $r->getName().' (em '.basename($arquivo).')';
			}
		}

		$this->assertSame(
			array(),
			$divergentes,
			'casing divergente: carrega no Windows, quebra no Linux'
		);
		// Nao afirmamos nada sobre $ausentes: ha referencias validas a classes de
		// pacotes que so existem em runtime ou em dev. O que importa e o casing.
		$this->assertIsArray($ausentes);
	}
}
