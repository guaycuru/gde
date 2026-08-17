<?php

namespace GDE;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * Planejado
 *
 * @ORM\Table(
 *   name="gde_planejados",
 *   indexes={
 *     @ORM\Index(name="compartilhado", columns={"compartilhado"}),
 *     @ORM\Index(name="simulado", columns={"simulado"})
 *   }
 * )
 * @ORM\Entity
 */
class Planejado extends Base {
	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 */
	protected $id_planejado;

	/**
	 * @var Usuario
	 *
	 * @ORM\ManyToOne(targetEntity="Usuario")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="id_usuario", referencedColumnName="id_usuario")
	 * })
	 */
	protected $usuario;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo", referencedColumnName="id_periodo")
	 */
	protected $periodo;

	/**
	 * @var Periodo
	 *
	 * @ORM\ManyToOne(targetEntity="Periodo")
	 * @ORM\JoinColumn(name="id_periodo_atual", referencedColumnName="id_periodo")
	 */
	protected $periodo_atual;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="Oferecimento")
	 * @ORM\JoinTable(name="gde_r_planejados_oferecimentos",
	 *      joinColumns={@ORM\JoinColumn(name="id_planejado", referencedColumnName="id_planejado")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="id_oferecimento", referencedColumnName="id_oferecimento")}
	 * )
	 */
	protected $oferecimentos;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="PlanejadoEliminada", mappedBy="planejado", cascade={"persist", "remove"})
	 */
	protected $eliminadas;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="PlanejadoExtra", mappedBy="planejado", cascade={"persist", "remove"})
	 */
	protected $extras;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $compartilhado = false;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean", options={"default"=0}, nullable=false)
	 */
	protected $simulado = false;

	private static $_cores = array(
		'#D96666', '#E67399', '#E6804D', '#A7A77D', '#B373B3', '#DDDD00', '#65AD89',
		'#8C66D9', '#C244AB', '#A992A9', '#D1BC36', '#668CB3', '#4650BD', '#59BFB3',
		'#F2A640', '#C7561E', '#94A2BE', '#668CD9', '#B5515D', '#E0C240', '#BE9494'
	);

	/**
	 * Por_Usuario
	 *
	 * @param Usuario $Usuario
	 * @param $periodo
	 * @param bool $compartilhados
	 * @return mixed
	 */
	public static function Por_Usuario(Usuario $Usuario, $periodo, $compartilhados = true) {
		if($periodo instanceof Periodo)
			$periodo = $periodo->getPeriodo();

		$params = array('usuario' => $Usuario->getID(), 'periodo' => $periodo);
		if($compartilhados === true)
			$params['compartilhado'] = true;

		return self::FindBy($params, array('id_planejado' => 'ASC'));
	}

	public static function getCores() {
		return self::$_cores;
	}

	public static function Algum(Usuario $Usuario, $periodo, &$Planejados = null, $periodo_atual = null) {
		if($Planejados == null)
			$Planejados = self::Por_Usuario($Usuario, $periodo, false);
		if(count($Planejados) == 0) {
			if($periodo_atual == null)
				$periodo_atual = Dado::Pega_Dados('planejador_periodo_atual');
			return self::Novo($Usuario, $periodo, $periodo_atual, true);
		} else
			return $Planejados[0];
	}

	public static function Novo($Usuario, $periodo, $periodo_atual = null, $flush = true) {
		$Novo = new self();
		$Novo->setUsuario($Usuario);
		$Novo->setPeriodo(Periodo::Load($periodo));
		if($periodo_atual == null) {
			$Atual = Periodo::getAtual();
			$periodo_atual = $Atual->getPeriodo();
		} else
			$Atual = Periodo::Load($periodo_atual);
		$Novo->setPeriodo_Atual($Atual);
		if($Usuario->getAluno(false) !== null) {
			foreach($Usuario->getAluno()->getOferecimentos($periodo_atual) as $Atual)
				if($Atual->getDisciplina(false) !== null)
					$Novo->Adicionar_Eliminada($Atual->getDisciplina(), false, false);
		}
		return ($Novo->Save($flush) !== false) ? $Novo : false;
	}

	/**
	 * Cria uma nova opcao de planejamento para o periodo informado, sem salvar
	 *
	 * @param Usuario $Usuario
	 * @param integer $pp Periodo a planejar
	 * @param integer $pa Periodo atual, ou 0 para usar o anterior ao planejado
	 * @return Planejado|null null se o periodo informado (ou o anterior a ele) nao existir
	 */
	public static function Criar_Opcao(Usuario $Usuario, $pp, $pa) {
		$pp = intval($pp);
		$pa = intval($pa);
		$Periodo = Periodo::Load($pp);
		if($Periodo->getId_periodo() == null)
			return null;
		if($pa == 0) {
			if($Periodo->Anterior() === null)
				return null;
			$pa = $Periodo->Anterior()->getPeriodo();
		}
		$Planejado = self::Novo($Usuario, $pp, $pa, false);
		if($Usuario->getAluno(false) === null)
			$Planejado->setSimulado(true);
		return $Planejado;
	}

	/**
	 * Verifica se este planejado existe e pertence ao usuario informado
	 *
	 * @param Usuario $Usuario
	 * @return boolean
	 */
	public function Pertence_A(Usuario $Usuario) {
		if($this->getUsuario(false) === null)
			return false;
		return ($this->getUsuario()->getID() == $Usuario->getID());
	}

	// Metodo que passa no cheap check do ProxyGenerator
	public function getId_planejado() {
		return $this->id_planejado;
	}

	public function Adicionar_Oferecimento(Oferecimento $Oferecimento, Arvore $Arvore, $salvar = true) {
		$obs = '';
		if($Arvore->Pode_Cursar($Oferecimento->getDisciplina(), $obs) === false)
			return array('ok' => false, 'Removido' => false, 'motivo' => 'nao_pode_cursar: '.$obs);
		$Tem = $this->Tem_Oferecimento($Oferecimento, true);
		if($Tem !== false) {
			if($Tem->getID() == $Oferecimento->getID()) // Ta tentando adicionar uma que ja esta la!
				return array('ok' => false, 'Removido' => false, 'motivo' => 'ja_esta');
			else // Ta tentando adicionar uma outra turma
				$Removido = array('id' => $Tem->getID());
		} else
			$Removido = false;

		// Marca o Oferecimento como read only
		$Oferecimento->markReadOnly();
		
		if((isset($Removido)) && (!empty($Removido['id'])))
			$this->removeOferecimentos(Oferecimento::Load($Removido['id']));
		$this->addOferecimentos($Oferecimento);
		if($salvar === false)
			$ok = true;
		else
			$ok = $this->Save(true) !== false;
		return array('ok' => $ok, 'Removido' => $Removido);
	}

	public function Remover_Oferecimento(Oferecimento $Oferecimento, $salvar = true) {
		if($this->Tem_Oferecimento($Oferecimento, false) === false)
			return false;

		// Marca o Oferecimento como read only
		$Oferecimento->markReadOnly();

		$this->removeOferecimentos($Oferecimento);
		if($salvar === false)
			return true;
		return ($this->Save(true) !== false);
	}

	public function Tem_Oferecimento(Oferecimento $Oferecimento, $checa_disciplina = false) {
		if($checa_disciplina === false)
			return ($this->getOferecimentos()->contains($Oferecimento)) ? $Oferecimento : false;
		foreach($this->getOferecimentos() as $Of)
			if($Of->getDisciplina()->getId() == $Oferecimento->getDisciplina()->getId())
				return $Of;
		return false;
	}

	public function Adicionar_Eliminada(Disciplina $Disciplina, $parcial = false, $flush = true) {
		$Tem = $this->Tem_Eliminada($Disciplina);
		if($Tem !== false)
			return false;
		$Nova = new PlanejadoEliminada();
		$Nova->setDisciplina($Disciplina);
		$Nova->setParcial($parcial);
		$this->addEliminadas($Nova);
		return ($this->Save($flush) !== false);
	}

	public function Remover_Eliminada(Disciplina $Disciplina, $flush = true) {
		$Eliminada = $this->Tem_Eliminada($Disciplina);
		if($Eliminada === false)
			return false;
		$this->removeEliminadas($Eliminada);
		return ($this->Save($flush) !== false);
	}

	public function Limpar_Eliminadas($flush = true) {
		$this->clearEliminadas();
		return ($this->Save($flush) !== false);
	}

	/**
	 * Substitui as eliminadas deste planejado pela lista informada
	 *
	 * @param array|null $conf IDs das disciplinas marcadas como eliminadas
	 * @param array|null $parciais IDs (dentre os de $conf) marcados como parciais
	 * @param bool $flush
	 * @return boolean
	 */
	public function Marcar_Eliminadas($conf = null, $parciais = null, $flush = true) {
		$this->Limpar_Eliminadas(false);
		if($conf !== null) {
			foreach($conf as $id_disciplina) {
				$D = Disciplina::Load($id_disciplina);
				$this->Adicionar_Eliminada($D, (($parciais !== null) && (in_array($id_disciplina, $parciais))), false);
			}
		}
		return ($this->Save($flush) !== false);
	}

	/**
	 * Lista as eliminadas "possiveis" deste planejado: as disciplinas que o
	 * usuario cursa no periodo atual e marcou que provavelmente vai passar
	 *
	 * @param Usuario $Usuario
	 * @param array $Config Recebe o estado (sigla/eliminada/parcial) de cada atual
	 * @return PlanejadoEliminada[]
	 */
	public function Eliminadas_Possiveis(Usuario $Usuario, &$Config = null) {
		$EliminadasAdd = $Config = array();
		if($Usuario->getAluno(false) !== null)
			$Atuais = $Usuario->getAluno()->getOferecimentos($this->getPeriodo_Atual()->getID(), Disciplina::$NIVEIS_GRAD);
		else
			$Atuais = array();

		// Processa as disciplinas atualmente em curso
		foreach($Atuais as $Atual) {
			if($Usuario->Eliminada($Atual->getDisciplina(), false) !== false) // Foi eliminada de verdade
				continue;
			$Tem = $this->Tem_Eliminada($Atual->getDisciplina());
			if($Tem !== false) { // Usuario marcou que possivelmente vai passar
				$EliminadasAdd[] = $Tem;
				$Config[] = array(
					'id' => $Atual->getDisciplina()->getId(),
					'sigla' => $Atual->getSigla(),
					'eliminada' => true,
					'parcial' => $Tem->getParcial()
				);
			} else {
				$Config[] = array(
					'id' => $Atual->getDisciplina()->getId(),
					'sigla' => $Atual->getSigla(),
					'eliminada' => false,
					'parcial' => false
				);
			}
		}
		return $EliminadasAdd;
	}

	/**
	 * Prepara o usuario para a arvore personalizada do planejador: read-only e
	 * com as eliminadas possiveis somadas as que ele ja tem
	 *
	 * Devolve o proprio objeto recebido (que e alterado), nao uma copia
	 *
	 * @param Usuario $Usuario
	 * @param array $Config Recebe o estado das disciplinas atuais
	 * @return Usuario
	 */
	public function Usuario_Para_Arvore(Usuario $Usuario, &$Config = null) {
		$EliminadasAdd = $this->Eliminadas_Possiveis($Usuario, $Config);
		$Usuario->markReadOnly();
		if($Usuario->getAluno(false) !== null)
			$Usuario->getAluno()->markReadOnly();
		foreach($EliminadasAdd as $EAdd)
			$Usuario->addEliminadas($EAdd->Para_UsuarioEliminada());
		return $Usuario;
	}

	/**
	 * Monta a arvore do planejador tratando os oferecimentos ja planejados como
	 * se fossem os atuais do usuario
	 *
	 * Quem chama deve verificar $Arvore->getErro()
	 *
	 * @param Usuario $Usuario
	 * @return Arvore
	 */
	public function Montar_Arvore(Usuario $Usuario) {
		$Usr = $this->Usuario_Para_Arvore($Usuario);
		$this->setUsuario($Usr);
		$Oferecimentos = array();
		foreach($this->getOferecimentos() as $Of)
			$Oferecimentos[] = $Of;
		$Usr->Substituir_Oferecimentos($Oferecimentos, $this->getPeriodo()->getID());
		return new Arvore($Usr, false, $this->getPeriodo());
	}

	/**
	 * Monta todos os dados do planejador para o usuario informado
	 *
	 * @param Usuario $Usuario
	 * @param integer $c Indice da cor da primeira disciplina
	 * @param integer $ce Indice da cor do primeiro extra
	 * @param integer|null $pa Periodo atual informado pelo cliente
	 * @param bool $debug Inclui os tempos de execucao no retorno
	 * @param array $times Tempos ja medidos por quem chamou
	 * @param float $tt Total ja acumulado em $times
	 * @return array|null null se a arvore nao puder ser montada
	 */
	public function Montar_Dados(Usuario $Usuario, $c = 0, $ce = 0, $pa = null, $debug = false, &$times = null, &$tt = null) {
		if($times === null)
			$times = array('arvore1' => false, 'arvore2' => false);
		if(($debug) && (!isset($times['start']))) { // Quem chamou nao instrumentou
			$times['arvore1'] = $times['arvore2'] = array();
			$times['start'] = microtime(true);
			$tt = 0;
		}

		$cores = self::getCores();
		$nc = count($cores);
		$cores_extras = PlanejadoExtra::getCores();
		$nce = count($cores_extras);

		$Raw = array();

		if($this->getPeriodo_Atual(false) === null) {
			if(intval($pa) == 0) {
				$PA = Periodo::Load(Dado::Pega_Dados('planejador_periodo_atual'));
				if($PA->getID() != null) {
					$this->setPeriodo_Atual($PA);
					$this->Save(true);
				}
			}
		}

		if($debug)
			$tt += $times['verifica1'] = microtime(true) - $times['start'] - $tt;

		$Disciplinas = array();
		$Disciplinas['N'] = array();
		$id_disciplinas = array();

		if($debug)
			$tt += $times['getEliminadas'] = microtime(true) - $times['start'] - $tt;

		// Cria a arvore personalizada para o planejador
		$Config = array();
		$Usr = $this->Usuario_Para_Arvore($Usuario, $Config);

		if($debug)
			$tt += $times['processa_atuais'] = microtime(true) - $times['start'] - $tt;

		$this->setUsuario($Usr);
		$Arvore = new Arvore($Usr, false, $this->getPeriodo(), $times['arvore1']);
		if($Arvore->getErro() === true)
			return null;
		$Disciplinas = $Disciplinas + $Arvore->getDisciplinas();

		if($debug)
			$tt += $times['cria_arvore'] = microtime(true) - $times['start'] - $tt;

		// Marca as disciplinas constantes na arvore
		foreach($Disciplinas as $sem => $lista)
			foreach($lista as $k => $Disciplina)
				$id_disciplinas[] = $Disciplina->getId();

		// Adiciona as eletivas faltantes
		$Disciplinas['E'] = array();
		foreach($Arvore->getEletivas_Faltantes() as $Elet) {
			if($Elet->getTipo() == CurriculoEletiva::TIPO_LIVRE) // Pula as livres
				continue;
			foreach($Elet->getConjuntos(false) as $Falta) {
				if(($Falta->Fechada() === false) || (in_array($Falta->getDisciplina()->getId(), $id_disciplinas)))
					continue;
				$Disciplinas['E'][] = $Falta;
				$id_disciplinas[] = $Falta->getDisciplina()->getId();
			}
		}

		if($debug)
			$tt += $times['adiciona_eletivas'] = microtime(true) - $times['start'] - $tt;

		// Insere as eletivas ja adicionadas
		foreach($this->getOferecimentos() as $Planejada) {
			if(in_array($Planejada->getDisciplina()->getId(), $id_disciplinas))
				continue;
			$Disciplinas['N'][] = $Planejada->getDisciplina();
			$id_disciplinas[] = $Planejada->getDisciplina()->getId();
		}

		if($debug)
			$tt += $times['insere_eletivas'] = microtime(true) - $times['start'] - $tt;

		// Organiza as disciplinas em ordem alfabetica de siglas
		foreach($Disciplinas as &$Lista)
			usort($Lista, array('GDE\\Disciplina', 'Organiza'));
		unset($Lista);

		// ToDo: Pra que serve $id_disciplinas se aqui eu reseto!?
		$id_disciplinas = $nao_pode = array();
		$total = 0;

		if($debug)
			$tt += $times['disciplina_alfabetica'] = microtime(true) - $times['start'] - $tt;

		foreach($Disciplinas as $sem => $lista) {
			foreach($lista as $k => $Disciplina) {
				$sigla = $Disciplina->getSigla();
				if($Disciplina instanceof CurriculoEletivaConjunto)
					$Disciplina = $Disciplina->getDisciplina();
				$id_disciplina = $Disciplina->getId();
				if(!isset($Raw[$sem]))
					$Raw[$sem] = array();
				if(in_array($id_disciplina, $id_disciplinas) === true)
					continue;
				$id_disciplinas[] = $id_disciplina;
				if(($sigla == Curriculo::ELETIVA) || ($sigla == Curriculo::LINGUAS))
					unset($Disciplinas[$sem][$k]);
				$Raw[$sem][$id_disciplina]['Disciplina'] = $Disciplina;
				$obs = null;
				$Raw[$sem][$id_disciplina]['pode'] = $this->getSimulado() || $Usr->Pode_Cursar($Disciplina, $obs, $Arvore);
				$Raw[$sem][$id_disciplina]['obs'] = $obs;
				if($Raw[$sem][$id_disciplina]['pode'])
					$Raw[$sem][$id_disciplina]['Oferecimentos'] = Oferecimento::Consultar(array("disciplina" => $id_disciplina, "periodo" => $this->getPeriodo()->getID()), "O.turma ASC", $total);
				else {
					$Raw[$sem][$id_disciplina]['Oferecimentos'] = array();
					$nao_pode[$id_disciplina] = true;
				}
				$Raw[$sem][$id_disciplina]['tem'] = (($Raw[$sem][$id_disciplina]['pode']) && ($total > 0));
			}
		}

		if($debug)
			$tt += $times['disciplinas'] = microtime(true) - $times['start'] - $tt;

		// Processa as disciplinas ja adicionadas e cria os vetores de dados para o script
		$Adicionados = $media_professor = $media_prof_disci = array();
		$Perguntas = AvaliacaoPergunta::Listar();
		$Ret = array();

		if($debug)
			$tt += $times['perguntas'] = microtime(true) - $times['start'] - $tt;

		// Removo os oferecimentos que eu nao posso cursar!
		foreach($this->getOferecimentos() as $Oferecimento)
			if(isset($nao_pode[$Oferecimento->getSigla()]))
				$this->Remover_Oferecimento($Oferecimento, true);

		if($debug)
			$tt += $times['limpa_nao_pode'] = microtime(true) - $times['start'] - $tt;

		$Ret['Planejado'] = array(
			'periodo' => $this->getPeriodo()->getID(),
			'periodo_nome' => $this->getPeriodo()->getNome(),
			'data_matricula_inicio' => $this->getPeriodo()->getData_Matricula_Inicio('d/m/Y'),
			'data_matricula_fim' => $this->getPeriodo()->getData_Matricula_Fim('d/m/Y'),
			'data_alteracao_inicio' => $this->getPeriodo()->getData_Alteracao_Inicio('d/m/Y'),
			'data_alteracao_fim' => $this->getPeriodo()->getData_Alteracao_Fim('d/m/Y'),
			'periodo_atual' => $this->getPeriodo_Atual()->getID(),
			'periodo_atual_nome' => $this->getPeriodo_Atual()->getNome(),
			'compartilhado' => ($this->getCompartilhado()) ? 't' : 'f',
			'simulado' => ($this->getSimulado()) ? 't' : 'f',
			'Config' => $Config
		);

		if($debug)
			$tt += $times['ret_planejado'] = microtime(true) - $times['start'] - $tt;

		// Retorno dos Oferecimentos
		$Ret['Oferecimentos'] = $siglas_arvore = array();
		foreach($Raw as $semestre => $Lista) {
			foreach($Lista as $id_disciplina => $Dados) {
				$Ofs = array();
				if(!isset($Dados['Oferecimentos'])) // Algo terrivelmente errado...
					continue;
				foreach($Dados['Oferecimentos'] as $Oferecimento) {
					$adicionado = ($this->Tem_Oferecimento($Oferecimento) !== false);
					if($adicionado) {
						$siglas_arvore[$id_disciplina] = $Oferecimento->getSigla(true);
						$Adicionados[] = $Oferecimento;
					}

					$professores = $Oferecimento->Dados_Professores($Perguntas, $media_professor, $media_prof_disci);
					$Ofs[$Oferecimento->getTurma()] = $Oferecimento->Dados_Planejador(
						$professores,
						(isset($cores[$c])) ? $cores[$c] : $cores[0],
						$adicionado,
						$Usr
					);
				}
				// Faco cast pra string para nao estragar a ordem no JSON
				$Ret['Oferecimentos']["D".$Dados['Disciplina']->getId()] = array(
					'Disciplina' => array(
						'id' => $Dados['Disciplina']->getId(),
						'sigla' => $Dados['Disciplina']->getSigla(false),
						'siglan' => str_replace(' ', '_', $Dados['Disciplina']->getSigla(false)),
						'nome' => $Dados['Disciplina']->getNome(false, true),
						'creditos' => $Dados['Disciplina']->getCreditos(false, true),
						'semestre' => $semestre,
						'quinzenal' => $Dados['Disciplina']->getQuinzenal(),
						'c' => $c,
						'cor' => (isset($cores[$c])) ? $cores[$c] : $cores[0],
						'tem' => $Dados['tem'],
						'pode' => $Dados['pode'],
						'obs' => $Dados['obs']
					),
					'Oferecimentos' => $Ofs
				);
				$c++;
				if($c >= $nc)
					$c = 0;
			}
		}

		if($debug)
			$tt += $times['retorno_oferecimentos'] = microtime(true) - $times['start'] - $tt;

		$Usr->Substituir_Oferecimentos($Adicionados, $this->getPeriodo()->getID());

		// Re-faz a arvore porque mudei as atuais
		$Arvore = new Arvore($Usr, false, $this->getPeriodo(), $times['arvore2']);
		if($Arvore->getErro() === true)
			return null;

		if($debug)
			$tt += $times['nova_arvore'] = microtime(true) - $times['start'] - $tt;

		$tipos = array();
		foreach($siglas_arvore as $id_disciplina => $sigla)
			$tipos[$id_disciplina] = $Arvore->getTipo(str_replace('_', ' ', $sigla), false);

		$Ret['Arvore'] = $Arvore->Dados($tipos);

		if($debug)
			$tt += $times['Ret_Arvore'] = microtime(true) - $times['start'] - $tt;

		// Indice da cor
		$Ret['c'] = $c;

		$Ret['Extras'] = $cor_extra = array();
		foreach($this->getExtras() as $Extra) {
			if(!isset($cor_extra[$Extra->getNome()])) {
				$cor_extra[$Extra->getNome()] = $ce++;
				if($ce >= $nce)
					$ce = 0;
			}
			$Ret['Extras'][] = $Extra->Evento($cores_extras[$cor_extra[$Extra->getNome()]]);
		}

		if($debug)
			$tt += $times['ret_extras'] = microtime(true) - $times['start'] - $tt;

		// Indice da cor
		$Ret['ce'] = $ce;

		if($debug) {
			$Ret['times_arvore1'] = $times['arvore1'];
			$Ret['times_arvore2'] = $times['arvore2'];
			unset($times['start'], $times['arvore1'], $times['arvore2']);
			asort($times, SORT_NUMERIC);
			$Ret['times'] = $times;
		}

		return $Ret;
	}

	/**
	 * Monta os dados de uma unica disciplina para o planejador
	 *
	 * @param Usuario $Usuario
	 * @param Disciplina $Disciplina
	 * @param integer $c Indice da cor
	 * @return array|null null se a arvore nao puder ser montada
	 */
	public function Montar_Dados_Disciplina(Usuario $Usuario, Disciplina $Disciplina, $c = 0) {
		$cores = self::getCores();
		$Ret = array();

		// Cria a arvore personalizada para o planejador
		$Usr = $this->Usuario_Para_Arvore($Usuario);

		$obs = null;
		$Arvore = new Arvore($Usr, false, $this->getPeriodo());
		if($Arvore->getErro() === true)
			return null;
		$pode = $this->getSimulado() || $Arvore->Pode_Cursar($Disciplina, $obs);
		$total = 0;
		if($pode)
			$Oferecimentos = Oferecimento::Consultar(array("disciplina" => $Disciplina->getId(), "periodo" => $this->getPeriodo()->getID()), "O.turma ASC", $total);
		else
			$Oferecimentos = array();
		$tem = ($pode) && ($total > 0);
		$adicionado = false;

		$Perguntas = AvaliacaoPergunta::Listar();

		$Ret['Oferecimentos'] = $Ofs = $media_prof_disci = $media_professor = array();
		foreach($Oferecimentos as $Oferecimento) {
			$professores = $Oferecimento->Dados_Professores($Perguntas, $media_professor, $media_prof_disci);
			// Diferente do planejador inteiro, aqui as vagas passam por htmlspecialchars
			// e nao ha checagem de reserva
			$Ofs[$Oferecimento->getTurma()] = $Oferecimento->Dados_Planejador(
				$professores,
				(isset($cores[$c])) ? $cores[$c] : $cores[0],
				$adicionado,
				null,
				true
			);
		}
		$Ret['Oferecimentos'][$Disciplina->getId()] = array(
			'Disciplina' => array(
				'id' => $Disciplina->getId(),
				'sigla' => $Disciplina->getSigla(false),
				'siglan' => str_replace(' ', '_', $Disciplina->getSigla(false)),
				'nome' => $Disciplina->getNome(false, true),
				'creditos' => $Disciplina->getCreditos(false, true),
				'semestre' => 'N',
				'c' => $c,
				'cor' => (isset($cores[$c])) ? $cores[$c] : $cores[0],
				'tem' => $tem,
				'pode' => $pode,
				'obs' => $obs
			),
			'Oferecimentos' => $Ofs
		);
		return $Ret;
	}

	public function Tem_Eliminada(Disciplina $Disciplina) {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("disciplina", $Disciplina));
		$criteria->setMaxResults(1);
		$Tem = $this->getEliminadas()->matching($criteria);
		if($Tem->isEmpty() === true)
			return false;
		return $Tem->first();
	}

	// Soh adiciona, nao salva (obviamente)
	public function Adicionar_Extra(PlanejadoExtra $Extra) {
		return $this->addExtras($Extra);
	}

	// Soh remove, nao salva (obviamente)
	public function Remover_Extra(PlanejadoExtra $Extra) {
		$this->removeExtras($Extra);
	}

	public function Monta_Horario() {
		$Horario = array();
		foreach($this->getOferecimentos() as $Oferecimento) {
			$horarios = $Oferecimento->Lista_Horarios();
			foreach($horarios as $h)
				$Horario[$h[0]][$h[1]][] = array($Oferecimento, $h[2]);
		}
		return $Horario;
	}

	/**
	 * @param Oferecimento $Oferecimento
	 * @return UsuarioAmigo[]
	 */
	public function Amigos_Por_Oferecimento(Oferecimento $Oferecimento) {
		$dql = "SELECT UA FROM GDE\\UsuarioAmigo UA JOIN UA.amigo AS A JOIN A.planejados AS P WHERE :id_oferecimento MEMBER OF P.oferecimentos AND P.simulado = FALSE AND UA.usuario = :id_usuario AND UA.ativo = TRUE";
		return self::_EM()
			->createQuery($dql)
			->setParameters(array('id_oferecimento' => $Oferecimento->getID(), 'id_usuario' => $this->getUsuario()->getID()))
			->getResult();
	}

	/**
	 * Monta os links para os perfis dos amigos informados, em ordem de nome
	 *
	 * @param UsuarioAmigo[] $Amigos
	 * @return string[]
	 */
	public static function Links_Amigos(array $Amigos) {
		usort($Amigos, "\GDE\UsuarioAmigo::Order_Por_Nome_Sort");
		$lista = array();
		foreach($Amigos as $Amigo)
			$lista[] = "<a href=\"".CONFIG_URL."perfil/?usuario=".$Amigo->getAmigo()->getLogin()."\" target=\"_blank\" style=\"text-decoration: none;\" title=\"".$Amigo->getAmigo()->getNome_Completo()."\">".$Amigo->Apelido_Ou_Nome(true, true)."</a>";
		return $lista;
	}

	/**
	 * @param Oferecimento $Oferecimento
	 * @return int
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public static function Total_Por_Oferecimento(Oferecimento $Oferecimento) {
		$sql = 'SELECT COUNT(DISTINCT P.id_usuario) AS `total` FROM `gde_planejados` AS P '.
				'INNER JOIN `gde_r_planejados_oferecimentos` AS PO ON (PO.`id_planejado` = P.`id_planejado`) '.
				'WHERE P.`simulado` = FALSE '.
				'AND PO.`id_oferecimento` = :id_oferecimento';
		$rsm = new ResultSetMappingBuilder(self::_EM());
		$rsm->addScalarResult('total', 'total');
		$query = self::_EM()->createNativeQuery($sql, $rsm);
		$query->setParameter('id_oferecimento', $Oferecimento->getID());
		return $query->getSingleScalarResult();
	}

	public function Save($flush = true) {
		// ToDo: Fazer isto de uma forma melhor!

		// Marca algumas coisas como read only
		$this->getUsuario()->markReadOnly();
		$this->getPeriodo()->markReadOnly();
		$this->getPeriodo_Atual()->markReadOnly();

		$ok = parent::Save(false) !== false;
		Base::_EM()->flush();
		return $ok;
	}

}
