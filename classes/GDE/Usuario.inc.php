<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Usuario
 *
 * @ORM\Table(
 *   name="gde_usuarios",
 *   indexes={
 *     @ORM\Index(name="ativo", columns={"ativo"}),
 *     @ORM\Index(name="compartilha_arvore", columns={"compartilha_arvore"}),
 *     @ORM\Index(name="ultimo_acesso", columns={"ultimo_acesso"})
 *   }
 * )
 * @ORM\Entity
 */
class Usuario extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_usuario;

	/**
	 * @var ArrayCollection|UsuarioAmigo[]
	 *
	 * @ORM\OneToMany(targetEntity="UsuarioAmigo", mappedBy="usuario")
	 */
	protected $amigos;

	/**
	 * @var ArrayCollection|Aluno[]
	 *
	 * @ORM\ManyToMany(targetEntity="Aluno")
	 * @ORM\JoinTable(name="gde_r_usuarios_favoritos",
	 *      joinColumns={@ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="ra", referencedColumnName="ra")}
	 * )
	 */
	protected $favoritos;

	/**
	 * @var UsuarioConfig
	 *
	 * @ORM\OneToOne(targetEntity="UsuarioConfig", mappedBy="usuario", cascade={"persist", "remove"})
	 */
	protected $config;

	/**
	 * @var Aluno
	 *
	 * @ORM\OneToOne(targetEntity="Aluno", inversedBy="usuario")
	 * @ORM\JoinColumn(name="ra", referencedColumnName="ra")
	 */
	protected $aluno;

	/**
	 * @var Professor
	 *
	 * @ORM\OneToOne(targetEntity="Professor", inversedBy="usuario")
	 * @ORM\JoinColumn(name="id_professor", referencedColumnName="id_professor")
	 */
	protected $professor;

	/**
	 * @var Curso
	 *
	 * @ORM\ManyToOne(targetEntity="Curso")
	 * @ORM\JoinColumn(name="id_curso", referencedColumnName="id_curso")
	 */
	protected $curso;

	/**
	 * @var Modalidade
	 *
	 * @ORM\ManyToOne(targetEntity="Modalidade")
	 * @ORM\JoinColumn(name="id_modalidade", referencedColumnName="id_modalidade")
	 */
	protected $modalidade;

	/**
	 * @var ArrayCollection|UsuarioEliminada[]
	 *
	 * @ORM\OneToMany(targetEntity="UsuarioEliminada", mappedBy="usuario")
	 */
	protected $eliminadas;

	/**
	 * @var ArrayCollection|UsuarioEmprego[]
	 *
	 * @ORM\OneToMany(targetEntity="UsuarioEmprego", mappedBy="usuario", cascade={"persist", "remove"})
	 */
	protected $empregos;

	/**
	 * @var ArrayCollection|Planejado[]
	 *
	 * @ORM\OneToMany(targetEntity="Planejado", mappedBy="usuario")
	 */
	protected $planejados;

	/**
	 * @var ArrayCollection|AvaliacaoResposta[]
	 *
	 * @ORM\OneToMany(targetEntity="AvaliacaoResposta", mappedBy="usuario")
	 */
	protected $avaliacao_respostas;

	/**
	 * @var ArrayCollection|EnqueteOpcao[]
	 *
	 * @ORM\ManyToMany(targetEntity="EnqueteOpcao", inversedBy="usuarios")
	 * @ORM\JoinTable(name="gde_r_usuarios_enquetes_opcoes",
	 *      joinColumns={@ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_opcao", referencedColumnName="id_opcao")}
	 * )
	 */
	protected $enquetes_opcoes;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=16, unique=true, nullable=false)
	 */
	protected $login;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $senha;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $nome;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $sobrenome;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, unique=true, nullable=true)
	 */
	protected $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $nivel;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $catalogo;

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="smallint", nullable=true)
	 */
	protected $ingresso;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $sexo;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=16, nullable=true)
	 */
	protected $foto;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_nascimento;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $apelido;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $status;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $orkut;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $gtalk;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $msn;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $blog;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $facebook;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $twitter;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $cidade;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=2, nullable=true)
	 */
	protected $estado;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */
	protected $estado_civil;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $mais;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, options={"default"=1}, nullable=false)
	 */
	protected $compartilha_arvore = 't';

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=true)
	 */
	protected $procurando_emprego;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $exp_profissionais;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $hab_pessoais;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $esp_tecnicas;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $info_profissional;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, options={"default"=1}, nullable=false)
	 */
	protected $compartilha_horario = 't';

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $mudanca_horario;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $ultimo_acesso;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_cadastro;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $email_validado = false;

	// ToDo: Contants
	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=1, options={"default"="d"}, nullable=false)
	 */
	protected $chat_status = 'd';

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $ativo = false;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $admin = false;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $beta = false;

	// Determina se esta eh uma copia da entidade original, que pode ser modificada
	private $_copia;

	// Erros de login
	const ERRO_LOGIN_NAO_ENCONTRADO = 1; // Usuario nao encontrado
	const ERRO_LOGIN_SENHA_INCORRETA = 2; // Usuario ou senha incorretos
	const ERRO_LOGIN_USUARIO_INATIVO = 3; // Usuario inativo
	const ERRO_LOGIN_TOKEN_INVALIDO = 4; // Token invalido

	// Fotos
	const PASTA_FOTOS = '/../../web/fts/';
	const URL_FOTOS = 'web/fts/';

	// Estados Civis
	const ESTADO_CIVIL_VAZIO = 0;
	const ESTADO_CIVIL_NAO_OPINAR = 1;
	const ESTADO_CIVIL_SOLTEIRO = 2;
	const ESTADO_CIVIL_ENROLADO = 3;
	const ESTADO_CIVIL_NAMORANDO = 4;
	const ESTADO_CIVIL_NOIVO = 5;
	const ESTADO_CIVIL_CASADO = 6;
	const ESTADO_CIVIL_CASAMENTO_ABERTO = 7;
	const ESTADO_CIVIL_LIBERAL = 8;
	private static $_estados_civis = array(
		self::ESTADO_CIVIL_VAZIO => '',
		self::ESTADO_CIVIL_NAO_OPINAR => 'Prefiro N&atilde;o Opinar',
		self::ESTADO_CIVIL_SOLTEIRO => 'Solteiro(a)',
		self::ESTADO_CIVIL_ENROLADO => 'Enrolado(a)',
		self::ESTADO_CIVIL_NAMORANDO => 'Namorando',
		self::ESTADO_CIVIL_NOIVO => 'Noivo(a)',
		self::ESTADO_CIVIL_CASADO => 'Casado(a)',
		self::ESTADO_CIVIL_CASAMENTO_ABERTO => 'Casamento Aberto',
		self::ESTADO_CIVIL_LIBERAL => 'Relacionamento Liberal'
	);

	public static function Listar_Estados_Civis() {
		return self::$_estados_civis;
	}

	/**
	 * Por_Unique
	 *
	 * Encontra um Usuario pelo valor unico provido (login, ra ou email)
	 *
	 * @param string $valor O valor a ser buscado
	 * @param string $campo (Opcional) Determina qual o campo a ser usado na busca
	 * @param bool|null $ativo (Opcional) Se nao for null, filtra pelo valor do campo $ativo
	 * @return Usuario|null O Usuario encontrado, ou null se nada for encontrado
	 */
	public static function Por_Unique($valor, $campo = null, $ativo = null) {
		if($valor == null) // Nao faz sentido procurar por um valor unico vazio
			return null;
		if($campo === null) { // Campo a ser determinado
			if(strpos($valor, '@') !== false) // Email
				$campo = 'email';
			elseif(preg_match('/^\d+$/i', $valor) > 0) // RA
				$campo = 'aluno'; // Busca por RA deve buscar um aluno
			else // Login
				$campo = 'login';
		} elseif($campo == 'ra')
			// Busca por RA deve buscar um aluno
			$campo = 'aluno';
		elseif($campo == 'matricula')
			// Busca por matricula (professor) precisa ser feita via DQL
			return self::Por_Matricula($valor, $ativo);
		$params = array($campo => $valor);
		if($ativo !== null)
			$params['ativo'] = $ativo;
		$Usuario = self::FindOneBy($params);
		if($Usuario === null)
			return null;
		return $Usuario;
	}

	/**
	 * Por_Login
	 *
	 * @param $login
	 * @param bool $ativo
	 * @param bool $vazio Se nenhum resultado for encontrado, retorna um objeto vazio
	 * @return null|Usuario
	 */
	public static function Por_Login($login, $ativo = true, $vazio = false) {
		$Usuario = self::Por_Unique($login, 'login', $ativo);
		if($Usuario === null && $vazio === true)
			return new self;
		return $Usuario;
	}

	/**
	 * Por_RA
	 *
	 * @param $ra
	 * @param bool $ativo
	 * @param bool $vazio Se nenhum resultado for encontrado, retorna um objeto vazio
	 * @return null|Usuario
	 */
	public static function Por_RA($ra, $ativo = true, $vazio = false) {
		$Usuario = self::Por_Unique($ra, 'ra', $ativo);
		if($Usuario === null && $vazio === true)
			return new self;
		return $Usuario;
	}

	/**
	 * Por_Matricula
	 *
	 * @param $matricula
	 * @param bool $ativo
	 * @param bool $vazio Se nenhum resultado for encontrado, retorna um objeto vazio
	 * @return null|Usuario
	 */
	public static function Por_Matricula($matricula, $ativo = true, $vazio = false) {
		$dql = 'SELECT U FROM GDE\\Usuario U INNER JOIN GDE\\Professor P WHERE P.matricula = ?1';
		if($ativo !== null)
			$dql .= ' AND U.ativo = ?2';
		$query = self::_EM()->createQuery($dql)
			->setParameter(1, $matricula)
			->setMaxResults(1);
		if($ativo !== null)
			$query->setParameter(2, $ativo);
		$Usuario = $query->getOneOrNullResult();
		if($Usuario === null && $vazio === true)
			return new self;
		return $Usuario;
	}

	/**
	 * Copia
	 *
	 * Se esta ja eh uma copia, retorna-a, caso contraria, cria uma copia e retorna-a
	 *
	 * @return $this|Usuario
	 */
	public function Copia() {
		if($this->_copia === true)
			return $this;
		$Copia = clone $this;
		$Copia->_copia = true;
		$AlunoCopia = $this->getAluno(true)->Copia();
		$Copia->setAluno($AlunoCopia);
		Base::_EM()->detach($Copia);
		return $Copia;
	}

	/**
	 * @return bool
	 */
	public function Online() {
		if($this->getUltimo_Acesso(false) === null)
			return false;
		return ((time() - $this->getUltimo_Acesso('U')) < CONFIG_ONLINE_TIMEOUT);
	}

	/**
	 * @param $esta_online
	 * @param $status
	 * @param $admin
	 * @param bool $puro
	 * @return string
	 */
	public static function Trata_Chat_Status($esta_online, $status, $admin, $puro = false) {
		$retorno = "off";
		if($esta_online) {
			if($puro === false && ($status == 'i' || $status == 'z'))
				$retorno = "off";
			elseif($status == 'd' && $admin)
				$retorno = "x";
			else
				$retorno = $status;
		}
		return $retorno;
	}

	/**
	 * @param bool $html
	 * @return string
	 */
	public function getNome_Completo($html = false) {
		return trim($this->getNome($html).' '.$this->getSobrenome($html));
	}

	/**
	 * @param bool $html
	 * @param bool $artigo
	 * @return string
	 */
	public function getSexo($html = false, $artigo = false) {
		if($html === false)
			return $this->sexo;
		elseif($this->sexo == 'f')
			return ($artigo) ? 'a' : 'Feminino';
		elseif($this->sexo == 'm')
			return ($artigo) ? 'o' : 'Masculino';
		elseif($this->sexo == 'o')
			return ($artigo) ? '*' : 'Outro';
		else
			return ($artigo) ? '*' : 'Desconhecido';
	}

	/**
	 * @param bool $html
	 * @return string
	 */
	public function getEstado_Civil($html = true) {
		return ($html && isset(self::$_estados_civis[$this->estado_civil]))
			? self::$_estados_civis[$this->estado_civil]
			: $this->estado_civil;
	}

	/**
	 * @param bool|false $icone
	 * @param bool|false $puro
	 * @return string
	 */
	public function getChat_Status($icone = false, $puro = false) {
		// Disponivel(D), Ocupado(O), Ausente(A), Invisivel(I), Inativo, Admin(X), Desconectado(Z)

		$esta_online = $this->Online();
		$status = self::Trata_Chat_Status($esta_online, $this->chat_status, $this->getAdmin(), $puro);

		if(!$icone)
			return $status;
		else
			return '<img src="'.CONFIG_URL.'web/images/status_'.$status.'.png" class="status_icone status_icone_'.$this->getID().'" alt="'.$status.'" />';
	}

	/**
	 * @param bool $html
	 * @param bool $th
	 * @param bool $url
	 * @return string
	 */
	public function getFoto($html = false, $th = false, $url = false) {
		if($this->foto == null)
			return self::getFoto_Padrao($th, $url);
		if($url) {
			return CONFIG_URL . self::URL_FOTOS . (($th) ? $this->foto.'_th.jpg' : $this->foto.'.jpg');
		} else {
			if($th)
				return __DIR__ . self::PASTA_FOTOS . (($html) ? $this->foto.'_th.jpg' : $this->foto);
			else
				return __DIR__ . self::PASTA_FOTOS . (($html) ? $this->foto.'.jpg' : $this->foto);
		}
	}

	/**
	 * @param bool $th
	 * @param bool $url
	 * @return string
	 */
	public static function getFoto_Padrao($th = false, $url = false) {
		if($url) {
			return CONFIG_URL . self::URL_FOTOS . (($th) ? 'nobody_th.gif' : 'nobody.gif');
		} else {
			if($th)
				return CONFIG_URL.'web/images/nobody_th.gif';
			else
				return CONFIG_URL.'web/images/nobody.gif';
		}
	}

	/**
	 * setSenha
	 *
	 * Define a senha do Usuario, possivelmente codificando-a
	 *
	 * @param string $senha A nova senha
	 * @param bool $codificar (Opcional) Se for true, a senha sera codificada
	 * @return string A nova senha
	 */
	public function setSenha($senha, $codificar = true) {
		if($codificar)
			$senha = self::Codificar_Senha($senha);
		return parent::setSenha($senha);
	}

	/**
	 * Codificar_Senha
	 *
	 * @param string $senha A senha a ser codificada
	 * @return string A senha codificada
	 */
	public static function Codificar_Senha($senha) {
		if($senha == null)
			return null;
		return password_hash($senha, PASSWORD_DEFAULT);
	}

	/**
	 * Verificar_Senha_Antiga
	 *
	 * Verifica um hash de senha antiga
	 *
	 * @param string $senha A senha para ser comparada com o hash
	 * @param string $hash O hash para ser comparado
	 * @return boolean Se a senha esta correta
	 */
	public static function Verificar_Senha_Antiga($senha, $hash) {
		return (sha1($senha.CONFIG_SALT_SENHA_ANTIGA) == $hash);
	}

	/**
	 * Verificar_Senha
	 *
	 * Verifica se a senha esta correta para o login fornecido
	 *
	 * @param string $senha A senha a ser verificada
	 * @param boolean $codificada (Opcional) Se a senha ja esta codificada
	 * @return boolean True caso a senha esteja correta, false caso contrario
	 *
	 */
	public function Verificar_Senha($senha, $codificada = true) {
		$hash = $this->getSenha(false);
		if(($codificada === true) && ($hash !== $senha))
			return false; // Senha ja codificada, mas incorreta
		elseif($codificada === false) {
			// Verifica se a senha ja esta no novo formato
			$info = password_get_info($hash);
			if($info['algo'] == 0) { // Senha antiga
				if(self::Verificar_Senha_Antiga($senha, $hash) === false)
					return false; // Senha antiga incorreta
			} elseif(password_verify($senha, $hash) === false) // Senha nova
				return false; // Senha nova incorreta
		}
		return true; // Tudo certo
	}

	/**
	 * Ping
	 *
	 * Chamada em todas as requisicoes, no common, para verificar se esta tudo OK e atualizar ultimo acesso
	 *
	 * @param boolean $verificar (Opcional) Se for false, nao ira verificar o Usuario
	 * @param $atualizar_acesso (Opcional) Se for true, ira atualizar o ultimo acesso
	 * @return self O Usuario atualmente logado
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public static function Ping($verificar = true, $atualizar_acesso = true) {
		$Usuario = new self();

		if($verificar === false)
			return $Usuario;

		// Verificar COOKIE
		if(!empty($_COOKIE[CONFIG_COOKIE_NOME])) {
			$Usuario = UsuarioToken::Verificar(trim($_COOKIE[CONFIG_COOKIE_NOME]));
			if((!is_object($Usuario)) || ($Usuario->getID() == null)) // Usuario inexistente
				$Usuario = self::Logout();
			elseif($Usuario->getAtivo() === false) // Usuario inativo
				$Usuario = self::Logout();
		}

		// Se o Usuario continua logado, atualiza o ultimo acesso dele
		if(($Usuario->getID() != null) && ($atualizar_acesso))
			$Usuario->Acesso();

		return $Usuario;
	}

	/**
	 * Verificar_Login
	 *
	 * Tenta efetuar o login, chamado pelo formulario de login
	 * Aproveita e verifica se a hash da senha precisa ser atualizada
	 *
	 * @param string $login Login, RA ou email
	 * @param string $senha A senha fornecida pelo usuario
	 * @param bool $lembrar (Opcional) Se for true, ira definir a duracao do cookie
	 * @param bool|string $erro (Opcional) Se for passado, sera preenchido com o codigo de erro
	 * @return Usuario
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public static function Verificar_Login($login, $senha, $lembrar = false, &$erro = false) {
		$Usuario = self::Por_Unique($login, null);
		if($Usuario === null) {
			$Usuario = self::Logout();
			if($erro !== false)
				$erro = self::ERRO_LOGIN_NAO_ENCONTRADO;
		} elseif($Usuario->Verificar_Senha($senha, false) === false) { // Senha incorreta
			$Usuario = self::Logout();
			if($erro !== false)
				$erro = self::ERRO_LOGIN_SENHA_INCORRETA;
		} elseif($Usuario->getAtivo() === false) { // Usuario inativo
			$Usuario = self::Logout();
			if($erro !== false)
				$erro = self::ERRO_LOGIN_USUARIO_INATIVO;
		} else { // Login e senha corretos
			// Verifica se eh necessario atualizar a hash da senha do usuario
			if(password_needs_rehash($Usuario->getSenha(false), PASSWORD_DEFAULT) === true) {
				$nova_senha = Usuario::Codificar_Senha($senha);
				$Usuario->senha = $nova_senha; // Evita iniciar uma transacao desnecessaria
				self::_EM()->createQuery('UPDATE GDE\\Usuario U SET U.senha = ?0 WHERE U.id_usuario = ?1')
					->setParameters(array($nova_senha, $Usuario->getID()))
					->getSingleScalarResult();
			}
		}

		// Salva o cookie do login
		$Usuario->Salvar_Cookie($lembrar);

		// Retorna o Usuario
		return $Usuario;
	}

	/**
	 * Efetuar_Login_DAC
	 *
	 * Efetua login proveniente do portal da DAC
	 *
	 * @param string $token O token fornecido pelo portal da DAC
	 * @param bool $erro
	 * @return false|Usuario O Usuario logado (podendo ser vazio ou nao) ou false se o token for invalido
	 */
	public static function Efetuar_Login_DAC($token, &$erro = false) {
		if($token == null) {
			if($erro !== false)
				$erro = self::ERRO_LOGIN_TOKEN_INVALIDO;
			return false;
		}
		list($resultado, $matricula, $tipo) = DAC::Validar_Token($token);
		if($resultado === false) {
			$Usuario = self::Logout();
			if($erro !== false)
				$erro = self::ERRO_LOGIN_TOKEN_INVALIDO;
		} else {
			switch($tipo) {
				case 'A': // Aluno
					$Usuario = self::Por_Unique($matricula, 'ra');
					break;
				case 'D': // Docente
					$Usuario = self::Por_Unique($matricula, 'matricula');
					break;
				default: // Outros (Funcionarios, etc)
					if($erro !== false)
						$erro = self::ERRO_LOGIN_TOKEN_INVALIDO;
					return false;
			}
			if($Usuario === null) {
				if($erro !== false)
					$erro = self::ERRO_LOGIN_NAO_ENCONTRADO;
				return false;
			}

			// Salva o cookie do login
			$Usuario->Salvar_Cookie(false);

			// Salva o token
			DAC::Novo_Token($token, true);
		}
		return $Usuario;
	}

	/**
	 * Gerar_Cookie
	 *
	 * Gera os dados do cookie
	 *
	 * @return string Os dados do cookie
	 */
	public function Gerar_Cookie() {
		$Token = UsuarioToken::Novo($this, true);
		if($Token === false)
			return false;
		return $Token->Em_String();
	}

	/**
	 * Cookie_Path
	 *
	 * Retorna o path a ser usado no cookie
	 *
	 * @return string O path a ser usado no cookie
	 */
	public static function Cookie_Path() {
		return parse_url(CONFIG_URL, PHP_URL_PATH);
	}

	/**
	 * Salvar_Cookie
	 *
	 * Salva o cookie
	 *
	 * @param boolean $lembrar (Opcional) Se for true, o cookie nao expirara no final da sessao
	 * @return boolean Se o cookie foi enviado com sucesso
	 */
	public function Salvar_Cookie($lembrar = false) {
		$duracao = ($lembrar) ? time() + (86400 * CONFIG_COOKIE_DIAS) : 0;
		return setcookie(CONFIG_COOKIE_NOME, $this->Gerar_Cookie(), $duracao, self::Cookie_Path(), '', false, true);
	}

	/**
	 * Logout
	 *
	 * Desloga o Usuario e limpa o cookie
	 *
	 * @return Usuario Um objeto Usuario vazio
	 */
	public static function Logout() {
		if(!empty($_COOKIE[CONFIG_COOKIE_NOME]))
			UsuarioToken::Excluir(trim($_COOKIE[CONFIG_COOKIE_NOME]));
		setcookie(CONFIG_COOKIE_NOME, '', time() - 3600, self::Cookie_Path(), '', false, true);
		session_unset();
		session_destroy();
		return new self();
	}

	/**
	 * Acesso
	 *
	 * Atualiza o ultimo acesso do Usuario, diretamente no banco para evitar uma transacao desncessaria
	 *
	 * @return boolean Se deu certo
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	private function Acesso() {
		if(time() - $this->getUltimo_Acesso('U') < CONFIG_ACESSSO_ATUALIZAR)
			return true;

		$this->ultimo_acesso = new \DateTime();
		$query = self::_EM()->createQuery('UPDATE GDE\\Usuario U SET U.ultimo_acesso = ?0 WHERE U.id_usuario = ?1');
		$query->setParameters(array($this->ultimo_acesso, $this->getID()));
		return ($query->getSingleScalarResult() > 0);
	}

	/**
	 * @param Usuario $Usuario
	 * @return bool|Usuario
	 */
	public function Relacionamento(Usuario $Usuario) { // Este eh meio que um "Amigos em comum"... nao?
		if($this->Amigo($Usuario) !== false)
			return $Usuario;
		else {
			$em_comum = 0;
			$this->Amigos_Em_Comum($Usuario, false, $em_comum);
			if($em_comum == 0)
				return false;
			return $this->Amigos_Em_Comum($Usuario, false)->first()->getAmigo();
		}
	}

	/**
	 * @param bool|false $live
	 * @return int
	 * @throws \Doctrine\ORM\Query\QueryException
	 */
	public static function Conta_Online($live = false) {
		if(($live === false) && (file_exists(__DIR__.'/../../cache/online.txt') === true))
			return intval(file_get_contents(__DIR__.'/../../cache/online.txt'));
		$Data = new \DateTime();
		$Data->modify('-'.CONFIG_TIME_ONLINE.' seconds');
		$online = self::_EM()->createQuery('SELECT COUNT(U.id_usuario) FROM '.get_class().' U WHERE U.ultimo_acesso >= ?1')
			->setParameter(1, $Data)
			->getSingleScalarResult();
		// Recorde
		$Dados = Dado::Load(1);
		if($Dados->getMax_Online(false) < $online) {
			$Dados->setMax_Online($online);
			$Dados->setMax_Online_TS();
			$Dados->Save(true);
		}
		return $online;
	}

	/**
	 * Amigos_Em_Comum
	 *
	 * @param Usuario $Usuario
	 * @param int $total
	 * @return ArrayCollection|UsuarioAmigo[]
	 */
	public function Amigos_Em_Comum(Usuario $Usuario, $ordem_nome = false, &$total = 0) {
		$Lista = array();
		$ids_amigos = array();
		foreach($Usuario->Amigos(false) as $Amigo)
			$ids_amigos[$Amigo->getAmigo()->getID()] = true;
		foreach($this->Amigos(false) as $Amigo) // Pego do meu, pq tem os MEUS apelidos!
			if(isset($ids_amigos[$Amigo->getAmigo()->getID()]))
				$Lista[] = $Amigo;
		$total = count($Lista);
		$Amigos = new ArrayCollection($Lista);
		if($ordem_nome === true)
			return UsuarioAmigo::Ordenar_Por_Nome($Amigos);
		return $Amigos;
	}

	/**
	 * @param int $minimo
	 * @param int|null $limite
	 * @param int|null $start
	 * @return Usuario[]|ArrayCollection
	 */
	public function Amigos_Recomendacoes($minimo = 2, $limite = null, $start = null) {
		// ToDo: Pegar nome da tabela das annotations
		//$res = self::$db->SelectLimit("SELECT U1.amigo FROM ".self::$tabela_r_amigos." AS U1 JOIN ".self::$tabela_r_amigos." AS U2 ON (U2.amigo = U1.".self::$chave.") WHERE U2.".self::$chave." = '".$this->getID()."' AND U1.amigo != '".$this->getID()."' AND U1.ativo = 't' AND U2.ativo = 't' AND U1.amigo NOT IN (SELECT amigo FROM ".self::$tabela_r_amigos." WHERE ".self::$chave." = '".$this->getID()."') AND U1.amigo NOT IN (SELECT id_usuario FROM ".self::$tabela_r_amigos." WHERE amigo = '".$this->getID()."') GROUP BY U1.amigo HAVING COUNT(U1.amigo) >= ".$minimo." ORDER BY COUNT(U1.amigo) DESC, RAND()", $limite, $start);
		$sql = 'SELECT U.* FROM `gde_usuarios_amigos` AS U1 '.
			'JOIN `gde_usuarios_amigos` AS U2 ON (U2.`id_amigo` = U1.`id_usuario`) '.
			'JOIN `gde_usuarios` AS U ON (U.`id_usuario` = U1.`id_amigo`) '.
			'WHERE U2.`id_usuario` = :id_usuario AND U1.`id_amigo` != :id_usuario AND '.
			'U1.`ativo` = TRUE AND U2.`ativo` = TRUE AND U1.`id_amigo` NOT IN '.
				'(SELECT `id_amigo` FROM `gde_usuarios_amigos` WHERE `id_usuario` = :id_usuario) '.
			'AND U1.`id_amigo` NOT IN '.
				'(SELECT `id_usuario` FROM `gde_usuarios_amigos` WHERE `id_amigo` = :id_usuario) '.
			'GROUP BY U1.`id_amigo` HAVING COUNT(U1.`id_amigo`) >= :minimo '.
			'ORDER BY COUNT(U1.`id_amigo`) DESC, RAND()';

		if(($limite !== null) && ($start !== null))
			$sql .= ' LIMIT '.intval($start).','.intval($limite);
		elseif($limite !== null)
			$sql .= ' LIMIT '.intval($limite);


		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addRootEntityFromClassMetadata(get_class(), 'U');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('id_usuario', $this->getID());
		$query->setParameter('minimo', $minimo);
		return $query->getResult();
	}

	/**
	 * getQuase_Amigos
	 *
	 * Retorna a lista de amigos que ainda nao aceitaram o pedido de amizade
	 *
	 * @return ArrayCollection|UsuarioAmigo[]
	 */
	public function getQuase_Amigos() {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("ativo", false));
		$criteria->andWhere(Criteria::expr()->eq("usuario", $this));
		return $this->getAmigos()->matching($criteria);
	}

	/**
	 * @param Usuario $Usuario
	 * @return UsuarioAmigo|false
	 */
	public function Quase_Amigo(Usuario $Usuario) { // Se eu to esperando autorizacao dele...
		$criteria = Criteria::create()->where(Criteria::expr()->eq("amigo", $Usuario));
		$criteria->setMaxResults(1);
		$Quase = $this->getQuase_Amigos()->matching($criteria);
		return ($Quase->count() > 0) ? $Quase->first() : false;
	}

	/**
	 * Amigos_Pendentes
	 *
	 * @return ArrayCollection|UsuarioAmigo[] Autorizacoes de amizades pendentes
	 */
	public function getAmigos_Pendentes() {
		/*$criteria = Criteria::create()->where(Criteria::expr()->eq("ativo", false));
		$criteria->andWhere(Criteria::expr()->eq("amigo", $this));
		return $this->getAmigos()->matching($criteria);*/
		$dql = 'SELECT A FROM GDE\\UsuarioAmigo A WHERE A.amigo = ?1 AND A.ativo = FALSE';
		return self::_EM()->createQuery($dql)
			->setParameter(1, $this->getID())
			->getResult();
	}

	/**
	 * @param $campo
	 * @return mixed
	 */
	public function getCompartilha($campo) {
		$campo = 'compartilha_'.$campo;
		if(property_exists(get_class(), $campo) === false)
			return false;
		return $this->{'get'.$campo}(false);
	}

	/**
	 * @param Usuario $Usuario
	 * @return UsuarioAmigo|false
	 */
	public function Amigo_Pendente(Usuario $Usuario) { // Se ele ta esperando minha autorizacao...
		/*$criteria = Criteria::create()->where(Criteria::expr()->eq("usuario", $Usuario));
		$criteria->setMaxResults(1);
		$Quase = $this->getAmigos_Pendentes()->matching($criteria);
		return ($Quase->count() > 0) ? $Quase->first() : false;*/
		$dql = 'SELECT A FROM GDE\\UsuarioAmigo A WHERE A.amigo = ?1 AND A.usuario = ?2 AND A.ativo = FALSE';
		$Pendente = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getID())
			->setParameter(2, $Usuario->getID())
			->setMaxResults(1)
			->getOneOrNullResult();
		return ($Pendente !== null) ? $Pendente : false;
	}

	/**
	 * @param Usuario $Usuario
	 * @return UsuarioAmigo|false
	 */
	public function Amigo(Usuario $Usuario) { // Se eh um amigo atualmente
		if($this->getID() == $Usuario->getID()) {
			// Eu sou meu amigo!
			$UA = new UsuarioAmigo();
			$UA->setUsuario($this);
			$UA->setAmigo($this);
			$UA->setApelido('Eu');
			return $UA;
		}
		$criteria = Criteria::create()->where(Criteria::expr()->eq("ativo", true));
		$criteria->andWhere(Criteria::expr()->eq("amigo", $Usuario));
		$criteria->setMaxResults(1);
		$Amigo = $this->getAmigos()->matching($criteria);
		return ($Amigo->count() > 0) ? $Amigo->first() : false;
	}

	/**
	 * @param bool $ordem_nome Ordernar por nome completo do amigo
	 * @return ArrayCollection|UsuarioAmigo[] Amizades ja autorizadas
	 */
	public function Amigos($ordem_nome = false) { // Lista de amizades ja autorizadas
		$criteria = Criteria::create()->where(Criteria::expr()->eq("ativo", true));
		$Amigos = $this->getAmigos()->matching($criteria);
		if($ordem_nome === true)
			return UsuarioAmigo::Ordenar_Por_Nome($Amigos);
		return $Amigos;
	}

	/**
	 * @param Usuario $Usuario
	 * @param bool $ativo
	 * @param bool $flush
	 * @return bool
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function Adicionar_Amigo(Usuario $Usuario, $ativo = false, $flush = true) {
		if(($this->Quase_Amigo($Usuario) !== false) || ($this->Amigo_Pendente($Usuario) !== false)) // Ja tem uma autorizacao pendente...
			return false;
		$Usuario_Amigo = new UsuarioAmigo();
		$Usuario_Amigo->setUsuario($this);
		$Usuario_Amigo->setAmigo($Usuario);
		$Usuario_Amigo->setAtivo($ativo);
		if($Usuario_Amigo->Save(false) === false)
			return false;
		if($ativo === true) {
			$Usuario_Amigo = new UsuarioAmigo();
			$Usuario_Amigo->setUsuario($Usuario);
			$Usuario_Amigo->setAmigo($this);
			$Usuario_Amigo->setAtivo(true);
			if($Usuario_Amigo->Save(false) === false)
				return false;
		}
		if(($flush) && (self::_EM()->flush() === false))
			return false;
		return true;
	}

	/**
	 * @param Usuario $Usuario
	 * @param bool $flush
	 * @return bool
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function Remover_Amigo(Usuario $Usuario, $flush = true) {
		$Usuario_Amigo = $this->Amigo($Usuario);
		if($Usuario_Amigo === false) {
			$Usuario_Amigo = $this->Quase_Amigo($Usuario);
			if($Usuario_Amigo === false) {
				$Usuario_Amigo = $this->Amigo_Pendente($Usuario);
			}
		}

		$Reverso = $Usuario->Amigo($this);
		if($Reverso === false) {
			$Reverso = $Usuario->Quase_Amigo($this);
			if($Reverso === false)
				$Reverso = $Usuario->Amigo($this);
		}

		if(($Usuario_Amigo === false) && ($Reverso === false))
			// Nada a fazer...
			return false;

		if($Usuario_Amigo !== false)
			$Usuario_Amigo->Delete(false);
		if($Reverso !== false)
			$Reverso->Delete(false);
		if(($flush) && (self::_EM()->flush() === false))
			return false;
		return true;
	}

	/**
	 * @param Usuario $Usuario
	 * @param bool $flush
	 * @return bool
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function Autorizar_Amigo(Usuario $Usuario, $flush = true) {
		$Quase_Amigo = $this->Amigo_Pendente($Usuario);
		if($Quase_Amigo === false)
			return false;

		$Quase_Amigo->setAtivo(true);
		if($Quase_Amigo->Save(false) === false)
			return false;

		$Reverso = $this->Quase_Amigo($Usuario);
		if($Reverso === false) {
			$Reverso = new UsuarioAmigo();
			$Reverso->setUsuario($this);
			$Reverso->setAmigo($Usuario);
		}
		$Reverso->setAtivo(true);
		if($Reverso->Save(false) === false)
			return false;

		if(($flush) && (self::_EM()->flush() === false))
			return false;
		return true;
	}

	/**
	 * Apelido_Ou_Nome
	 *
	 * @param Usuario $Usuario
	 * @param bool|true $completo
	 * @param bool|true $html
	 * @return string
	 */
	public function Apelido_Ou_Nome(Usuario $Usuario, $completo = false, $html = true) {
		$Amigo = $this->Amigo($Usuario);
		if($Amigo !== false)
			return $Amigo->Apelido_Ou_Nome($completo, $html);
		else
			return ($completo) ? $Usuario->getNome_Completo($html) : $Usuario->getNome($html);
	}

	/**
	 * Favorito
	 *
	 * Determina se o $Aluno esta na lista de favoritos deste usuario
	 *
	 * @param Aluno $Aluno
	 * @return bool
	 */
	public function Favorito(Aluno $Aluno) {
		return $this->getFavoritos()->contains($Aluno);
	}

	/**
	 * Cursando
	 *
	 * Retorna true se este Usuario esta cursando a Disciplina / Oferecimento passado
	 *
	 * @param $Disciplina_Oferecimento
	 * @return bool
	 */
	public function Cursando($Disciplina_Oferecimento) {
		if($Disciplina_Oferecimento instanceof Disciplina) {
			return $this->getAluno(true)->Cursou($Disciplina_Oferecimento);
		} elseif($Disciplina_Oferecimento instanceof Oferecimento) {
			return $this->getAluno(true)->getOferecimentos()->contains($Disciplina_Oferecimento);
		} else {
			// Erro!
			return false;
		}
	}

	/**
	 * Tem_Dimensao
	 *
	 * Determina se o aluno deste usuario possui a dimensao do periodo em questao
	 *
	 * @param $Dimensao_dimensoes
	 * @param Periodo $Periodo
	 * @return bool
	 */
	public function Tem_Dimensao($Dimensao_dimensoes, Periodo $Periodo) {
		$dim = $Dimensao_dimensoes instanceof Dimensao;
		if(!$dim && $Dimensao_dimensoes[0] == '????') {
			return false;
		}
		$Atuais = $this->getAluno(true)->getOferecimentos($Periodo->getID());
		foreach($Atuais as $Atual) {
			foreach($Atual->getDimensoes() as $Dimens) {
				if(
					($dim && $Dimens->getID() == $Dimensao_dimensoes->getID()) ||
					(!$dim && (
						($Dimens->getSala(true)->getNome(false) == $Dimensao_dimensoes[0]) &&
						($Dimens->getDia() == $Dimensao_dimensoes[1]) &&
						($Dimens->getHorario() == $Dimensao_dimensoes[2])
					))
				) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Pode_Cursar
	 *
	 * Retorna true se este Usuario pode cursar a Disciplina
	 *
	 * @param Disciplina $Disciplina
	 * @param bool|string $obs
	 * @return bool
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Pode_Cursar(Disciplina $Disciplina, &$obs = false) {
		// ToDo: Na pos nao pode cursar quando ja cursou a mesma disciplina E turma!
		if(($this->Eliminada($Disciplina, false) !== false) && ($Disciplina->getNivel(false) != Disciplina::NIVEL_POS)) {
			if($obs !== false)
				$obs = 'ja_cursou';
			return false;
		}
		$Pre = $Disciplina->getPre_Requisitos($this);
		if(count($Pre) == 0)
			return true;
		$soh_aa200 = false;
		foreach($Pre as $conjunto) {
			$sobrou = false;
			foreach($conjunto as $pre) {
				$aa200 = false;
				if($this->Eliminou($pre[0], $pre[1]) === false) {
					$sobrou = true;
					break; // Vai pro proximo conjunto, este esta incompleto!
				} elseif($pre[0]->getSigla(false) == 'AA200')
					$aa200 = true;
			}
			if($sobrou === false) { // Nao sobrou pre requisito
				if($aa200 === false)
					return true;
				else
					$soh_aa200 = true;
			}
		}
		if($soh_aa200) { // Nao sobrou pre requisito, mas tinha AA200
			if($obs !== false)
				$obs = 'AA200';
			return true;
		}
		if($obs !== false)
			$obs = 'falta_pre';
		return false;
	}

	/**
	 * Conta_Eliminadas
	 *
	 * @return integer
	 */
	public function Conta_Eliminadas() {
		return $this->getEliminadas()->count();
	}

	/**
	 * Eliminada
	 *
	 * Determina se a Disciplina foi eliminada (sem contar equivalencia)
	 *
	 * @param Disciplina $Disciplina
	 * @param bool $parcial
	 * @param bool $novo_formato
	 * @return UsuarioEliminada|false
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Eliminada(Disciplina $Disciplina, $parcial = false, $novo_formato = false) {
		if($Disciplina->getID() == null)
			return false;

		$dql = 'SELECT E FROM GDE\\UsuarioEliminada E WHERE E.usuario = ?1 AND E.disciplina = ?2';
		$Eliminada = self::_EM()->createQuery($dql)
			->setParameter(1, $this->getID())
			->setParameter(2, $Disciplina->getID())
			->getOneOrNullResult();

		if(
			($Eliminada !== null) &&
			(($parcial === true) ||	($Eliminada->getParcial(false) === false))
		) {
			return ($novo_formato)
				? $Eliminada
				: $Eliminada->toOld();
		} else
			return false;
	}

	/**
	 * Eliminou
	 *
	 * Determina se a Disciplina foi eliminada, seja normalmente, por proficiencia ou por equivalencia
	 *
	 * @param Disciplina $Disciplina
	 * @param bool $parcial
	 * @return array|false
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function Eliminou(Disciplina $Disciplina, $parcial = false) {
		if($Disciplina->getID() == null)
			return false;
		// array(array(array(Disc, proficiencia), array(Disc, proficiencia)), equivalencia)
		$eliminada = $this->Eliminada($Disciplina, $parcial, false);
		if($eliminada !== false)
			return array(array($eliminada), false);
		$Equivalencias = $Disciplina->Equivalencias(false);
		foreach($Equivalencias as $Equivalente) {
			$ret = array();
			foreach($Equivalente as $Disciplina) {
				$eliminada = $this->Eliminada($Disciplina, $parcial, false);
				if($eliminada !== false)
					$ret[] = $eliminada;
			}
			if(count($ret) == count($Equivalente))
				return array($ret, true);
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function Token_Email() {
		// ToDo: Nao usar mais sha1
		return sha1($this->login.'GDE'.$this->senha);
	}

	/**
	 * @return bool
	 */
	public function Enviar_Email_Validar() {
		if($this->getProfessor(false) !== null)
			$RA_Matricula = '<li>Matr&iacute;cula: '.$this->getProfessor(true)->getMatricula(true).'</li>';
		elseif($this->getAluno(false) !== null)
			$RA_Matricula = '<li>RA: '.$this->getAluno(true)->getRA(true).'</li>';
		$to = $this->getEmail(false);
		$subject = 'GDE - Valide seu email';
		$message = '
		<html>
		<head>
			<title>Valida&ccedil;&atilde;o de email do GDE</title>
			<style type="text/css">
				a:link, a:visited { background-color: #F6FAFA; }
				a:hover, a:active { background-color: #FFFFFF; }
			</style>
		</head>
		<body style="width: 460px; padding-left: 30px; padding-right:30px; border: 1px solid black; background-color: #F6FAFA;">
			<h1 style="
				background-color: #639BAC; 
				text-align: center; 
				color: #FFFFFF;
				border: 1px solid black;
				-moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; 
			">Valida&ccedil;&atilde;o de email do GDE</h1>
			<div style="font-family: Arial, sans-serif; font-size: 12px; color: #333; ">
				<h2>'.$this->getNome(true).'</h2>
				<p>Obrigado por utilizar o <strong>GDE</strong>, a rede de ajuda acad&ecirc;mica!</p>
				<p>Por favor, confira os seus dados abaixo e valide seu email:</p>
				<ul>
					<li>Nome: '.$this->getNome(true).'</li>
					<li>Email: '.$this->getEmail(true).'</li>
					'.$RA_Matricula.'
				</ul>
				<a href="'.CONFIG_URL.'validar-email/?id='.$this->getID().'&token='.$this->Token_Email().'">Clique aqui para validar seu e-mail.</a>
				<p>Se o seu cliente de e-mail n&atilde;o suportar links, copie o endere&ccedil;o abaixo e cole-o na barra de navega&ccedil;&atilde;o de seu navegador:</p>
				<div style="font-family: Courier, \'Courier New\', monospace; background-color: #FFFFFF">'.CONFIG_URL.'validar-email/?id='.$this->getID().'&token='.$this->Token_Email().'</div>
				<p>Caso n&atilde;o tenha solicitado esse email, por favor desconsidere-o. Provavelmente foi por engano de algum outro usu&aacute;rio.</p>
				<p>Divirta-se!</p>
				<p style="font-style: italic;">Equipe do <strong>GDE</strong></p>
			</div>
		</body>
		</html>
		';
		return Util::Enviar_Email($to, $subject, $message, "GDE <gde@guaycuru.net>", true);
	}

	/**
	 * @param $arquivo
	 * @return bool
	 */
	public function Enviar_Foto($arquivo) {
		$m_largura = 128;
		$m_altura = 150;
		if((is_uploaded_file($arquivo['tmp_name']) === false) || ($arquivo['size'] > 5242880))
			return false;
		list($largura, $altura, $tipo) = getimagesize($arquivo['tmp_name']);
		if($tipo == IMAGETYPE_JPEG) {
			$original = imagecreatefromjpeg($arquivo['tmp_name']);
		} elseif($tipo == IMAGETYPE_GIF) {
			$original = imagecreatefromgif($arquivo['tmp_name']);
		} elseif($tipo == IMAGETYPE_PNG) {
			$original = imagecreatefrompng($arquivo['tmp_name']);
		} else
			return false;
		if($this->foto != null) {
			@unlink(__DIR__.self::PASTA_FOTOS.$this->foto.'.jpg');
			@unlink(__DIR__.self::PASTA_FOTOS.$this->foto.'_th.jpg');
		}
		do {
			$nome = Util::Random(16);
		} while(file_exists(__DIR__.self::PASTA_FOTOS.$nome.'.jpg'));
		$p_largura = ($largura > $m_largura) ? $m_largura / $largura : 1;
		$p_altura = ($altura > $m_altura) ? $m_altura / $altura : 1;
		$porcentagem = min($p_altura, $p_largura);
		$n_largura = round($porcentagem * $largura);
		$n_altura = round($porcentagem * $altura);
		$nova = imagecreatetruecolor($n_largura, $n_altura);
		imagecopyresampled($nova, $original, 0, 0, 0, 0, $n_largura, $n_altura, $largura, $altura);
		if(imagejpeg($nova, __DIR__.self::PASTA_FOTOS.$nome.'.jpg', 90) === true) {
			$this->setFoto($nome);
			$porcentagem_th = $porcentagem / 2;
			$n_largura = round($porcentagem_th * $largura);
			$n_altura = round($porcentagem_th * $altura);
			$nova = imagecreatetruecolor($n_largura, $n_altura);
			imagecopyresampled($nova, $original, 0, 0, 0, 0, $n_largura, $n_altura, $largura, $altura);
			imagejpeg($nova, __DIR__.self::PASTA_FOTOS.$nome.'_th.jpg', 90);
			return true;
		} else
			return false;
	}

	/**
	 * @param Usuario $Usuario
	 * @param $campo
	 * @return bool
	 */
	public function Posso_Ver(Usuario $Usuario, $campo) {
		return (($this->getAdmin()) || (($campo == 't') || (($campo == 'a') && ($this->Amigo($Usuario) !== false))));
	}

	/**
	 * Pode_Ver
	 *
	 * Determina se este usuario pode ver $campo do $Usuario
	 *
	 * @param Usuario $Usuario
	 * @param $campo
	 * @return array|bool
	 */
	public function Pode_Ver(Usuario $Usuario, $campo) {
		if(($this->getID() == $Usuario->getID()) || ($this->getAdmin() === true))
			return true;
		$minha = $this->getCompartilha($campo);
		if($Usuario->getID() == null) // Aluno sem usuario
			return ($minha != 't') ? array(false, 1) : array(true, null);
		$alheia = $Usuario->getCompartilha($campo);
		if($minha == 'f')
			return array(false, 1);
		if($alheia == 'f')
			return array(false, 2);
		if($this->Amigo($Usuario) === false) {
			if($minha == 'a')
				return array(false, 1);
			if($alheia == 'a')
				return array(false, 2);
		}
		return array(true, null);
	}

	/**
	 * Pode_Mudar_Compartilha_Horairo
	 *
	 * Determina se este usuario pode mudar o compartilhamento de horario
	 *
	 * @param $valor
	 * @return bool
	 */
	public function Pode_Mudar_Compartilha_Horario($valor) {
		if(($valor == $this->compartilha_horario) || ($this->getAdmin()))
			return true;
		$mudanca = $this->getMudanca_Horario('U');
		if(($mudanca == null) || (time() - $mudanca >= 15811200))
			return true;
		elseif(($valor == 't') || ($this->compartilha_horario == 'f' && $valor == 'a'))
			return true;
		else
			return false;
	}

	/**
	 * Formata_Horario
	 *
	 * Retorna o horario deste usuario formatado (HTML)
	 *
	 * @param $Horario
	 * @param $dia
	 * @param $hora
	 * @param $meu
	 * @param Periodo|null $Periodo
	 * @param bool $links
	 * @return string
	 */
	public function Formata_Horario($Horario, $dia, $hora, $meu, Periodo $Periodo = null, $links = true) {
		if(!isset($Horario[$dia][$hora]))
			return "-";
		$formatado = array();
		$i = 0;
		foreach($Horario[$dia][$hora] as $dados) {
			list($Oferecimento, $sala) = $dados;
			$strong_oferecimento = ((!$meu) && ($Periodo !== null) && ($this->Cursando($Oferecimento)));
			$strong_sala = ((!$meu) && ($Periodo !== null) && ($this->Tem_Dimensao(array($sala, $dia, $hora), $Periodo)));
			$formatado[$i] = (
				($links)
					? "<a href=\"".CONFIG_URL."oferecimento/".$Oferecimento->getID()."/\" title=\"".$Oferecimento->getDisciplina()->getNome(true)."\">"
					: null
			).
			(($strong_oferecimento)	? "<strong>" : null).
				$Oferecimento->getSigla(true).$Oferecimento->getTurma(true).
			(($strong_oferecimento) ? "</strong>" : null ).
			(($links) ? "</a>" : null);
			if(!empty($dados[1])) {
				$formatado[$i] .= (($links)
						? "/<a href=\"" . CONFIG_URL . "sala/" . $sala . "/\">"
						: "/") .
					(($strong_sala) ? "<strong>" : null) .
					$sala .
					(($strong_sala) ? "</strong>" : null) .
					(($links) ? "</a>" : null);
			}
			$i++;
		}
		return implode("<br />", $formatado);
	}

	/**
	 * Formata_Horario_Sala
	 *
	 * Retorna o horario de uma sala para este usuario formatado (HTML)
	 *
	 * @param $Horario
	 * @param $dia
	 * @param $hora
	 * @return string
	 */
	public function Formata_Horario_Sala($Horario, $dia, $hora) {
		$formatado = array();
		if(isset($Horario[$dia][$hora])) {
			foreach($Horario[$dia][$hora] as $Oferecimento) {
				$strong = $this->Cursando($Oferecimento);
				$formatado[] = "<a href=\"".CONFIG_URL."oferecimento/".$Oferecimento->getID()."/\">".(($strong) ? "<strong>" : "").$Oferecimento->getSigla(true).$Oferecimento->getTurma(true).(($strong) ? "</strong>" : "")."</a>";
			}
			return implode("<br />", $formatado);
		} else
			return "-";
	}

	// Soh pra Planejador... Nao salva!
	public function Adicionar_Oferecimentos($Oferecimentos = array()) {
		foreach($Oferecimentos as $Oferecimento)
			$this->getAluno()->addOferecimentos($Oferecimento);
	}

	// Soh pra Planejador... Nao salva!
	public function Remover_Oferecimentos($Oferecimentos = array()) {
		foreach($Oferecimentos as $Oferecimento)
			$this->getAluno()->removeOferecimentos($Oferecimento);
	}

}
