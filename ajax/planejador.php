<?php

namespace GDE;

define('JSON', true);

require_once('../common/common.inc.php');

if(!isset($_POST['a']))
	die(json_encode(false));

$Ret = false;

if($_POST['a'] == 'n') { // Nova Opcao
	$pp = intval($_POST['pp']);
	$pa = intval($_POST['pa']);
	if($pa == 0) {
		$Periodo = Periodo::Load($pp);
		if(($Periodo->getId_periodo() == null) || ($Periodo->Anterior() === null))
			die(json_encode(false));
		$pa = $Periodo->Anterior()->getPeriodo();
	}
	$Planejado = Planejado::Novo($_Usuario, $pp, $pa, false);
	if($_Usuario->getAluno(false) === null)
		$Planejado->setSimulado(true);
	$Ret['ok'] = ($Planejado->Save(true) !== false);
	if($Ret['ok'] === true)
		$Ret['id'] = $Planejado->getID();
} elseif($_POST['a'] == 'x') { // Excluir Opcao
	$Planejado = Planejado::Load($_POST['id']);
	if(($Planejado->getUsuario(false) === null) || ($Planejado->getUsuario()->getID() != $_Usuario->getID()))
		die(json_encode(false));
	$Ret['ok'] = ($Planejado->Delete(true) != false);
	$Planejados = null;
	if($Ret['ok'] === true)
		$Ret['id'] = Planejado::Algum($_Usuario, $Planejado->getPeriodo()->getID(), $Planejados, $Planejado->getPeriodo_Atual()->getID())->getID();
} else {
	if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1)) {
		$times = array('arvore1' => array(), 'arvore2' => array());
		$times['start'] = microtime(true);
		$tt = 0;
	} else
		$times = array('arvore1' => false, 'arvore2' => false);

	$cores = Planejado::getCores();
	$nc = count($cores);
	$cores_extras = PlanejadoExtra::getCores();
	$nce = count($cores_extras);
	
	$Planejado = Planejado::Load($_POST['id']);
	
	if(($Planejado->getUsuario(false) === null) || ($Planejado->getUsuario()->getID() != $_Usuario->getID()))
		die('forbidden');
	
	if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
		$tt += $times['load'] = microtime(true) - $times['start'] - $tt;

	if($_POST['a'] == 'c') { // Carregar
		$Raw = array();

		$c = (isset($_POST['c'])) ? intval($_POST['c']) : 0;
		$ce = (isset($_POST['ce'])) ? intval($_POST['ce']) : 0;

		if(!isset($_POST['s'])) { // Planejador inteiro
			if($Planejado->getPeriodo_Atual(false) === null) {
				$pa = intval($_POST['pa']);
				if($pa == 0) {
					$PA = Periodo::Load(Dado::Pega_Dados('planejador_periodo_atual'));
					if($PA->getID() != null) {
						$Planejado->setPeriodo_Atual($PA);
						$Planejado->Save(true);
					}
				}
			}
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['verifica1'] = microtime(true) - $times['start'] - $tt;
			
			$EliminadasAdd = array();
			if($_Usuario->getAluno(false) !== null)
				$Atuais = $_Usuario->getAluno()->getOferecimentos($Planejado->getPeriodo_Atual()->getID(), Disciplina::$NIVEIS_GRAD);
			else
				$Atuais = array();
			$Config = array();
			$Disciplinas = array();
			$Disciplinas['N'] = array();
			$siglas = array();
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['getEliminadas'] = microtime(true) - $times['start'] - $tt;
			
			// Processa as disciplinas atualmente em curso
			foreach($Atuais as $Atual) {
				if($_Usuario->Eliminada($Atual->getDisciplina(), false) !== false) // Foi eliminada de verdade
					continue;
				$Tem = $Planejado->Tem_Eliminada($Atual->getDisciplina());
				if($Tem !== false) { // Usuario marcou que possivelmente vai passar
					$EliminadasAdd[] = $Tem;
					$Config[] = array(
						'sigla' => $Atual->getSigla(),
						'eliminada' => true,
						'parcial' => $Tem->getParcial()
					);
				} else {
					$Config[] = array(
						'sigla' => $Atual->getSigla(),
						'eliminada' => false,
						'parcial' => false
					);
				}
			}
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['processa_atuais'] = microtime(true) - $times['start'] - $tt;
			
			// Cria a arvore personalizada para o planejador
			$Usr = $_Usuario;
			$Usr->markReadOnly();
			if($Usr->getAluno(false) !== null)
				$Usr->getAluno()->markReadOnly();
			foreach($EliminadasAdd as $EAdd)
				$Usr->addEliminadas($EAdd->Para_UsuarioEliminada());
			$Planejado->setUsuario($Usr);
			$Arvore = new Arvore($Usr, false, $Planejado->getPeriodo()->getID(), $times['arvore1']);
			if($Arvore->getErro() === true)
				die(json_encode(false));
			$Disciplinas = $Disciplinas + $Arvore->getDisciplinas();
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['cria_arvore'] = microtime(true) - $times['start'] - $tt;
			
			// Marca as disciplinas constantes na arvore
			foreach($Disciplinas as $sem => $lista)
				foreach($lista as $k => $Disciplina)
					$siglas[] = $Disciplina->getSigla();
			
			// Adiciona as eletivas faltantes
			$Disciplinas['E'] = array();
			foreach($Arvore->getEletivas_Faltantes() as $Elet) {
				if($Elet->getTipo() == CurriculoEletiva::TIPO_LIVRE) // Pula as livres
					continue;
				foreach($Elet->getConjuntos(false) as $Falta) {
					if(($Falta->Fechada() === false) || (in_array($Falta->getSigla(false), $siglas)))
						continue;
					$Disciplinas['E'][] = $Falta;
					$siglas[] = $Falta->getSigla();
				}
			}
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['adiciona_eletivas'] = microtime(true) - $times['start'] - $tt;
			
			// Insere as eletivas ja adicionadas
			foreach($Planejado->getOferecimentos() as $Planejada) {
				if(in_array($Planejada->getSigla(), $siglas))
					continue;
				$Disciplinas['N'][] = $Planejada->getDisciplina();
				$siglas[] = $Planejada->getSigla();
			}
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['insere_eletivas'] = microtime(true) - $times['start'] - $tt;
			
			// Organiza as disciplinas em ordem alfabetica de siglas
			foreach($Disciplinas as &$Lista)
				usort($Lista, array('GDE\\Disciplina', 'Organiza'));

			// ToDo: Pra que serve $siglas se aqui eu reseto!?
			$siglas = $nao_pode = array();
			$total = 0;
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['disciplina_alfabetica'] = microtime(true) - $times['start'] - $tt;
			
			foreach($Disciplinas as $sem => $lista) {
				foreach($lista as $k => $Disciplina) {
					$sigla = $Disciplina->getSigla();
					if($Disciplina instanceof CurriculoEletivaConjunto)
						$Disciplina = $Disciplina->getDisciplina();
					if(!isset($Raw[$sem]))
						$Raw[$sem] = array();
					if(in_array($sigla, $siglas) === true)
						continue;
					$siglas[] = $sigla;
					if(($sigla == 'ELET') || ($sigla == 'LING'))
						unset($Disciplinas[$sem][$k]);
					$Raw[$sem][$sigla]['Disciplina'] = $Disciplina;
					$obs = null;
					$Raw[$sem][$sigla]['pode'] = $Planejado->getSimulado() || $Usr->Pode_Cursar($Disciplina, $obs, $Arvore);
					$Raw[$sem][$sigla]['obs'] = $obs;
					if($Raw[$sem][$sigla]['pode']) 
						$Raw[$sem][$sigla]['Oferecimentos'] = Oferecimento::Consultar(array("sigla" => $sigla, "periodo" => $Planejado->getPeriodo()->getID()), "O.turma ASC", $total);
					else {
						$Raw[$sem][$sigla]['Oferecimentos'] = array();
						$nao_pode[$sigla] = true;
					}
					$Raw[$sem][$sigla]['tem'] = (($Raw[$sem][$sigla]['pode']) && ($total > 0));
				}
			}
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['disciplinas'] = microtime(true) - $times['start'] - $tt;
			
			// Processa as disciplinas ja adicionadas e cria os vetores de dados para o script
			$Adicionados = $media_professor = $media_prof_disci = array();
			$Perguntas = AvaliacaoPergunta::Listar();
			$Ret = array();
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['perguntas'] = microtime(true) - $times['start'] - $tt;
			
			// Removo os oferecimentos que eu nao posso cursar!
			foreach($Planejado->getOferecimentos() as $Oferecimento)
				if(isset($nao_pode[$Oferecimento->getSigla()]))
					$Planejado->Remover_Oferecimento($Oferecimento, true);
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['limpa_nao_pode'] = microtime(true) - $times['start'] - $tt;
			
			$Ret['Planejado'] = array(
				'periodo' => $Planejado->getPeriodo()->getID(),
				'periodo_nome' => $Planejado->getPeriodo()->getNome(),
				'periodo_atual' => $Planejado->getPeriodo_Atual()->getID(),
				'periodo_atual_nome' => $Planejado->getPeriodo_Atual()->getNome(),
				'compartilhado' => ($Planejado->getCompartilhado()) ? 't' : 'f',
				'simulado' => ($Planejado->getSimulado()) ? 't' : 'f',
				'Config' => $Config
			);
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['ret_planejado'] = microtime(true) - $times['start'] - $tt;
			
			$timesO['adicionado'] = $timesO['medias'] = $timesO['array'] = $timesO['final'] = 0;
			
			// Retorno dos Oferecimentos
			$Ret['Oferecimentos'] = $siglas_arvore = array();
			foreach($Raw as $semestre => $Lista) {
				foreach($Lista as $sigla => $Dados) {
					$Ofs = array();
					if(!isset($Dados['Oferecimentos'])) // Algo terrivelmente errado...
						continue;
					foreach($Dados['Oferecimentos'] as $Oferecimento) {
						$adicionado = ($Planejado->Tem_Oferecimento($Oferecimento) !== false);
						if($adicionado) {
							$siglas_arvore[] = $Oferecimento->getSigla(true);
							$Adicionados[] = $Oferecimento;
						}

						$id_disciplina = $Oferecimento->getDisciplina()->getId_Disciplina();
						$professores = array();
						foreach($Oferecimento->getProfessores(false) as $Professor) {
							if(!isset($media_professor[$Professor->getID()])) {
								$Media = $Perguntas[0]->getMedia($Professor->getID(), null);
								$mediap = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
								$media_professor[$Professor->getID()] = $mediap;
							} else
								$mediap = $media_professor[$Professor->getID()];
							if(!isset($media_prof_disci[$Professor->getID()][$id_disciplina])) {
								$Media = $Perguntas[1]->getMedia($Professor->getID(), $id_disciplina);
								$media1 = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
								$media_prof_disci[$Professor->getID()][$id_disciplina][1] = $media1;
								$Media = $Perguntas[2]->getMedia($Professor->getID(), $id_disciplina);
								$media2 = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
								$media_prof_disci[$Professor->getID()][$id_disciplina][2] = $media2;
								$Media = $Perguntas[3]->getMedia($Professor->getID(), $id_disciplina);
								$media3 = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
								$media_prof_disci[$Professor->getID()][$id_disciplina][3] = $media3;
							} else {
								$media1 = $media_prof_disci[$Professor->getID()][$id_disciplina][1];
								$media2 = $media_prof_disci[$Professor->getID()][$id_disciplina][2];
								$media3 = $media_prof_disci[$Professor->getID()][$id_disciplina][3];
							}
							$professores[] = array(
								'nome' => $Professor->getNome(true),
								'mediap' => $mediap,
								'media1' => $media1,
								'media2' => $media2,
								'media3' => $media3
							);
						}
						$Ofs[$Oferecimento->getTurma()] = array(
							'id' => $Oferecimento->getID(),
							'siglan' => $Oferecimento->getSigla(true),
							'turma' => $Oferecimento->getTurma(true),
							'professor' => $Oferecimento->Professores(true),
							'vagas' => $Oferecimento->getVagas(),
							'fechado' => $Oferecimento->getFechado(),
							'link' => $Oferecimento->getSigla(false).' '.$Oferecimento->getTurma(true).' ('.$Oferecimento->getVagas().')',
							'horarios' => $Oferecimento->Lista_Horarios(true),
							'professores' => $professores,
							'possivel' => !$Oferecimento->getFechado(),
							'adicionado' => $adicionado,
							'viola_reserva' => $Oferecimento->Viola_Reserva($Usr),
							'eventSources' => array(
								'events' => $Oferecimento->Eventos(true),
								'editable' => false,
								'textColor' => '#000000',
								'color' => (isset($cores[$c])) ? $cores[$c] : $cores[0]
							),
							'Amigos' => array(),
							'total' => -1
						);
					}
					$Ret['Oferecimentos'][$sigla] = array(
						'Disciplina' => array(
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
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['retorno_oferecimentos'] = microtime(true) - $times['start'] - $tt;


			$Usr->Substituir_Oferecimentos($Adicionados, $Planejado->getPeriodo()->getID());

			// Re-faz a arvore porque mudei as atuais
			$Arvore = new Arvore($Usr, false, $Planejado->getPeriodo()->getID(), $times['arvore2']);
			if($Arvore->getErro() === true)
				die(json_encode(false));
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['nova_arvore'] = microtime(true) - $times['start'] - $tt;
			
			$tipos = array();
			foreach($siglas_arvore as $sigla)
				$tipos[$sigla] = $Arvore->getTipo(str_replace('_', ' ', $sigla), false);
			
			$Ret['Arvore'] = array(
				'cp' => $Arvore->getCP(4),
				'cpf' => $Arvore->getCPF(4),
				'integralizacao' => $Arvore->Integralizacao(),
				'tipos' => $tipos
			);
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['Ret_Arvore'] = microtime(true) - $times['start'] - $tt;

			// Indice da cor
			$Ret['c'] = $c;
			
			$Ret['Extras'] = $cor_extra = array();
			foreach($Planejado->getExtras() as $Extra) {
				if(!isset($cor_extra[$Extra->getNome()])) {
					$cor_extra[$Extra->getNome()] = $ce++;
					if($ce >= $nce)
						$ce = 0;
				}
				$Ret['Extras'][] = $Extra->Evento($cores_extras[$cor_extra[$Extra->getNome()]]);
			}
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1))
				$tt += $times['ret_extras'] = microtime(true) - $times['start'] - $tt;
			
			// Indice da cor
			$Ret['ce'] = $ce;
			
			if((isset($_SESSION['admin']['debug'])) && ($_SESSION['admin']['debug'] >= 1)) {
				$Ret['times_arvore1'] = $times['arvore1'];
				$Ret['times_arvore2'] = $times['arvore2'];
				unset($times['start'], $times['arvore1'], $times['arvore2']);
				asort($times, SORT_NUMERIC);
				$Ret['times'] = $times;
			}
		
		} else { // Apenas uma disciplina
			$Usr = $_Usuario;
			$Usr->markReadOnly();
			if($Usr->getAluno(false) !== null)
				$Usr->getAluno()->markReadOnly();
			
			$sigla = $_POST['s'];
			$Disciplina = Disciplina::Por_Sigla($sigla);
			
			$obs = null;
			$Arvore = new Arvore($Usr, false, $Planejado->getPeriodo()->getID());
			if($Arvore->getErro() === true)
				die(json_encode(false));
			$pode = $Planejado->getSimulado() || $Arvore->Pode_Cursar($Disciplina, $obs);
			$total = 0;
			if($pode)
				$Oferecimentos = Oferecimento::Consultar(array("sigla" => $sigla, "periodo" => $Planejado->getPeriodo()->getID()), "O.turma ASC", $total);
			else
				$Oferecimentos = array();
			$tem = ($pode) && ($total > 0);
			$adicionado = false;
			
			$Perguntas = AvaliacaoPergunta::Listar();
			
			$Ret['Oferecimentos'] = $Ofs = $media_prof_disci = $media_professor = array();
			foreach($Oferecimentos as $Oferecimento) {
				$id_disciplina = $Oferecimento->getDisciplina()->getId_Disciplina();
				$professores = array();
				foreach($Oferecimento->getProfessores(false) as $Professor) {
					if(!isset($media_professor[$Professor->getID()])) {
						$Media = $Perguntas[0]->getMedia($Professor->getID(), null);
						$mediap = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
						$media_professor[$Professor->getID()] = $mediap;
					} else
						$mediap = $media_professor[$Professor->getID()];
					if(!isset($media_prof_disci[$Professor->getID()][$id_disciplina])) {
						$Media = $Perguntas[1]->getMedia($Professor->getID(), $id_disciplina);
						$media1 = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
						$media_prof_disci[$Professor->getID()][$id_disciplina][1] = $media1;
						$Media = $Perguntas[2]->getMedia($Professor->getID(), $id_disciplina);
						$media2 = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
						$media_prof_disci[$Professor->getID()][$id_disciplina][2] = $media2;
						$Media = $Perguntas[3]->getMedia($Professor->getID(), $id_disciplina);
						$media3 = ($Media['v'] >= CONFIG_AVALIACAO_MINIMO) ? round($Media['w'] * 10) : -1;
						$media_prof_disci[$Professor->getID()][$id_disciplina][3] = $media3;
					} else {
						$media1 = $media_prof_disci[$Professor->getID()][$id_disciplina][1];
						$media2 = $media_prof_disci[$Professor->getID()][$id_disciplina][2];
						$media3 = $media_prof_disci[$Professor->getID()][$id_disciplina][3];
					}
					$professores[] = array(
						'nome' => $Professor->getNome(true),
						'mediap' => $mediap,
						'media1' => $media1,
						'media2' => $media2,
						'media3' => $media3
					);
				}
				$Ofs[$Oferecimento->getTurma()] = array(
					'id' => $Oferecimento->getID(),
					'siglan' => $Oferecimento->getSigla(true),
					'turma' => $Oferecimento->getTurma(true),
					'professor' => $Oferecimento->Professores(true),
					'vagas' => $Oferecimento->getVagas(true),
					'fechado' => $Oferecimento->getFechado(false),
					'link' => $Oferecimento->getSigla(false).' '.$Oferecimento->getTurma(true).' ('.$Oferecimento->getVagas(true).')',
					'horarios' => $Oferecimento->Lista_Horarios(true),
					'professores' => $professores,
					'possivel' => !$Oferecimento->getFechado(false),
					'adicionado' => $adicionado,
					'eventSources' => array(
						'events' => $Oferecimento->Eventos(true),
						'editable' => false,
						'textColor' => '#000000',
						'color' => (isset($cores[$c])) ? $cores[$c] : $cores[0]
					),
					'Amigos' => array(),
					'total' => -1
				);
			}
			$Ret['Oferecimentos'][$sigla] = array(
				'Disciplina' => array(
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
			$c++;
			if($c >= $nc)
				$c = 0;
		}
	} elseif($_POST['a'] == 'a') { // Adicionar
		$Oferecimento = Oferecimento::Load($_POST['o']);
		if($Oferecimento->getID() == null) {
			$Ret = array('ok' => false, 'Removido' => false, 'motivo' => 'nao_encontrado');
		} else {
			// ToDo: Remove codigo duplicado aqui e no carregar planejador inteiro
			$EliminadasAdd = array();
			if($_Usuario->getAluno(false) !== null)
				$Atuais = $_Usuario->getAluno()->getOferecimentos($Planejado->getPeriodo_Atual()->getID(), Disciplina::$NIVEIS_GRAD);
			else
				$Atuais = array();

			// Processa as disciplinas atualmente em curso
			foreach($Atuais as $Atual) {
				if($_Usuario->Eliminada($Atual->getDisciplina(), false) !== false) // Foi eliminada de verdade
					continue;
				$Tem = $Planejado->Tem_Eliminada($Atual->getDisciplina());
				if($Tem !== false) { // Usuario marcou que possivelmente vai passar
					$EliminadasAdd[] = $Tem;
				}
			}

			// Cria a arvore personalizada para o planejador
			$Usr = $_Usuario;
			$Usr->markReadOnly();
			if($Usr->getAluno(false) !== null)
				$Usr->getAluno()->markReadOnly();
			foreach($EliminadasAdd as $EAdd)
				$Usr->addEliminadas($EAdd->Para_UsuarioEliminada());
			$Planejado->setUsuario($Usr);
			$Oferecimentos = array();
			foreach($Planejado->getOferecimentos() as $Of)
				$Oferecimentos[] = $Of;
			$Usr->Substituir_Oferecimentos($Oferecimentos, $Planejado->getPeriodo()->getID());
			$Arvore = new Arvore($Usr, false, $Planejado->getPeriodo()->getID());
			if($Arvore->getErro() === true)
				die(json_encode(false));

			$Ret = $Planejado->Adicionar_Oferecimento($Oferecimento, $Arvore, true);
			if($Ret['ok'] !== false) {
				// Preciso recalcular a Arvore para atualizar a integralizacao
				$Usr->Adicionar_Oferecimentos(array($Oferecimento));
				$Arvore = new Arvore($Usr, false, $Planejado->getPeriodo()->getID());
				if($Arvore->getErro() === true)
					die(json_encode(false));
				$Ret['Arvore'] = array(
					'cp' => $Arvore->getCP(4),
					'cpf' => $Arvore->getCPF(4),
					'integralizacao' => $Arvore->Integralizacao(),
					'tipos' => array($Oferecimento->getSigla(true) => $Arvore->getTipo($Oferecimento->getSigla(), false))
				);
			}
		}
	} elseif($_POST['a'] == 'r') { // Remover
		$Oferecimento = Oferecimento::Load($_POST['o']);
		$Ret['ok'] = ($Planejado->Remover_Oferecimento($Oferecimento) !== false);
		if($Ret['ok'] !== false) {
			// ToDo: Remove codigo duplicado aqui e no carregar planejador inteiro
			$EliminadasAdd = array();
			if($_Usuario->getAluno(false) !== null)
				$Atuais = $_Usuario->getAluno()->getOferecimentos($Planejado->getPeriodo_Atual()->getID(), Disciplina::$NIVEIS_GRAD);
			else
				$Atuais = array();

			// Processa as disciplinas atualmente em curso
			foreach($Atuais as $Atual) {
				if($_Usuario->Eliminada($Atual->getDisciplina(), false) !== false) // Foi eliminada de verdade
					continue;
				$Tem = $Planejado->Tem_Eliminada($Atual->getDisciplina());
				if($Tem !== false) { // Usuario marcou que possivelmente vai passar
					$EliminadasAdd[] = $Tem;
				}
			}

			// Cria a arvore personalizada para o planejador
			$Usr = $_Usuario;
			$Usr->markReadOnly();
			if($Usr->getAluno(false) !== null)
				$Usr->getAluno()->markReadOnly();
			foreach($EliminadasAdd as $EAdd)
				$Usr->addEliminadas($EAdd->Para_UsuarioEliminada());
			$Planejado->setUsuario($Usr);
			$Oferecimentos = array();
			foreach($Planejado->getOferecimentos() as $Of)
				$Oferecimentos[] = $Of;
			$Usr->Substituir_Oferecimentos($Oferecimentos, $Planejado->getPeriodo()->getID());
			$Arvore = new Arvore($Usr, false, $Planejado->getPeriodo()->getID());
			if($Arvore->getErro() === true)
				die(json_encode(false));
			$Ret['Arvore'] = array(
				'cp' => $Arvore->getCP(4),
				'cpf' => $Arvore->getCPF(4),
				'integralizacao' => $Arvore->Integralizacao(),
				'tipos' => array()
			);
		}
	} elseif($_POST['a'] == 'm') { // Mudar compartilhado
		$Planejado->setCompartilhado(($_POST['v'] == 't'));
		$Ret = ($Planejado->Save(true) !== false);
	} elseif(($_POST['a'] == 's') && ($_Usuario->getAdmin())) { // Mudar simulado
		$Planejado->setSimulado(($_POST['v'] == 't'));
		$Ret = ($Planejado->Save(true) !== false);
	} elseif($_POST['a'] == 'f') { // Marcar eliminadas
		$Planejado->Limpar_Eliminadas(false);
		if(isset($_POST['conf'])) {
			foreach($_POST['conf'] as $sigla) {
				$D = Disciplina::Por_Sigla($sigla);
				$Planejado->Adicionar_Eliminada($D, ((isset($_POST['parciais'])) && (in_array($sigla, $_POST['parciais']))), false);
			}
		}
		$Ret = ($Planejado->Save(true) !== false);
	} elseif($_POST['a'] == 'ae') { // Adicionar Extra
		$erros = array();
		if(strlen($_POST['nome']) < 2)
			$erros[] = "O Nome informado &eacute; inv&aacute;lido.";
		if(($_POST['dia'] < 1) || ($_POST['dia'] > 7))
			$erros[] = "O dia informado &eacute; inv&aacute;lido.";
		if(preg_match('/^\d{1,2}:\d{2}:\d{2}$/i', $_POST['inicio']) == 0)
			$erros[] = "O in&iacute;cio informado &eacute; inv&aacute;lido.";
		if(preg_match('/^\d{1,2}:\d{2}:\d{2}$/i', $_POST['fim']) == 0)
			$erros[] = "O fim informado &eacute; inv&aacute;lido.";
		$Extra = new PlanejadoExtra();
		$Extra->setPlanejado($Planejado);
		$Extra->setNome($_POST['nome']);
		$Extra->setDia($_POST['dia']);
		$Extra->setInicio($_POST['inicio']);
		$Extra->setFim($_POST['fim']);
		if(count($erros) > 0)
			Base::Error_JSON(implode(' ', $erros));
		elseif($Extra->Save(true) === false)
			Base::Error_JSON('Ocorreu um erro. Por favor, tente novamente.');
		else {
			$extra = array('c' => (!empty($cores_extras[intval($_POST['c'])])) ? $Extra->Evento($cores_extras[intval($_POST['c'])]) : '');
			Base::OK_JSON(null, 200, $extra);
		}
	} elseif($_POST['a'] == 're') { // Remover Extra
		$Extra = PlanejadoExtra::Load($_POST['ide']);
		if(($Extra->getID() == null)  || ($Extra->getPlanejado(false) === null) || ($Extra->getPlanejado()->getID() != $Planejado->getID()) || ($Extra->Delete(true) === false))
			Base::Error_JSON('Ocorreu um erro. Por favor, tente novamente.');
		else
			Base::OK_JSON(null);
	} elseif($_POST['a'] == 'ee') { // Editar Extra
		$Extra = PlanejadoExtra::Load($_POST['ide']);
		if(($Extra->getID() == null)  || ($Extra->getPlanejado(false) === null) || ($Extra->getPlanejado()->getID() != $Planejado->getID()))
			Base::Error_JSON('Acesso negado.');
		else {
			$Extra->Mover(intval($_POST['dd']), intval($_POST['md']), ($_POST['t'] == 'd'));
			if($Extra->Save(true) === false)
				Base::Error_JSON('Ocorreu um erro. Por favor, tente novamente.');
			else
				Base::OK_JSON(null);
		}
	} elseif($_POST['a'] == 'cd') { // Carregar Dados extendidos do Oferecimento
		$Oferecimento = Oferecimento::Load($_POST['oid']);
		
		$Amigos = $Planejado->Amigos_Por_Oferecimento($Oferecimento);
		usort($Amigos, "\GDE\UsuarioAmigo::Order_Por_Nome_Sort");
		$lista = array();
		foreach($Amigos as $Amigo)
			$lista[] = "<a href=\"Perfil.php?l=".$Amigo->getAmigo()->getLogin()."\" target=\"_blank\" style=\"text-decoration: none;\" title=\"".$Amigo->getAmigo()->getNome_Completo()."\">".$Amigo->Apelido_Ou_Nome(true, true)."</a>";

		$Ret = array(
			'Amigos' => $lista,
			'total' => $Planejado::Total_Por_Oferecimento($Oferecimento)
		);
	}
}

echo json_encode($Ret);
