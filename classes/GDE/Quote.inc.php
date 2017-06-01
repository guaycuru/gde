<?php

namespace GDE;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Quote
 *
 * @ORM\Table(
 *  name="gde_quotes",
 *  indexes={
 *     @ORM\Index(name="tipo", columns={"tipo"}),
 *     @ORM\Index(name="hash", columns={"hash"})
 *  }
 * )
 * @ORM\Entity
 */
class Quote extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=32, nullable=false)
	 */
	protected $hash;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $texto;

	const CACHE_DIR = __DIR__.'/../../cache/rss';
	const CACHE_TIME = 900;

	private static $_urls = array(
		'http://www.brainyquote.com/link/quotefu.rss',
		//'http://feeds.feedburner.com/frasedodia?format=xml',
		'http://www.quotedb.com/quote/quote.php?action=random_quote_rss',
		//'http://quoteoftheday.wordpress.com/feed/',
		'http://quotes4all.net/rss/440010300/quotes.xml'
	);

	/**
	 * @param bool $html
	 * @return string
	 */
	public static function Qualquer_Uma($html = true) {
		require_once(__DIR__."/../../common/lastRSS.php");
		$rss = new \lastRSS;
		$rss->cache_dir = self::CACHE_DIR;
		$rss->cache_time = self::CACHE_TIME;
		$urls = self::$_urls;
		$onde = mt_rand(0, 3);
		$qual = mt_rand(0, count($urls)-1);
		$quote = "";
		if(($onde == 1) || ($onde == 2)) {
			$Quote = self::Aleatoria($onde);
			if($Quote === null)
				return '';
			return $Quote->getTexto($html);
		}
		if(($rs = $rss->get($urls[$qual])) && ($rs['items_count'] > 0)) {
			$n = mt_rand(0, $rs['items_count']-1);
			$quote = str_ireplace("<br>", "<br />", utf8_encode(strip_tags(html_entity_decode($rs['items'][$n]['title'].((isset($rs['items'][$n]['description']))?": ".$rs['items'][$n]['description']:null)), "<br>")));
		}
		return $quote;
	}

	/**
	 * @param integer $tipo
	 * @return Quote|null
	 */
	public static function Aleatoria($tipo) {
		$sql = "SELECT Q.* FROM `gde_quotes` AS Q WHERE `tipo` = :tipo ORDER BY RAND() LIMIT 1";
		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata(get_class(), 'Q');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('tipo', $tipo);
		return $query->getOneOrNullResult();
	}

}
