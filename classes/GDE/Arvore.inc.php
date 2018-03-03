<?php

namespace GDE;

class Arvore {

	private $nome;
	private $ra;
	private $curso;
	private $nome_curso;
	private $modalidade;
	private $nome_modalidade;
	private $catalogo;
	private $ingresso;
	private $nivel;

	private $Usuario;

	private $cp;
	private $cpf;

	private $Disciplinas; // Array do tipo $Disciplinas[$semestre] = array(Disciplina, Disciplina, Disciplina);
	private $Pre_Requisitos; //  Array do tipo $Pre_Requisitos[$sigla] = array($sigla, $sigla);
	private $Pos_Requisitos; //  Array do tipo $Pos_Requisitos[$sigla] = array();
	private $Curriculos;
	private $Eletivas;
	private $Eliminadas;
	private $Eletivas_Faltantes;
	private $Atuais;
	private $Periodo;

	private $periodo;
	private $creditos_totais;
	private $creditos_feitos;
	private $creditos_eletivas_eliminados;
	private $creditos_proficiencia;
	private $creditos_atuais;
	private $numero_semestres;

	private $siglas_obrigatorias;
	private $siglas_eletivas;
	private $siglas_todas_eliminadas;
	private $siglas_atuais;
	private $siglas_por_equivalencia;

	private $creditos_eletivas;
	private $creditos_eletivas_atuais;
	private $creditos_linguas;

	private $completa;

	private $largura;
	private $altura;
	private $arquivo;

	private $dados;
	private $posicoes;
	private $setas;

	static $consts = array("inicio_x" => 80, "inicio_y" => 50, "largura" => 90, "altura" => 30, "dist_x" => 55, "dist_y" => 75);

	public function __construct(Usuario $Usuario, $completa = true, $periodo = null, &$times = false) {
		if($times !== false)
			$times = array('start' => microtime(true));

		// Garante que estamos trabalhando em read only no $Usuario
		$Usuario->markReadOnly();

		$this->nome = $Usuario->getNome_Completo(true);
		$this->ra = $Usuario->getAluno(true)->getRA(true);
		$this->curso = $Usuario->getCurso(true)->getNumero(true);
		$this->nome_curso = $Usuario->getCurso(true)->getNome(true);
		$this->modalidade = $Usuario->getModalidade(true)->getSigla(true);
		$this->nome_modalidade = $Usuario->getModalidade(true)->getNome(true);
		$this->catalogo = $Usuario->getCatalogo(true);
		$this->ingresso = $Usuario->getIngresso(true);
		$this->nivel = 'G';

		$this->Usuario = $Usuario;

		if($times !== false)
			$times['usuario'] = microtime(true) - $times['start'];

		$this->cp = 0;
		$this->cpf = 0;
		$this->Periodo = ($periodo != null) ? Periodo::Load($periodo) : Periodo::getAtual();
		$this->periodo = $this->Periodo->getPeriodo();

		if($times !== false)
			$times['periodos'] = microtime(true) - $times['start'];

		$this->Disciplinas = array(); // Disciplinas Faltantes por Semestre
		$this->Pre_Requisitos = array(); // Pre-Requisitos faltantes para a Disciplina!
		$this->Curriculos = Curriculo::Consultar(array("curso" => $this->curso, "modalidade" => $this->modalidade, "catalogo" => $this->catalogo));
		$this->Eletivas = CurriculoEletiva::Consultar(array("curso" => $this->curso, "modalidade" => $this->modalidade, "catalogo" => $this->catalogo));
		$this->Eliminadas = array();
		$this->Eletivas_Faltantes = $this->Eletivas;
		$this->Atuais = $Usuario->getAluno(true)->getOferecimentos($this->periodo, $this->nivel);

		if($times !== false)
			$times['disciplina'] = microtime(true) - $times['start'];

		$this->creditos_totais = 0;
		$this->creditos_feitos = 0;
		$this->creditos_eletivas_eliminados = 0;
		$this->creditos_proficiencia = 0;
		$this->creditos_atuais = 0;
		$this->creditos_faltantes_atuais = 0;
		$this->creditos_faltantes_futuros = 0;
		$this->numero_semestres = 0;

		$this->siglas_obrigatorias = array(); // Siglas de todas as disciplinas obrigatorias
		$this->siglas_eletivas = array(); // Siglas das disciplinas cursadas como eletivas
		$this->siglas_todas_eliminadas = array(); // Siglas de todas as disciplinas ja cursadas
		$this->siglas_atuais = array(); // Siglas das disciplinas sendo cursadas no semestre atual
		$this->siglas_por_equivalencia = array(); // Siglas das disciplinas eliminadas por equivalencia

		$this->creditos_eletivas = array(); // Numero de creditos de disciplinas eletivas por semestre
		$this->creditos_linguas = array(); // Numero de creditos de disciplinas de linguas por semestre

		$this->completa = $completa;

		$Possiveis_Eletivas = array();

		if($completa === false) {
			$this->Eliminadas = $this->Usuario->getEliminadas(false, false, $this->nivel)->toArray();
			$siglas_old_eliminadas = array();
			// Organiza as eliminadas por periodo, credito e sigla
			foreach($this->Eliminadas as $Eli) {
				if($Eli->getParcial() === false)
					$siglas_old_eliminadas[] = $Eli->getDisciplina(true)->getSigla();
			}
			uasort($this->Eliminadas, array("GDE\\UsuarioEliminada", "Ordenar_DAC"));
			$Old_Eliminadas = $this->Eliminadas;
			// Adiciona as Atuais como se fossem Eliminadas... Sao varios motivos, nem tente entender...
			foreach($this->Atuais as $Ofrc) {
				if($this->Usuario->Eliminada($Ofrc->getDisciplina(), false) === false) { // Se ja nao foi eliminada completamente
					$El = new UsuarioEliminada();
					//$El->setUsuario($Usuario);
					$El->setDisciplina($Ofrc->getDisciplina());
					$El->setPeriodo($this->Periodo);
					$this->Eliminadas[$Ofrc->getDisciplina()->getSigla()] = $El;
					$this->siglas_atuais[] = $Ofrc->getDisciplina()->getSigla();
				}
			}
			// Sim, eu tenho que organizar duas vezes, pq pela DAC, verao vem depois dos semestres normais!
			uasort($this->Eliminadas, array("GDE\\UsuarioEliminada", "Ordenar_DAC"));
			$this->Usuario->setEliminadas($this->Eliminadas, false);
		}
		if($times !== false)
			$times['eliminadas'] = microtime(true) - $times['start'];

		$maior_semestre = 1;

		foreach($this->Curriculos as &$For_Curriculo) { // Percorre todas as Disciplinas do Curriculo
			$maior_semestre = $For_Curriculo->getSemestre();
			if($For_Curriculo->getSigla() == 'ELET.') {
				if(!isset($this->creditos_eletivas[$For_Curriculo->getSemestre()]))
					$this->creditos_eletivas[$For_Curriculo->getSemestre()] = 0;
				$this->creditos_eletivas[$For_Curriculo->getSemestre()]++;
				$this->creditos_totais++;
			} elseif($For_Curriculo->getSigla() == 'LING.') {
				if(!isset($this->creditos_linguas[$For_Curriculo->getSemestre()]))
					$this->creditos_linguas[$For_Curriculo->getSemestre()] = 0;
				$this->creditos_linguas[$For_Curriculo->getSemestre()]++;
				$this->creditos_totais++;
			} else { // Disciplina Obrigatoria
				$Disciplina = $For_Curriculo->getDisciplina(true);
				$this->Disciplinas[$For_Curriculo->getSemestre()][] = $Disciplina;
				$this->siglas_obrigatorias[] = $For_Curriculo->getSigla();
				$this->creditos_totais += intval($Disciplina->getCreditos());
			}
		}
		$this->numero_semestres = $maior_semestre;

		if($times !== false)
			$times['curriculo'] = microtime(true) - $times['start'];

		$equivalencias_adicionadas = array();

		if($completa === false) {
			// Verifica quais Disciplinas Obrigatorias do curriculo foram eliminadas pelo usuario, e faz as correcoes necessarias para Equivalencias
			// Aqui as disciplinas do semestre atual tambem contam como eliminadas
			foreach($this->Disciplinas as $semestre => &$For_Deste_Semestre) {
				foreach($For_Deste_Semestre as $k => &$For_Disciplina) {
					$Cursou = $this->Usuario->Eliminou($For_Disciplina, false); // Nao conta as eliminadas parcialmente
					if($Cursou !== false) { // A Disciplina ja foi cursada
						$sigla = $For_Disciplina->getSigla();
						if($Cursou[1] === true) { // Foi por equivalencia
							$s = array_search($sigla, $this->siglas_obrigatorias);
							unset($this->siglas_obrigatorias[$s]); // Remove do Curriculo a Obrigatoria que nao foi cursada
							$this->siglas_por_equivalencia[] = $sigla;
							$this->creditos_totais -= $For_Disciplina->getCreditos();
						} else {
							$this->siglas_todas_eliminadas[] = $sigla;
						}
						if(in_array($sigla, $this->siglas_atuais) === false) { // Soh se ela nao eh uma Atual
							unset($this->Disciplinas[$semestre][$k]); // Remove ela das Disciplinas Faltantes
						}
						foreach($Cursou[0] as &$Disc) {
							if(isset($equivalencias_adicionadas[$Disc[0]->getSigla()]) === false) { // Para corrigir casos que 1 Disciplina vale por 2 do Curriculo!
								$this->creditos_feitos += $Disc[0]->getCreditos();
								if($Disc[1] === true) // Foi por Proficiencia
									$this->creditos_proficiencia += $Disc[0]->getCreditos();
								if($Cursou[1] === true) { // Foi por Equivalencia
									$this->siglas_obrigatorias[] = $Disc[0]->getSigla();
									$this->creditos_totais += $Disc[0]->getCreditos();
								}
								$equivalencias_adicionadas[$Disc[0]->getSigla()] = true;
							}
						}
					}
				}
			}

			if($times !== false)
				$times['cursadas'] = microtime(true) - $times['start'];

			// As siglas atuais sao as que vao ser eliminadas no proximo semestre, mas ainda nao foram neste!
			$this->siglas_atuais = array_diff($this->siglas_todas_eliminadas, $siglas_old_eliminadas);

			if($times !== false)
				$times['siglas_atuais'] = microtime(true) - $times['start'];

			// Percorre a lista de disciplinas eliminadas do usuario em busca de possiveis Eletivas
			foreach($this->Eliminadas as $Eliminada) {
				if(in_array($Eliminada->getDisciplina(true)->getSigla(false), $this->siglas_obrigatorias) === false)
					$Possiveis_Eletivas[$Eliminada->getDisciplina(true)->getSigla(false)] = $Eliminada;
				if(($Eliminada->getProficiencia() === true) && (!isset($equivalencias_adicionadas[$Eliminada->getDisciplina(true)->getSigla(false)])))
					$this->creditos_proficiencia += $Eliminada->getDisciplina()->getCreditos();
			}

			// Organiza a lista de Eletivas: Primeiro as fechadas, depois as semi-livres e por ultimo as livres
			usort($this->Eletivas_Faltantes, array("GDE\\Arvore", "OrdenaEletivas"));
			// Organiza a lista de possiveis Eletivas em ordem decrescente de creditos e de periodos
			uasort($Possiveis_Eletivas, array("GDE\\UsuarioEliminada", "Ordenar_Creditos"));

			if($times !== false)
				$times['possEletivas'] = microtime(true) - $times['start'];

			$volta_eletivas = 0;

			// Verifica quais das possiveis eletivas sao realmente eletivas
			foreach($Possiveis_Eletivas as $sigla => $Eliminada) {
				//echo "<br />\n".$sigla." possivel eletiva (".count($Possiveis_Eletivas)." restantes): ";
				if(in_array($sigla, $this->siglas_eletivas))
					continue;
				$Elimina = $Eliminada->Elimina_Eletiva($this->Eletivas_Faltantes, $Possiveis_Eletivas);
				if($Elimina !== false) {
					//echo "<br />\nEletiva '".$Elimina['eliminada']."' (".$Elimina['sobraram'].") eliminada com '".implode(', ', $Elimina['siglas'])."' (".$Elimina['creditos'].")!";
					$this->siglas_eletivas = array_merge($this->siglas_eletivas, $Elimina['siglas']);
					$this->creditos_eletivas_eliminados += $Elimina['creditos'];
					// Se foram eliminados mais creditos do que eram necessarios, soma creditos nos creditos totais e evita que a arvore fique incorreta
					if($Elimina['diff_creditos'] > 0) {
						//echo "<br />Foi mais do que deveria! Volta ".$Elimina['diff_creditos']."!";
						$this->creditos_totais += $Elimina['diff_creditos'];
						$volta_eletivas += $Elimina['diff_creditos'];
					}
				} /*else
					echo "Nao eliminada!";*/
			}

			if($times !== false)
				$times['eletivasEliminadas'] = microtime(true) - $times['start'];

			// Adiciona ao numero de creditos cursados os creditos das eletivas cursadas
			$this->creditos_feitos += $this->creditos_eletivas_eliminados;

			// Impede que a arvore conte errado o numero de creditos de Eletivas restantes
			$this->creditos_eletivas_eliminados -= $volta_eletivas;

			// Reseta as Eliminadas para as realmente eliminadas
			$this->Eliminadas = $Old_Eliminadas;
			$this->Usuario->setEliminadas($Old_Eliminadas, false);
			$this->siglas_todas_eliminadas = $siglas_old_eliminadas;

			foreach($this->Atuais as $k => &$For_Mtr) {
				$sigla = $For_Mtr->getDisciplina()->getSigla();
				if(in_array($sigla, $this->siglas_todas_eliminadas)) { // Remove disciplinas ja eliminadas em curso
					unset($this->Atuais[$k]);
					continue;
				}

				// Remove deste semestre os creditos que ainda estao sendo cursados
				if((in_array($sigla, $this->siglas_obrigatorias) === true) || (in_array($sigla, $this->siglas_eletivas) === true)) { // Nao eh extra-curricular
					$this->creditos_feitos -= $For_Mtr->getDisciplina()->getCreditos();
					$this->creditos_atuais += $For_Mtr->getDisciplina()->getCreditos();
				}
			}

			// Calcula os creditos faltantes atuais e futuros
			foreach($this->Disciplinas as $Deste_Semestre) {
				foreach($Deste_Semestre as $Disciplina) {
					$sigla = $Disciplina->getSigla();
					if(($sigla != 'ELET') && ($sigla != 'LING') && (in_array($sigla, $this->siglas_atuais) === false))
						$this->creditos_faltantes_futuros += $Disciplina->getCreditos();
				}
			}
			foreach($this->Eletivas_Faltantes as $Elet)
				$this->creditos_faltantes_futuros += $Elet->getCreditos();
			$this->creditos_faltantes_atuais = $this->creditos_faltantes_futuros + $this->creditos_atuais;

			// Calula o CP e o CPF
			$this->cp = ($this->creditos_totais > 0) ? $this->creditos_feitos / $this->creditos_totais : 0;
			$this->cpf = ($this->creditos_totais > 0) ? ($this->creditos_feitos + $this->creditos_atuais) / $this->creditos_totais : 0;
			if($this->cp > 1)
				$this->cp = 1;
			if($this->cpf > 1)
				$this->cpf = 1;

			if($times !== false)
				$times['cpecpf'] = microtime(true) - $times['start'];

			// Remove os pre-requisitos do tipo AAnxx
			for($i = 0.1; $i < 1; $i += 0.05) {
				if($this->cp >= $i) {
					$sigla = 'AA4'.($i*100);
					$El = new UsuarioEliminada();
					//$El->setUsuario($this->Usuario);
					$DisciplinaT = new Disciplina();
					$DisciplinaT->markReadOnly();
					$DisciplinaT->setSigla($sigla);
					$El->setDisciplina($DisciplinaT);
					$El->setPeriodo($this->Periodo);
					$this->Eliminadas[$sigla] = $El;
					$this->siglas_todas_eliminadas[] = $sigla;
				}
			}
			$this->Usuario->setEliminadas($this->Eliminadas, false);

			if($times !== false) {
				$times['setEliminadas'] = microtime(true) - $times['start'];
				unset($times['start']);
				asort($times, SORT_NUMERIC);
			}
		}
	}

	public static function OrdenaEletivas($a, $b) {
		return ($a->getTipo() - $b->getTipo());
	}
	
	private static function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1) {
		/* this way it works well only for orthogonal lines
		imagesetthickness($image, $thick);
		return imageline($image, $x1, $y1, $x2, $y2, $color);
		*/
		if ($thick == 1) { // Linha normal
			return imageline($image, $x1, $y1, $x2, $y2, $color);
		}
		$t = $thick / 2 - 0.5;
		if ($x1 == $x2 || $y1 == $y2) { // Linha horizontal ou vertical
			return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
		}
		$k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
		$a = $t / sqrt(1 + pow($k, 2));
		$points = array(
			round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
			round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
			round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
			round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
		);
		imagefilledpolygon($image, $points, 4, $color);
		return imagepolygon($image, $points, 4, $color);
	}

	private static function imagelinedotted($im, $x1, $y1, $x2, $y2, $color, $dist) {
		$transp = imagecolortransparent($im);

		$style = array($color);

		for ($i=0; $i<$dist; $i++) {
			array_push($style, $transp); // Generate style array - loop needed for customisable distance between the dots
		}

		imagesetstyle($im, $style);
		return (integer) imageline($im, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
		// imagesetstyle($im, array($color)); // Reset style - just in case...
	}

	public static function LinhaEntreDois($image, $consts, $dados, $x1, $y1, $x2, $y2, $cor, $parcial, $largura_s1 = null) {
		if($y1 >= $y2) // Evita problema de pre-requisito "circular"
			return;

		$qual_x = $x1 - $consts['inicio_x'];
		$qual_x /= $consts['largura'] + $consts['dist_x'];
		$qual_x++;
		$altura_l1 = $qual_x * ($consts['dist_y'] * (7/10) / $dados['maximo_x']);
		$de_x = $x1 + ($consts['largura'] / 2);
		$de_y = $y1 + $consts['altura'];
		$pr_x = $x2 + ($consts['largura'] / 2);
		$pr_y = $y2 - 2;
		self::imagelinethick($image, $de_x, $de_y, $de_x, $de_y+$altura_l1, $cor, 2);

		$at_x = $de_x;
		$at_y = $de_y+$altura_l1;
		$temp_x = $qual_x;
		$qual_x_dif = $qual_x;

		if($largura_s1 == null) {
			for($x = 0; $x < $dados['maximo_x']; $x++) {
				$largura_s1 = $x2 + ($qual_x_dif * ($consts['largura'] * (9/10) / $dados['maximo_x']));
				$rgb = imagecolorat($image, $largura_s1, $pr_y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				if(($r == 255) && ($g == 255) && ($b == 255))
					break;
				if($x == $dados['maximo_x']) {
					$qual_x_dif = mt_rand(1, $dados['maximo_x']);
					break; // ja ia sair anyway...
				} else {
					$qual_x_dif = ($qual_x_dif + 1) % $dados['maximo_x'];
				}
			}
		}

		if($y2 > $y1 + $consts['dist_y'] + $consts['altura'] + 1) { // Se tem que descer mais que um nivel...
			$largura_l2 = $x2 + $consts['largura'] + ($consts['dist_x'] * (3/4) * (($dados['maximo_x'] - $qual_x_dif + 1) / $dados['maximo_x']));
			self::imagelinethick($image, $at_x, $at_y, $largura_l2, $de_y+$altura_l1, $cor, 2); // Se prepara pra descer
			//if($image2 != null)
			//self::imagelinethick($image2, $at_x, $at_y, $largura_l2, $de_y+$altura_l1, $cor2, 2);

			$at_x = $largura_l2;
			$at_y = $de_y+$altura_l1;
			$altura_l2 = $pr_y - $altura_l1;
			self::imagelinethick($image, $at_x, $at_y, $at_x, $altura_l2, $cor, 2); // Desce

			$at_y = $altura_l2;
		}
		self::imagelinethick($image, $at_x, $at_y, $largura_s1, $at_y, $cor, 2); // Vai ate a seta

		$at_x = $largura_s1;
		self::imagelinethick($image, $at_x, $at_y, $largura_s1, $pr_y-8, $cor, 2); // Desce pra seta

		if($parcial === false)
			imagefilledpolygon($image, array($largura_s1-4, $pr_y-8, $largura_s1+5, $pr_y-8, $largura_s1, $pr_y), 3, $cor); // A seta (integral)
		else
			imagepolygon($image, array($largura_s1-4, $pr_y-8, $largura_s1+5, $pr_y-8, $largura_s1, $pr_y), 3, $cor); // A seta (parcial)

		return $largura_s1;
	}

	public function PRTPreRR($sigla, &$pintados, &$im_t, &$transp) { // Pinta Retangulos Transparentes de Pre-Requisitos Recursivamente
		if((isset($pintados[$sigla])) && ($pintados[$sigla] === true) || (!isset($this->Pre_Requisitos[$sigla])))
			return;
		$pintados[$sigla] = true;
		$Conjuntos = $this->Pre_Requisitos[$sigla];
		// Cria o retangulos transparentes nos pre-requisitos
		foreach($Conjuntos as $c => $Conjunto) {
			foreach($Conjunto as $Lista) {
				if(isset($this->posicoes[$Lista[0]->getSigla()])) { // Se a disciplina ainda esta la (nao foi eliminada...)
					self::LinhaEntreDois($im_t, self::$consts, $this->dados, $this->posicoes[$Lista[0]->getSigla()][0], $this->posicoes[$Lista[0]->getSigla()][1], $this->posicoes[$sigla][0], $this->posicoes[$sigla][1], $transp, $Lista[1], $this->setas[$Lista[0]->getSigla()][$sigla]);
					// Desenha o retangulo vazio no overlay
					imagefilledrectangle($im_t, $this->posicoes[$Lista[0]->getSigla()][0], $this->posicoes[$Lista[0]->getSigla()][1], $this->posicoes[$Lista[0]->getSigla()][0]+self::$consts['largura']-1, $this->posicoes[$Lista[0]->getSigla()][1]+self::$consts['altura']-1, $transp);
					// Cria os retangulos recursivamente
					$this->PRTPreRR($Lista[0]->getSigla(), $pintados, $im_t, $transp);
				}
			}
			break; // Evita que dois conjuntos que estejam ambos no catalogo aparecam...
		}
	}

	public function PRTPosRR($sigla, &$pintados, &$im_t, &$transp) { // Pinta Retangulos Transparentes de Pos-Requisitos Recursivamente
		if((isset($pintados[$sigla])) && ($pintados[$sigla] === true) || (!isset($this->Pos_Requisitos[$sigla])))
			return;
		$pintados[$sigla] = true;
		// Cria o retangulos transparentes nos pre-requisitos
		foreach($this->Pos_Requisitos[$sigla] as $pos_sigla) {
			if(isset($this->posicoes[$pos_sigla[0]])) { // Se a disciplina ainda esta la (nao foi eliminada...)
				self::LinhaEntreDois($im_t, self::$consts, $this->dados, $this->posicoes[$sigla][0], $this->posicoes[$sigla][1], $this->posicoes[$pos_sigla[0]][0], $this->posicoes[$pos_sigla[0]][1], $transp, $pos_sigla[1], $this->setas[$sigla][$pos_sigla[0]]);
				// Desenha o retangulo vazio no overlay
				imagefilledrectangle($im_t, $this->posicoes[$pos_sigla[0]][0], $this->posicoes[$pos_sigla[0]][1], $this->posicoes[$pos_sigla[0]][0]+self::$consts['largura']-1, $this->posicoes[$pos_sigla[0]][1]+self::$consts['altura']-1, $transp);
				// Cria os retangulos recursivamente
				$this->PRTPosRR($pos_sigla[0], $pintados, $im_t, $transp);
			}
		}
	}

	private function Prepara_Desenho() {
		$this->creditos_eletivas_atuais = 0;

		if($this->completa === false) {
			// Conta quantos creditos de eletivas atuais estao sendo cursados
			foreach($this->Atuais as $Atual) {
				if(in_array($Atual->getDisciplina()->getSigla(), $this->siglas_eletivas)) {
					$this->creditos_eletivas_atuais += $Atual->getDisciplina()->getCreditos();
					$Nova = new Disciplina();
					$Nova->markReadOnly();
					$Nova->setSigla($Atual->getSigla());
					$Nova->setNome($Atual->getDisciplina()->getNome());
					$Nova->setCreditos($Atual->getDisciplina()->getCreditos());
					$this->Disciplinas[$this->numero_semestres][] = $Nova;
					$this->siglas_atuais[] = $Atual->getSigla();
				}
			}

			// Determina o numero de creditos de eletivas restantes por semestre, retirando as eletivas ja cursadas
			$tira = $this->creditos_eletivas_eliminados + $this->creditos_eletivas_atuais;
			foreach($this->creditos_eletivas as $semestre => $creditos) {
				if($tira <= 0)
					break;
				if($tira >= $creditos) {
					$tira -= $creditos;
					unset($this->creditos_eletivas[$semestre]);
				} else {
					$this->creditos_eletivas[$semestre] -= $tira;
					$tira = 0;
				}
			}
		}

		// Cria as pseudo-disciplinas eletivas
		foreach($this->creditos_eletivas as $semestre => $creditos) {
			$Nova = new Disciplina();
			$Nova->markReadOnly();
			$Nova->setSigla('ELET');
			$Nova->setNome('Eletiva');
			$Nova->setCreditos($creditos);
			$this->Disciplinas[$semestre][] = $Nova;
		}

		// Cria as pseudo-disciplinas de linguagem
		foreach($this->creditos_linguas as $semestre => $creditos) {
			$Nova = new Disciplina();
			$Nova->markReadOnly();
			$Nova->setSigla('LING');
			$Nova->setNome('Linguas');
			$Nova->setCreditos($creditos);
			$this->Disciplinas[$semestre][] = $Nova;
		}

		// Pega todos os pre-requisitos das Disciplinas
		foreach($this->Disciplinas as &$For_Deste_Semestre) {
			foreach($For_Deste_Semestre as &$For_Disciplina) {
				if(($this->completa === true) || ($this->Usuario->Pode_Cursar($For_Disciplina) === false))
					$this->Pre_Requisitos[$For_Disciplina->getSigla()] = $For_Disciplina->getPre_Requisitos($this->Usuario, false, $this->catalogo);
			}
		}

	}

	public function Desenha($saida = null) {
		$this->Prepara_Desenho();
		$consts = self::$consts;
		$this->posicoes = array();
		//$c_cores = array(array(255,0,0), array(132,230,188), array(124,77,0), array(0,0,255), array(0,128,0), array(174,94,255), array(128,128,128), array(0,213,213), array(255,95,17), array(255,255,0));
		$c_cores = array(array(0,106,237), array(174,94,255), array(255,84,0), array(132,230,188), array(255,0,0), array(240,255,0), array(252,0,255), array(124,77,0), array(0,128,0), array(128,128,128));

		$maximo_x = 0;
		$maximo_y = count($this->Disciplinas);
		foreach($this->Disciplinas as $Lista)
			if(count($Lista) > $maximo_x)
				$maximo_x = count($Lista);

		$string_titulo = utf8_decode($this->nome_curso).(($this->modalidade != null)?" - ".utf8_decode($this->nome_modalidade)." (".$this->modalidade.")":null)." - ".$this->catalogo;
		$titulo_len = strlen($string_titulo)*9;

		$this->largura = $consts['inicio_x'] + ($maximo_x * ($consts['largura'] + $consts['dist_x'])) + $consts['dist_x'] + 5;
		if($this->largura < $titulo_len)
			$this->largura = $titulo_len + 10;
		$this->altura = $consts['inicio_y'] + ($maximo_y * ($consts['altura'] + $consts['dist_y'])) - $consts['dist_y'] + 10;

		$this->dados = array("largura" => $this->largura, "altura" => $this->altura, "maximo_x" => $maximo_x, "maximo_y" => $maximo_y);

		$im = imagecreatetruecolor($this->largura, $this->altura);
		$branco = $fundo = imagecolorallocate($im, 255, 255, 255); // A cor de fundo!
		$preto = imagecolorallocate($im, 0, 0, 0);
		//imagefill($im, 0, 0, $branco);
		imagefilledrectangle($im, 0, 0, $this->largura, $this->altura, $fundo); // Pinta o fundo da imagem

		$cores = $cors = $mapa = $overlays = array();
		foreach($c_cores as $cor)
			$cores[] = imagecolorallocate($im, $cor[0], $cor[1], $cor[2]);
		for($c = 1; $c <= 10; $c++)
			$fundos[] = imagecreatefromgif(__DIR__.'/../../web/img/arvore_'.$c.'.gif');
		$fundo_preto = imagecreatefromgif(__DIR__.'/../../web/img/arvore_0.gif');

		// Aplica a watermark
		$wm = imagecreatefromgif(__DIR__.'/../../web/img/gde_watermark_r.gif');
		imagecopy($im, $wm, floor($this->dados['largura'] / 2) - 250, floor($this->dados['altura'] / 2) - 250, 0, 0, 500, 500);

		$em_x = $consts['inicio_x'];
		$em_y = $consts['inicio_y'];
		$c = 0;

		imagestring($im, 5, intval(($this->largura)/2 - $titulo_len/2), 15, $string_titulo, $preto);

		uksort($this->Disciplinas, 'strnatcasecmp'); // Ordena os semestres para alguns casos em que eles "sairam" da ordem

		foreach($this->Disciplinas as $semestre => $Lista) {
			$creditos[$semestre] = 0;
			foreach($Lista as $Disciplina) {
				$creditos[$semestre] += $Disciplina->getCreditos();
				$this->posicoes[$Disciplina->getSigla()] = array($em_x, $em_y);
				$atual = (in_array($Disciplina->getSigla(), $this->siglas_atuais));
				$funds[$Disciplina->getSigla()] = ($atual) ? $fundo_preto : $fundos[$c];
				$cors[$Disciplina->getSigla()] = ($atual) ? $preto : $cores[$c];
				$mapa[$Disciplina->getSigla()] = array($em_x, $em_y, $em_x+$consts['largura'], $em_y+$consts['altura']);
				//imagefilledrectangle($im, $em_x, $em_y, $em_x+$consts['largura'], $em_y+$consts['altura'], $cors[$Disciplina->getSigla()]); // Desenha o retangulo da disciplina
				//imagefilledellipse($im, $em_x, $em_y, $consts['largura'], $consts['altura'], $cors[$Disciplina->getSigla()]); // Desenha o retangulo da disciplina
				imagecopy($im, $funds[$Disciplina->getSigla()], $em_x, $em_y, 0, 0, 90, 30); // Desenha o retangulo da disciplina
				$eh_eletiva = (in_array($Disciplina->getSigla(), $this->siglas_eletivas));
				imagestring($im, 5, $em_x-(($Disciplina->getCreditos() > 9) ? 4 : 0)+(($eh_eletiva) ? 7 : 10), $em_y+7, $Disciplina->getSigla()."(".$Disciplina->getCreditos().")".(($eh_eletiva) ? "*" : ""), ($atual) ? $branco : $preto); // Escreve a sigla da disciplina

				$em_x += $consts['largura'] + $consts['dist_x'];
				$c++;
				$c %= 10;

				$this->Pos_Requisitos[$Disciplina->getSigla()] = array();
			}
			imagestring($im, 5, 10, $em_y, $semestre."o sem", $preto);
			imagestring($im, 5, 20, $em_y+18, "(".$creditos[$semestre].")", $preto);
			$em_x = $consts['inicio_x'];
			$em_y += $consts['altura'] + $consts['dist_y'];
		}

		foreach($this->Pre_Requisitos as $sigla => $Conjuntos) {
			foreach($Conjuntos as $c => $Conjunto) {
				foreach($Conjunto as $l => $Lista) {
					// Remove dos pre-requisitos os que nao estao presentes na arvore
					// Essa parte comentada nao fazia mt sentido... Eu tenho que tirar as AA, nao?
					if(/*(substr($Lista[0]->getSigla(), 0, 2) != 'AA') && */(in_array($Lista[0]->getSigla(), $this->siglas_obrigatorias) === false) && (in_array($Lista[0]->getSigla(), $this->siglas_por_equivalencia) === false))
						unset($this->Pre_Requisitos[$sigla][$c]);
				}
			}
		}

		// Cria a lista de pos-requisitos para os presentes na arvore
		foreach($this->Pre_Requisitos as $sigla => $Conjuntos) {
			foreach($Conjuntos as $Conjunto) {
				foreach($Conjunto as $Lista) {
					if(isset($this->posicoes[$Lista[0]->getSigla()])) { // Se a disciplina ainda esta la (nao foi eliminada...)
						$this->Pos_Requisitos[$Lista[0]->getSigla()][] = array($sigla, $Lista[1]);
						// Cria a linha entre a disciplina e seu pre-requisito
						$this->setas[$Lista[0]->getSigla()][$sigla] = self::LinhaEntreDois($im, $consts, $this->dados, $this->posicoes[$Lista[0]->getSigla()][0], $this->posicoes[$Lista[0]->getSigla()][1], $this->posicoes[$sigla][0], $this->posicoes[$sigla][1], $cors[$Lista[0]->getSigla()], $Lista[1], null);
					}
				}
				break; // Evita que dois conjuntos que estejam ambos no catalogo aparecam...
			}
		}

		foreach($this->siglas_obrigatorias as $sigla) {
			if(!isset($this->posicoes[$sigla])) // Se a disciplina ja foi eliminada...
				continue;
			// Cria o retangulo transparente na disciplina
			$im_t = imagecreate($this->largura, $this->altura);
			$cinza = imagecolorallocate($im_t, 64, 64, 64);
			$transp = imagecolorallocate($im_t, 16, 16, 16);
			imagecolortransparent($im_t, $transp);
			//imagefilledrectangle($im_t, 0, 0, $this->largura, $this->altura, $cinza);
			imagefilledrectangle($im_t, $this->posicoes[$sigla][0], $this->posicoes[$sigla][1], $this->posicoes[$sigla][0]+$consts['largura']-1, $this->posicoes[$sigla][1]+$consts['altura']-1, $transp);

			// Cria os overlays transparentes entre disciplina e seus pre-requisitos
			$pintados[$sigla] = array();
			$this->PRTPreRR($sigla, $pintados[$sigla], $im_t, $transp);

			// Cria os overlays transparentes entre disciplina e seus pos-requisitos
			$pintados[$sigla] = array();
			$this->PRTPosRR($sigla, $pintados[$sigla], $im_t, $transp);
			imagepng($im_t, str_replace('.png', '_'.str_replace(' ', '_', $sigla).'.png', $saida));
			imagedestroy($im_t);
		}

		//imagestring($im, 5, $dados['largura'] - 230, $dados['altura'] - 30, "http://GDE.guaycuru.net", $preto);

		// Gera a imagem da arvore
		if($saida != null) {
			imagepng($im, $saida);
		} else {
			header('Content-type: image/png');
			imagepng($im);
		}
		$this->arquivo = $saida;
		imagedestroy($im);

		return true;
	}

	public function Mapa($meu = true) {
		$consts = self::$consts;
		$mapas = array();
		$maximo_x = 0;
		$maximo_y = count($this->Disciplinas);
		foreach($this->Disciplinas as $Lista)
			if(count($Lista) > $maximo_x)
				$maximo_x = count($Lista);

		$em_x = $consts['inicio_x'];
		$em_y = $consts['inicio_y'];

		foreach($this->Disciplinas as $semestre => $Lista) {
			foreach($Lista as $Disciplina) {
				if(($Disciplina->getSigla() == 'ELET') || ($Disciplina->getSigla() == 'LING'))
					continue;
				$mapas[$Disciplina->getSigla()] = array($Disciplina->getNome(), array($em_x, $em_y, $em_x+$consts['largura'], $em_y+$consts['altura']));
				$em_x += $consts['largura'] + $consts['dist_x'];
			}
			$em_x = $consts['inicio_x'];
			$em_y += $consts['altura'] + $consts['dist_y'];
		}

		$mapa = "";
		foreach($mapas as $sigla => $dados)
			$mapa .= "<area shape='rect' coords='".implode(",", $dados[1])."' title='".$dados[0]."' alt='".$dados[0]."' href='".(($meu === true)?"#":CONFIG_URL."disciplina/".$sigla."/' target='_blank'")."' id='Shape_".str_replace(" ", "_", $sigla)."' class='Shapes' />";
		return $mapa;
	}

	public function Inicializa_Mostra($meu = true) {
		$inic = "	\$(\"a#inline\").fancybox({
		'width': ".$this->largura.",
		'height': ".$this->altura.",
		'hideOnContentClick': false,
		'autoDimensions' : false,
		'autoScale' : false,
		'centerOnScroll': false
	})".((isset($_GET['v']))?".trigger('click');":null).";";

		$largura = 900;
		if($largura < $this->largura) {
			$altura = intval(($largura / $this->largura) * $this->altura);
			$div = "<a id=\"inline\" href=\"#popup_arvore\"><img src=\"".$this->arquivo."\" border=\"0\" width=\"".$largura."\" height=\"".$altura."\" alt=\"Arvore\" /></a>
<div style=\"display:none\">
<div id=\"popup_arvore\">
<div class=\"div_img_ov_arvore\" style=\"opacity: 0; position: absolute; display: none; z-index: 100;\"><img class=\"img_ov_arvore\" src=\"\" alt=\"\" /></div><div id=\"div_img_ov_mapa\" style=\"position: absolute; z-index: 110;\"><img id=\"img_ov_mapa\" usemap=\"#mapa\" src=\"".CONFIG_URL."web/images/spacer.gif\" width=\"".$this->largura."\" height=\"".$this->altura."\" alt=\"\" /></div>
<img src=\"".$this->arquivo."\" class=\"img_arvore\" usemap=\"#mapa\" border=\"0\" alt=\"Arvore\" />
</div>
</div>";
		} else {
			$div = "<div class=\"div_img_ov_arvore\" style=\"opacity: 0; position: absolute; display: none; z-index: 100;\"><img class=\"img_ov_arvore\" src=\"\" alt=\"\" /></div><div id=\"div_img_ov_mapa\" style=\"position: absolute; z-index: 110;\"><img id=\"img_ov_mapa\" usemap=\"#mapa\" src=\"".CONFIG_URL."web/images/spacer.gif\" width=\"".$this->largura."\" height=\"".$this->altura."\" alt=\"\" /></div><img src=\"".$this->arquivo."\" id=\"img_arvore\" border=\"0\" alt=\"Arvore\" />";
		}
		$div .= "<map name=\"mapa\" id=\"mapa\">
".$this->Mapa($meu)."
</map>";
		return array($inic, $div);
	}

	public function RMenu($meu = true) {
		$starters = $menus = "";
		$z = 60;
		foreach($this->Disciplinas as $semestre => $Lista) {
			foreach($Lista as $Disciplina) {
				if(($Disciplina->getSigla() == 'ELET') || ($Disciplina->getSigla() == 'LING'))
					//if(in_array($Disciplina->getSigla(), $this->siglas_obrigatorias) === false)
					continue;
				if($meu)
					$starters .= "
	\$(\"#Shape_".str_replace(" ", "_", $Disciplina->getSigla())."\").showMenu({
		opacity: 0.9,
		left: true,
		query: \"#Menu_".str_replace(" ", "_", $Disciplina->getSigla())."\"
	});\r\n";
				if(in_array($Disciplina->getSigla(), $this->siglas_obrigatorias) !== false) {
					$img_overlay = str_replace('.png', '_'.str_replace(' ', '_', $Disciplina->getSigla()).'.png', $this->arquivo);
					$starters .= "
	\$(\"#Shape_".str_replace(" ", "_", $Disciplina->getSigla())."\").hover(function() {
		\$(\"img.img_ov_arvore\").attr(\"src\", \"".$img_overlay."\");
		\$(\"div.div_img_ov_arvore\").fadeTo('slow', 0.9);
	}, function () {
		\$(\"div.div_img_ov_arvore\").fadeOut('fast');
		\$(\"#img_ov_arvore\").attr(\"src\", \"".CONFIG_URL."web/images/spacer.gif\");
	});";
					$menus .= "<div style=\"display: none;\"><img src=\"".$img_overlay."\" alt=\"\" /></div>";
				}
				if($meu)
					$menus .= "<div id=\"Menu_".str_replace(" ", "_", $Disciplina->getSigla(true))."\" style=\"display:none;\" class=\"RMenu\">
   <ul>
   <li>".$Disciplina->getNome()."</li>
   <li><a href=\"".CONFIG_URL."disciplina/".$Disciplina->getSigla(true)."/\" target=\"_blank\">Informa&ccedil;&otilde;es</a></li>
   <li><a href=\"#\" onclick=\"Elimina('".$Disciplina->getSigla(true)."', 0 ,0); return false;\">Eliminar Normalmente</a></li>
   <li><a href=\"#\" onclick=\"Elimina('".$Disciplina->getSigla(true)."', 1 ,0); return false;\">Eliminar Parcialmente</a></li>
   <li><a href=\"#\" onclick=\"Elimina('".$Disciplina->getSigla(true)."', 0 ,1); return false;\">Eliminar Por Profici&ecirc;ncia</a></li>
   </ul>
</div>\r\n";
			}
		}

		return array($starters, $menus);
	}

	public function Integralizacao() {
		$limites = $this->Limite();
		return "<br />
<pre>
  <strong>Aviso:</strong> Esta n&atilde;o &eacute; a integraliza&ccedil;&atilde;o oficial da DAC e pode ou n&atilde;o refletir a realidade.
  <strong>Aluno:</strong> <a href=\"".CONFIG_URL."perfil/usuario=".$this->Usuario->getLogin(true)."\">".$this->nome."</a>
  <strong>Registro Acad&ecirc;mico (RA):</strong> ".sprintf("%06d", $this->ra)."
  <strong>Curso:</strong> ".$this->curso." - ".$this->nome_curso."
  <strong>Modalidade:</strong> ".(($this->modalidade != null)?strtoupper($this->modalidade)." - ".$this->nome_modalidade:"-")."
  <strong>Cat&aacute;logo:</strong> ".$this->catalogo."
  <strong>Ingresso:</strong> 1&ordm; semestre de ".$this->ingresso."
  <strong>Limite para Integraliza&ccedil;&atilde;o:</strong> ".(($limites !== false)?$limites[1]."&ordm; semestre de ".$limites[0]:"Desconhecido! Favor preencher o ano de Ingresso!")."
  <strong><i>Semestre Atual</i>:</strong> ".$this->getPeriodo()->getNome()."
  <strong><i>Neste semestre</i> ->    CP :</strong> ".$this->getCP(4)."  <strong>Cr&eacute;ditos Obtidos:</strong> ".$this->creditos_feitos."  <strong>Cr&eacute;ditos Faltantes:</strong> ".$this->creditos_faltantes_atuais."
  <strong><i>Final do semestre </i>-> CPF:</strong> ".$this->getCPF(4)."  <strong>Cr&eacute;ditos Obtidos:</strong> ".($this->creditos_feitos+$this->creditos_atuais)."  <strong>Cr&eacute;ditos Faltantes:</strong> ".$this->creditos_faltantes_futuros."


".$this->Historico()."
</pre>";
	}

	public function Historico() {
		$i = 0;
		$eliminadas = array();
		//$ret = "  HISTORICO ATUAL:\r\n";
		$ret = "  <strong>Disciplinas j&aacute; cursadas:</strong>\r\n";
		foreach($this->Eliminadas as $Eliminada) {
			$sigla = $Eliminada->getDisciplina(true)->getSigla(true);
			if(($sigla == 'AA200') || ($Eliminada->getParcial() === true)) // Pula as eliminadas parcialmente
				continue;
			$eliminadas[] = $sigla;
			if(substr($sigla, 0, 2) == 'AA')
				continue;
			$i++;
			$url = Disciplina::URL_Disciplina($sigla);
			$ret .= "  <a href=\"".$url."\" class=\"sigla\" title=\"".$Eliminada->getDisciplina(true)->getNome(true)."\" target=\"_blank\">".$sigla."</a>(".(sprintf("%02d", $Eliminada->getDisciplina(true)->getCreditos(true))).")".$this->getTipo($sigla, true).' '.$Eliminada->getPeriodo(true)->getNome(true); //  MC102S06+  9,2150 4 1S07
			if($i % 4 == 0) $ret .= "<br />"; // normal eh 3
		}

		//$ret .= "\r\n\r\n  DISCIPLINAS OBRIGATORIAS FALTANTES PARA O CURSO   ".$this->curso." MODALIDADE ".$this->modalidade.":\r\n";
		$ret .= "\r\n\r\n  <strong>Disciplinas Obrigat&oacute;rias que ainda devem ser cursadas:</strong>\r\n";

		$i = 0;
		foreach($this->Disciplinas as $semestre => $Deste_Semestre) {
			foreach($Deste_Semestre as $Disciplina) {
				$sigla = $Disciplina->getSigla(true);
				if(($sigla != 'ELET') && ($sigla != 'LING') && (in_array($sigla, $this->siglas_atuais) === false)) {
					$url = Disciplina::URL_Disciplina($Disciplina->getSigla(false));
					$ret .= "  <a href=\"".$url."\" class=\"sigla\" title=\"".$Disciplina->getNome(true)."\" target=\"_blank\">".$Disciplina->getSigla(true)."</a>(".(sprintf("%02d", $Disciplina->getCreditos(false))).")";
					$i++;
					if($i % 7 == 0) $ret .= "\r\n";
				}
			}
		}

		//$ret .= "\r\n\r\n  DISCIPLINAS ELETIVAS FALTANTES PARA O CURSO   ".$this->curso." MODALIDADE ".$this->modalidade.":\r\n";
		$ret .= "\r\n\r\n  <strong>Disciplinas Eletivas que ainda devem ser cursadas:</strong>\r\n";

		foreach($this->Eletivas_Faltantes as $Elet) {
			$creditos = $Elet->getCreditos();
			if($Elet->getTipo() == CurriculoEletiva::TIPO_LIVRE) {
				//$ret .= "\r\n  OBTER  ".(($creditos<10)?' ':null).$creditos." CREDITO(S) DENTRE  -----";
				$ret .= "  Obter ".(($creditos<10)?' ':null).$creditos." Cr&eacute;dito(s) dentre quaisquer disciplinas da Unicamp";
			} else {
				$i = 0;
				//$ret .= "\r\n  OBTER  ".(($creditos<10)?' ':null).$creditos." CREDITO(S) DENTRE";
				$ret .= "  Obter ".(($creditos<10)?' ':null).$creditos." Cr&eacute;dito(s) dentre a(s) seguinte(s) disciplina(s): ";
				foreach($Elet->getConjuntos() as $Falta) {
					$url = Disciplina::URL_Disciplina($Falta->getSigla(false));
					$ret .= "  <a href=\"".$url."\" class=\"sigla\" title=\"".$Falta->getDisciplina()->getNome(true)."\" target=\"_blank\">".$Falta->getSigla(true)."</a>(".(($Falta->getDisciplina()->getCreditos() > 0)?(sprintf("%02d", $Falta->getDisciplina()->getCreditos(false))):'??').")";
					$i++;
					if($i % 5 == 0) $ret .= "\r\n                                                             ";
				}
			}
			$ret .= "\r\n";
		}

		//$ret .= "\r\n\r\n  MATRICULAS ATUAIS:\r\n";
		$ret .= "\r\n\r\n  <strong>Disciplinas sendo cursadas atualmente:</strong>\r\n";

		if($this->completa === false) {
			$i = 0;
			foreach($this->Atuais as $Atual) {
				$ret .= "  <a href=\"".CONFIG_URL."oferecimento/".$Atual->getID()."\" class=\"sigla\" title=\"".$Atual->getDisciplina(true)->getNome()."\" target=\"_blank\">".$Atual->getDisciplina(true)->getSigla(true).$Atual->getTurma(true)."</a>(".(sprintf("%02d", $Atual->getDisciplina(true)->getCreditos(false))).")".$this->getTipo($Atual->getSigla(true), true);
				$i++;
				if($i % 6 == 0) $ret .= "\r\n";
			}
		}

		//$ret .= "\r\n\r\n\r\n\r\n     TABELA EXPLICATIVA DOS CODIGOS UTILIZADOS NO HISTORICO ESCOLAR:\r\n     ---------------------------------------------------------------\r\n\r\n  CONTEUDO DO HISTORICO:  LLXXX (DD)I\r\n\r\n    LLXXX=CODIGO DISCIPLINA T=TURMA DD=NUMERO DE CREDITOS\r\n    I=APROVEITAMENTO PARA INTEGRALIZACAO\r\n\r\n  APROVEITAMENTO PARA INTEGRALIZACAO:\r\n\r\n    += OBRIGATORIA   *= ELETIVA                X= EXTRA-CURRICULAR";
		$ret .= "\r\n\r\n\r\n\r\n  <strong>C&oacute;digos utilizados:</strong>\r\n     ---------------------------------------------------------------\r\n\r\n  Formato:  LLXXXT(DD)I PPAAAA\r\n\r\n    LLXXX = Sigla da Disciplina DD = Cr&eacute;ditos da Disciplina\r\n    I = Tipo de Aproveitamento PP = Periodo AAAA = Ano\r\n\r\n  Tipos de Aproveitamento:\r\n\r\n    + = Obrigat&oacute;ria   * = Eletiva    X = Extra-Curricular";

		return $ret;

	}

	public function Limite() {
		if($this->ingresso == null)
			return false;
		$maximo_semestres = ceil($this->numero_semestres * 1.5);
		if($this->creditos_totais > 0)
			$semestres = floor((($this->creditos_totais - $this->creditos_proficiencia) * $maximo_semestres) / $this->creditos_totais);
		else
			$semestres = 5;
		if($semestres < 5) $semestres = 5;
		$limite_ano = floor($this->ingresso + ($semestres / 2) - 0.5);
		$limite_sem = ($semestres % 2 == 0) ? 2 : 1;
		return array($limite_ano, $limite_sem);
	}

	public function getDisciplinas() {
		return $this->Disciplinas;
	}

	public function getPre_Requisitos() {
		return $this->Pre_Requisitos;
	}

	public function getEletivas() {
		return $this->Eletivas;
	}

	public function getEletivas_Faltantes() {
		return $this->Eletivas_Faltantes;
	}

	public function getSiglas_Obrigatorias() {
		return $this->siglas_obrigatorias;
	}

	public function getSiglas_Atuais() {
		return $this->siglas_atuais;
	}

	public function getCreditos_Totais() {
		return $this->creditos_totais;
	}

	public function getCreditos_Feitos() {
		return $this->creditos_feitos;
	}

	public function getCreditos_Proficiencia() {
		return $this->creditos_proficiencia;
	}

	public function getCreditos_Atuais() {
		return $this->creditos_atuais;
	}

	public function getNumero_Semestres() {
		return $this->numero_semestres;
	}

	public function getPeriodo() {
		return $this->Periodo;
	}

	public function getCP($digitos) {
		return number_format($this->cp, $digitos, ',', '.');
	}

	public function getCPAA400() {
		return floor($this->cp * 100);
	}

	public function getCPF($digitos) {
		return number_format($this->cpf, $digitos, ',', '.');
	}

	public function getTipo($sigla, $simbolo = true) {
		if(in_array($sigla, $this->siglas_obrigatorias))
			return ($simbolo) ? '+' : 'Obrigat&oacute;ria';
		elseif(in_array($sigla, $this->siglas_eletivas))
			return ($simbolo) ? '*' : 'Eletiva';
		else
			return ($simbolo) ? 'X' : 'Extra-Curricular';
	}

	public function Pode_Cursar(Disciplina $Disciplina, &$obs = false) {
		return $this->Usuario->Pode_Cursar($Disciplina, $obs, $this);
	}
}
