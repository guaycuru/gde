<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Acontecimento
 *
 * @ORM\Table(name="gde_acontecimentos", indexes={@ORM\Index(name="tipo", columns={"tipo"}), @ORM\Index(name="id_origem", columns={"id_origem"}), @ORM\Index(name="id_destino", columns={"id_destino"}), @ORM\Index(name="id_original", columns={"id_original"}), @ORM\Index(name="id_grupo_origem", columns={"id_grupo_origem"}), @ORM\Index(name="id_grupo_destino", columns={"id_grupo_destino"})})
 * @ORM\Entity
 */
class Acontecimento extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_acontecimento", type="integer", options={"unsigned"=true}), nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id_acontecimento;

	// ToDo: Constants de tipo
	/**
	 * @var string
	 *
	 * @ORM\Column(name="tipo", type="string", length=2, nullable=false)
	 */
	protected $tipo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_grupo_origem", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $grupo_origem;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_grupo_destino", type="integer", options={"unsigned"=true}), nullable=true)
	 */
	protected $grupo_destino;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="data", type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="texto", type="text", nullable=true)
	 */
	protected $texto;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="numero_respostas", type="integer", options={"unsigned"=true}), nullable=false)
	 */
	protected $numero_respostas = '0';

	/**
	 * @var \GDE\Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_origem", referencedColumnName="id_usuario")
	 * })
	 */
	protected $origem;

	/**
	 * @var \GDE\Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_destino", referencedColumnName="id_usuario")
	 * })
	 */
	protected $destino;

	/**
	 * @var \GDE\Acontecimento
	 *
	 * @ORM\ManyToOne(targetEntity="Acontecimento")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_original", referencedColumnName="id_acontecimento")
	 * })
	 */
	protected $original;

	public function getTexto($html = false, $processa = false, $meu = false, Usuario $Usuario = null) {
		global $_Usuario;
		if(!$html)
			return $this->texto;
		// Amizade
		if($this->getTipo() == 'ua')
			return (!$meu)
					? " agora &eacute; amig".$this->getOrigem()->getSexo(true, true)." de ".$_GDE['Usuario']->Apelido_Ou_Nome($this->getDestino(), true)."."
					: " agora &eacute; ".(($this->getOrigem()->getSexo() == 'f')?'sua amiga':'seu amigo').".";
		// Mensagem, Status de Usuario ou de Grupo
		elseif(($this->getTipo() == 'um') || ($this->getTipo() == 'us') || ($this->getTipo() == 'rs')) {
			if($this->getTipo() == 'us')
				$texto_pre = "<span class=\"atualizacao_tipo\"> (status)</span>";
			elseif($this->getTipo() == 'rs')
				$texto_pre = "<span class=\"atualizacao_tipo\"> (an&uacute;ncio)</span>";
			elseif(($this->getOriginal() !== null) && ($this->getDestino() !== null) && ($this->getDestino()->getID() != $Usuario->getID())) // Tinha um ($meu) && ali, mas acho q nao faz sentido
				$texto_pre = " -> ".(($this->getDestino() !== null) ? $_Usuario->Apelido_Ou_Nome($this->getDestino(), true) : $this->getGrupo_Destino()->getNome());
			else
				$texto_pre = "";
			$texto_pre .= ": ";
			$texto = htmlspecialchars($this->texto);
			if($processa)
				$texto = self::Processar($texto);
			$texto = nl2br(Util::Fix_String_Aux($texto));
			return $texto_pre.$texto;
		}
		// Atualizacao do GDE
		elseif($this->getTipo() == 'ga') {
			return ": ".$this->texto;
		}
		// Entrada em Grupo
		elseif($this->getTipo() == 'ra') {
			return (!$meu)
					? " entrou para o grupo ".$this->getGrupo_Destino()->getNome()."."
					: " entrou para este grupo.";
		}
		// Novo moderador no Grupo
		elseif($this->getTipo() == 'rm') {
			return (!$meu)
					? " virou moderador do grupo ".$this->getGrupo_Destino()->getNome()."."
					: " virou moderador deste grupo.";
		}
	}

	public function getLink() {
		if(($this->getOrigem() !== null) || ($this->getGrupo_Origem() !== null))
			return ($this->getOrigem() !== null) ? "Perfil.php?l=".$this->getOrigem()->getLogin() : "Grupo.php?l=".$this->getGrupo_Origem()->getApelido();
		else
			return "";
	}

	public function getNome($completo = false) {
		global $_Usuario;
		if(($this->getOrigem() !== null) || ($this->getGrupo_Origem() !== null)) {
			if($this->getOrigem() !== null) // Usuario
				return ($completo) ? $this->getOrigem()->getNome_Completo(true) : $_Usuario->Apelido_Ou_Nome($this->getOrigem(), true);
			else // Grupo
				return $this->getGrupo_Origem()->getNome();
		} elseif($this->tipo == 'ga')
			return "Atualiza&ccedil;&atilde;o do GDE";
	}

	public function getFoto($th = true) {
		if(($this->getOrigem() !== null) || ($this->getGrupo_Origem() !== null))
			return ($this->getOrigem() !== null) ? $this->getOrigem()->getFoto(true, $th) : $this->getGrupo_Origem()->getFoto(true, $th);
		elseif($this->tipo == 'ga')
			return ($th) ? "../web/images/gde_th.gif" : "../web/images/gde.gif";
		elseif($this->tipo == 'gc')
			return ($th) ? "../web/images/gde_th.gif" : "../web/images/gde.gif";
	}

	public function Pode_Responder(Usuario $Usuario) {
		if($Usuario->getAdmin())
			return true;
		// Atualizacoes do GDE
		if($this->getTipo() == 'ga')
			return true;
		// Status de Usuario ou Grupo
		if(($this->getTipo() == 'us') || ($this->getTipo() == 'rs'))
			return true;
		// Mensagens pra mim ou minhas
		if(($this->getTipo() == 'um') && (($this->getOrigem()->getID() == $Usuario->getID()) || ($this->getDestino()->getID() == $Usuario->getID())))
			return true;
		return false;
	}

	public function Pode_Apagar($Quem) {
		if(($Quem instanceof Usuario) && ($Quem->getAdmin()))
			return true;
		if($Quem instanceof Usuario) {
			if(($this->getOrigem() !== null) & ($Quem->getID() == $this->getOrigem()->getID()))
				return true;
			elseif(($this->getDestino() !== null) && ($Quem->getID() == $this->getDestino()->getID()))
				return true;
		} elseif($Quem instanceof Grupo) {
			if(($this->getGrupo_Origem() !== null) & ($Quem->getID() == $this->getGrupo_Origem()->getID()))
				return true;
			elseif(($this->getGrupo_Destino() !== null) && ($Quem->getID() == $this->getGrupo_Destino()->getID()))
				return true;
		}
		return false;
	}

	/**
	 * @param Usuario|null $Usuario
	 * @return mixed
	 */
	public function Listar_Respostas(Usuario $Usuario = null) { // Soh usada para Usuarios (Ajax) (pra grupos Usuario == null)
		//$Lista = array();
		$todas = (
			($Usuario === null) || (
				(($this->getOrigem() !== null) && ($this->getOrigem()->getID() == $Usuario->getID())) ||
				(($this->getDestino() !== null) && ($this->getDestino()->getID() == $Usuario->getID()))
			)
		);
		$dql = "SELECT A FROM GDE\\Acontecimento A WHERE A.original = ?1";
		if($todas) {
			$dql .= " AND (A.origem = ?2 OR A.destino = ?3";
			if($this->getTipo() != 'ga')
				$dql .= " OR A.destino IS NULL";
			$dql .= ")";
		}
		$dql .= " ORDER BY A.id_acontecimento ASC";
		$query = self::_EM()->createQuery($dql);
		$query->setParameter(1, $this->getID());
		if($todas) {
			$query->setParameter(2, $Usuario->getID());
			$query->setParameter(3, $Usuario->getID());
		}
		return $query->getResult();
	}

	// ToDo: Melhorar isso!
	/**
	 * @param $url
	 * @return mixed|string
	 */
	public static function TrataURL($url) {
		if(preg_match('/(?:http:\/\/)?(?:www.|)youtube.com\/watch?(?:.*?)v=([a-z0-9_\-]+)(?:.*?)(&hd=1|).*/i', $url))
			return preg_replace('/(?:http:\/\/)?(?:www.|)youtube.com\/watch?(?:.*?)v=([a-z0-9_\-]+)(?:.*?)(&hd=1|).*/i', '<br /><a href="#" id="youtube_${1}" class="video_youtube"><img src="../web/images/play_video.png" style="background: url(http://i.ytimg.com/vi/${1}/default.jpg) transparent" alt="YouTube" border="0" height="90" width="120" /></a>', $url);
		if(preg_match('/(?:http:\/\/)?(?:www.|)youtu.be\/([a-z0-9_\-]+)(?:.*?)(\?hd=1|).*/i', $url))
			return preg_replace('/(?:http:\/\/)?(?:www.|)youtu.be\/([a-z0-9_\-]+)(?:.*?)(\?hd=1|).*/i', '<br /><a href="#" id="youtube_${1}" class="video_youtube"><img src="../web/images/play_video.png" style="background: url(http://i.ytimg.com/vi/${1}/default.jpg) transparent" alt="YouTube" border="0" height="90" width="120" /></a>', $url);
		if(preg_match('/(http\:\/\/i.ytimg.com\/vi\/.*\/default.jpg)/i', $url))
			return $url;

		if(strcmp(substr($url, 0, strlen('http://')), 'http://') == 0) {
			return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		} else {
			if (preg_match('/([a-z][a-z0-9-_.]*@([0-9a-z_-]+\.)+(aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mn|mn|mo|mp|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|nom|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ra|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw|arpa))/i', $url))
				return preg_replace('/([a-z][a-z0-9-_.]*@([0-9a-z_-]+\.)+(aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mn|mn|mo|mp|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|nom|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ra|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw|arpa))/i', '<a href="mailto:$1">$1</a>', $url);
			else
				return '<a href="http://'.$url.'" target="_blank">'.$url.'</a>';
		}
	}

	/**
	 * @param $texto
	 * @return mixed
	 */
	public static function Processar($texto) {
		$tira = array('/\b((http\:\/{2}|ftp:\/{2}|https:\/{2}|((http:\/\/)?[a-z][a-z0-9-_.]*@)|)(([0-9a-z_-]+\.)+(aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mn|mn|mo|mp|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|nom|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ra|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw|arpa)(:[0-9]{1,5})?(\/(([~0-9a-zA-Z\#\+\%@\.\/_-]+))?(\?[0-9a-zA-Z\+\%@\.\/&\[\];=_-]+)?(#[~0-9a-zA-Z\+\%@\.\/_-]+)?)?))\b/i');
		return preg_replace_callback($tira, array('self', 'TrataURL'), $texto);
	}

	/**
	 * @param Usuario|null $Usuario
	 * @param string $limit
	 * @param string $start
	 * @param bool|false $maior_que
	 * @param bool|true $mensagens
	 * @param bool|true $minhas
	 * @param bool|false $amizades
	 * @param bool|false $amigos
	 * @param bool|false $gde
	 * @param bool|false $grupos
	 * @return ArrayCollection
	 */
	public static function Listar(Usuario $Usuario = null, $limit = '-1', $start = '-1', $maior_que = false, $mensagens = true, $minhas = true, $amizades = false, $amigos = false, $gde = false, $grupos = false) {
		$qrs = $qrsr = array();
		if((!$mensagens) && (!$minhas) && (!$amigos) && (!$gde) && (!$grupos))
			return new ArrayCollection();
		// Mensagens para mim
		if($mensagens)
			$qrs[] = "(O.tipo = 'um')";
		// Minhas Atualizacoes
		if($minhas)
			$qrs[] = "(O.tipo = 'us' AND O.id_origem = :id_usuario)";
		// Atualizacoes dos meus amigos
		if($amigos) {
			/*ESTE EH (BEM) MAIS LENTO $jns[] = "LEFT JOIN ".Usuario::$tabela_r_amigos." AS UA ON (UA.amigo = A.id_origem)";
			$qrs[] = "(A.tipo = 'us' AND UA.".Usuario::$chave." = '".$Usuario->getID()."' AND UA.ativo = 't')";*/
			$UsuarioAmigoMetaData = self::_EM()->getClassMetadata('GDE\\UsuarioAmigo');
			$qrs[] = "(O.tipo = 'us' AND O.id_origem IN (SELECT id_amigo FROM " . $UsuarioAmigoMetaData->getTableName() . " WHERE id_usuario = :id_usuario AND ativo = TRUE))";
		}
		if($amizades)
			$qrs[] = "(O.tipo = 'ua')";
		// Atualizacoes do GDE
		if($gde)
			$qrs[] = "(O.tipo = 'ga')";
		/*if($grupos) {
			$GrupoMetaData = self::_EM()->getClassMetadata('GDE\Grupo');
			$qrs[] = "(O.tipo = 'rs' AND O.id_grupo_origem IN (SELECT " . Grupo::$chave . " FROM " . Grupo_Usuario::$tabela . " WHERE " . Usuario::$chave . " = '" . $Usuario->getID() . "'))";
		}*/
		$qrs = implode(" OR ", $qrs);
		//if(!$todas_respostas)
		// Pego todas as respostas que sejam para o usuario ou que nao tenham sido enviadas pelo proprio usuario (qd eh US e id_destino eh NULL, eh broadcast...)
		$qrsr[] = "(R.id_destino = :id_usuario OR (O.tipo = 'us' AND R.id_origem != :id_usuario AND R.id_destino IS NULL))";
		$AcontecimentoMetaData = self::_EM()->getClassMetadata('GDE\Acontecimento');
		if($maior_que)
			$qrsr[] = "(R.id_acontecimento > :maior_que)";
		$qrsr = (count($qrsr) > 0) ? " WHERE ".implode(" AND ", $qrsr) : "";
		$qrd = "O.id_destino = :id_usuario OR (O.id_destino IS NULL AND O.id_grupo_destino IS NULL)";
		$maior = ($maior_que) ? " AND O.id_acontecimento > '".intval($maior_que)."'" : "";
		$originais = "(SELECT O.*, O.id_acontecimento AS ordem FROM " . $AcontecimentoMetaData->getTableName() . " AS O WHERE O.id_original IS NULL ".$maior." AND (".$qrd.") AND (".$qrs.") ORDER BY ordem DESC)";
		$respostas = "(SELECT O.*, MAX(R.id_acontecimento) AS ordem FROM " . $AcontecimentoMetaData->getTableName() . " AS R INNER JOIN "  . $AcontecimentoMetaData->getTableName() . " AS O ON (O.id_acontecimento = R.id_original AND (".$qrs.")) ".$qrsr." GROUP BY R.id_original ORDER BY ordem DESC)";
		$sql = "SELECT *, MAX(ordem) AS ordem FROM (".$originais." UNION ".$respostas.") AS A GROUP BY id_acontecimento ORDER BY ordem DESC LIMIT :limit OFFSET :offset";
		//$sql = "SELECT O.* FROM " . $AcontecimentoMetaData->getTableName() . " AS O WHERE O.id_original IS NULL ".$maior." AND (".$qrd.") AND (".$qrs.") ORDER BY id_acontecimento DESC LIMIT ? OFFSET ?";
		//$params[] = $limit;
		//$params[] = $start;

		$rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata('GDE\\Acontecimento', 'O');

		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('id_usuario', $Usuario->getID());
		$query->setParameter('limit', $limit);
		$query->setParameter('offset', $start);
		if($maior_que)
			$query->setParameter('maior_que', intval($maior_que));

		return $query->getResult();
	}

	/*public static function Listar_Grupo(Grupo $Grupo = null, $limit = '-1', $start = '-1', $maior_que = false, $mensagens = true, $minhas = true, $entradas = false) {
		$Lista = $qrs = $qrsr = array();//$db->debug = true;
		if((!$mensagens) && (!$minhas) && (!$entradas))
			return $Lista;
		// Mensagens para mim
		if($mensagens)
			$qrs[] = "(O.tipo = 'um')";
		// Minhas Atualizacoes
		if($minhas)
			$qrs[] = "(O.tipo = 'rs' AND O.id_grupo_origem = '".$Grupo->getID()."')";
		if($entradas)
			$qrs[] = "(O.tipo IN ('ra', 'rm'))";
		$qrs = implode(" OR ", $qrs);
		// Pego todas as respostas que sejam para o grupo ou que nao tenham sido enviadas pelo proprio grupo (qd eh US e id_destino eh NULL, eh broadcast...)
		$qrsr[] = "(R.id_grupo_destino = '".$Grupo->getID()."')"; // Acho que isto nao faz sentido -> OR (O.tipo = 'rs' AND R.id_grupo_origem != '".$Grupo->getID()."' AND R.id_grupo_destino IS NULL))";
		if($maior_que)
			$qrsr[] = "(R.".self::$chave." > '".intval($maior_que)."')";
		$qrsr = (count($qrsr) > 0) ? " WHERE ".implode(" AND ", $qrsr) : "";
		$qrd = "O.id_grupo_destino = '".$Grupo->getID()."' OR (O.id_destino IS NULL AND O.id_grupo_destino IS NULL)";
		$maior = ($maior_que) ? " AND O.".self::$chave." > '".intval($maior_que)."'" : "";
		$originais = "(SELECT O.".self::$chave." AS ".self::$chave.", O.".self::$chave." AS ordem FROM ".self::$tabela." AS O WHERE O.id_original IS NULL ".$maior." AND (".$qrd.") AND (".$qrs.") ORDER BY ordem DESC)";
		$respostas = "(SELECT R.id_original AS ".self::$chave.", MAX(R.".self::$chave.") AS ordem FROM ".self::$tabela_respostas." AS R INNER JOIN gde_acontecimentos AS O ON (O.id_acontecimento = R.id_original AND (".$qrs.")) ".$qrsr." GROUP BY R.id_original ORDER BY ordem DESC)";
		$resultado = $db->SelectLimit("SELECT ".self::$chave.", MAX(ordem) AS ordem FROM (".$originais." UNION ".$respostas.") AS A GROUP BY ".self::$chave." ORDER BY ordem DESC", $limit, $start);
		foreach($resultado as $linha)
			$Lista[] = new self($db, $linha[self::$chave]);
		return $Lista;
	}*/

	/*public static function Consultar($param = array(), $orderby = null, &$total = 0, $limit = '-1', $start = '-1') {
		$Lista = array();
		$qrs = array();
		if($orderby == null)
			$orderby = self::$chave." DESC";
		if(isset($param['id_origem']))
			$qrs[] = "id_origem ".(($param['id_origem'] != null)?" = '".intval($param['id_origem'])."'" : "IS NULL");
		if(isset($param['id_destino']))
			$qrs[] = "id_destino ".(($param['id_destino'] != null)?" = '".intval($param['id_destino'])."'" : "IS NULL");
		if(isset($param['tipo'])) {
			if(is_array($param['tipo']))
				$qrs[] = "tipo IN ('".implode("', '", $param['tipo'])."')";
			else
				$qrs[] = "tipo = '".substr($param['tipo'], 0, 2)."'";
		} if(isset($param['id_ate']))
			$qrs[] = self::$chave." < '".intval($param['id_minimo'])."'";
		$where = (count($qrs) > 0) ? " WHERE ".implode(" AND ", $qrs) : null;
		$total = $db->Execute("SELECT COUNT(*) as total FROM ".self::$tabela.$where)->fields['total'];
		$resultado = $db->SelectLimit("SELECT ".self::$chave." FROM ".self::$tabela.$where." ORDER BY ".$orderby, $limit, $start);
		foreach($resultado as $linha)
			$Lista[] = new self($db, $linha[self::$chave]);
		return $Lista;
	}*/

	public static function Ultimo_ID() {
		return self::_EM()->createQuery("SELECT MAX(A.id_acontecimento) FROM GDE\\Acontecimento A")->getSingleScalarResult();
	}
}
