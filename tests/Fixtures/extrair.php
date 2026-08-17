<?php
/**
 * Gera tests/Fixtures/gde_amostra.sql: uma fatia REAL e ANONIMIZADA de um
 * banco GDE, com o "mundo" de um unico usuario - curriculo, pre-requisitos,
 * equivalencias, eliminadas, historico do aluno, planejados (com extras) e as
 * turmas dos periodos que interessam.
 *
 * Le direto do banco (SOMENTE SELECTs) em vez de um dump, para que a fatia
 * saia sempre no schema atual: as colunas de saida vem do metadata do
 * Doctrine, entao mexer nas entidades so exige rodar isto de novo.
 *
 * Uso:
 *   php tests/Fixtures/extrair.php
 *   FIXTURE_DB_NAME=gde42_prod php tests/Fixtures/extrair.php
 *
 * Conexao por variaveis de ambiente (defaults entre parenteses):
 *   FIXTURE_DB_HOST (127.0.0.1)  FIXTURE_DB_PORT (3306)
 *   FIXTURE_DB_NAME (gde42_prod) FIXTURE_DB_USER (Web)  FIXTURE_DB_PASS ('')
 *
 * ANONIMIZACAO: nomes, logins, e-mails, senhas e campos de perfil de usuarios,
 * alunos e professores sao substituidos (ver anonimizar()). O RA e mantido,
 * como no pacote publico gde_pacote.sql. Confira a saida antes de publicar.
 */

namespace GDE\Tests\Fixtures;

require __DIR__.'/../bootstrap.php';

/** Usuario cujo "mundo" vira a amostra */
const ID_USUARIO = 1;
/** Hash de 'gde42', a senha unica do pacote publico */
const SENHA = '$2y$10$kdt35E9qnTCBP17wWXfCNeKnAC/1krA0Cbm2AKnWkc.99deFaz/8y';

/**
 * Periodos em que a amostra guarda TODAS as turmas das disciplinas alvo (e
 * nao so as que o usuario cursou/planejou), para o planejador ter alternativas
 * reais. Manter curto: cada periodo custa ~200 oferecimentos.
 */
$PERIODOS_EXPANDIR = array(20232, 20241, 20112, 20121);

$SAIDA = __DIR__.'/gde_amostra.sql';

function env($nome, $padrao) {
	$v = getenv($nome);
	return ($v === false) ? $padrao : $v;
}

$dsn = 'mysql:host='.env('FIXTURE_DB_HOST', '127.0.0.1')
	.';port='.env('FIXTURE_DB_PORT', '3306')
	.';dbname='.env('FIXTURE_DB_NAME', 'gde42_prod')
	.';charset=utf8';
$PDO = new \PDO($dsn, env('FIXTURE_DB_USER', 'Web'), env('FIXTURE_DB_PASS', ''));
$PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
fwrite(STDERR, "Lendo ".env('FIXTURE_DB_NAME', 'gde42_prod')." (somente leitura)...\n");

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function pega(\PDO $PDO, $sql, array $params = array()) {
	$st = $PDO->prepare($sql);
	$st->execute($params);
	return $st->fetchAll(\PDO::FETCH_ASSOC);
}

/** Lista de inteiros para embutir num IN (), ja sanitizada */
function lista(array $ids) {
	if(count($ids) == 0)
		return '(NULL)';
	return '('.implode(',', array_map('intval', $ids)).')';
}

function coluna(array $linhas, $col) {
	$out = array();
	foreach($linhas as $l)
		if($l[$col] !== null)
			$out[$l[$col]] = true;
	return array_keys($out);
}

/** Colunas que cada tabela deve ter, segundo o mapeamento Doctrine */
function colunas_do_orm() {
	$out = array();
	foreach(\GDE\Base::_EM()->getMetadataFactory()->getAllMetadata() as $meta) {
		if((strpos($meta->getName(), 'GDE\\') !== 0) || ($meta->isMappedSuperclass))
			continue;
		$t = $meta->getTableName();
		$out[$t] = $out[$t] ?? array();
		foreach($meta->getFieldNames() as $f)
			$out[$t][] = $meta->getColumnName($f);
		foreach($meta->getAssociationMappings() as $a) {
			foreach(($a['joinColumns'] ?? array()) as $jc)
				$out[$t][] = $jc['name'];
			if(empty($a['joinTable']))
				continue;
			$jt = $a['joinTable']['name'];
			$out[$jt] = $out[$jt] ?? array();
			foreach($a['joinTable']['joinColumns'] as $jc) $out[$jt][] = $jc['name'];
			foreach($a['joinTable']['inverseJoinColumns'] as $jc) $out[$jt][] = $jc['name'];
		}
	}
	foreach($out as $t => $cols)
		$out[$t] = array_values(array_unique($cols));
	return $out;
}

/** joinColumn -> [tabela alvo, coluna alvo], para checar FKs orfas na fatia */
function fks_do_orm() {
	$EM = \GDE\Base::_EM();
	$out = array();
	foreach($EM->getMetadataFactory()->getAllMetadata() as $meta) {
		if((strpos($meta->getName(), 'GDE\\') !== 0) || ($meta->isMappedSuperclass))
			continue;
		foreach($meta->getAssociationMappings() as $a) {
			if(empty($a['joinColumns']))
				continue;
			$alvo = $EM->getClassMetadata($a['targetEntity']);
			foreach($a['joinColumns'] as $jc)
				$out[$meta->getTableName()][$jc['name']] = array($alvo->getTableName(), $jc['referencedColumnName']);
		}
	}
	return $out;
}

// ---------------------------------------------------------------------------
// Fatiar
// ---------------------------------------------------------------------------

$S = array();

$S['gde_usuarios'] = pega($PDO, 'SELECT * FROM gde_usuarios WHERE id_usuario = ?', array(ID_USUARIO));
if(count($S['gde_usuarios']) == 0) {
	fwrite(STDERR, "Usuario ".ID_USUARIO." nao encontrado.\n");
	exit(1);
}
$U = $S['gde_usuarios'][0];
$ra = $U['ra'];
$id_curso = $U['id_curso'];
$id_modalidade = $U['id_modalidade'];
$catalogo = $U['catalogo'];
fwrite(STDERR, "  usuario ".ID_USUARIO.": RA ".$ra.", curso ".$id_curso
	.", modalidade ".$id_modalidade.", catalogo ".$catalogo."\n");

// Tabelas de referencia pequenas: vao inteiras
foreach(array('gde_dados', 'gde_institutos', 'gde_cursos', 'gde_periodos', 'gde_avaliacao_perguntas') as $t)
	$S[$t] = pega($PDO, 'SELECT * FROM `'.$t.'`');
// Modalidades sao 3 mil; so as do curso do usuario e as citadas nos curriculos
$S['gde_modalidades'] = pega($PDO, 'SELECT * FROM gde_modalidades WHERE id_curso = ?', array($id_curso));

$S['gde_alunos'] = pega($PDO, 'SELECT * FROM gde_alunos WHERE ra = ?', array($ra));

// Curriculo e eletivas do curso/modalidade/catalogo do usuario
$S['gde_curriculos'] = pega($PDO,
	'SELECT * FROM gde_curriculos WHERE id_curso = ? AND catalogo = ?
	 AND (id_modalidade IS NULL OR id_modalidade = ?)',
	array($id_curso, $catalogo, $id_modalidade));
$S['gde_curriculos_eletivas'] = pega($PDO,
	'SELECT * FROM gde_curriculos_eletivas WHERE id_curso = ? AND catalogo = ?
	 AND (id_modalidade IS NULL OR id_modalidade = ?)',
	array($id_curso, $catalogo, $id_modalidade));
$S['gde_curriculos_eletivas_conjuntos'] = pega($PDO,
	'SELECT * FROM gde_curriculos_eletivas_conjuntos WHERE id_eletivas IN '
	.lista(coluna($S['gde_curriculos_eletivas'], 'id_eletivas')));

// Eliminadas, planejados e historico
$S['gde_usuarios_eliminadas'] = pega($PDO, 'SELECT * FROM gde_usuarios_eliminadas WHERE id_usuario = ?', array(ID_USUARIO));
$S['gde_usuarios_config'] = pega($PDO, 'SELECT * FROM gde_usuarios_config WHERE id_usuario = ?', array(ID_USUARIO));
$S['gde_planejados'] = pega($PDO, 'SELECT * FROM gde_planejados WHERE id_usuario = ?', array(ID_USUARIO));
$ids_planejados = lista(coluna($S['gde_planejados'], 'id_planejado'));
$S['gde_planejados_eliminadas'] = pega($PDO, 'SELECT * FROM gde_planejados_eliminadas WHERE id_planejado IN '.$ids_planejados);
$S['gde_planejados_extras'] = pega($PDO, 'SELECT * FROM gde_planejados_extras WHERE id_planejado IN '.$ids_planejados);
$S['gde_r_planejados_oferecimentos'] = pega($PDO, 'SELECT * FROM gde_r_planejados_oferecimentos WHERE id_planejado IN '.$ids_planejados);
$S['gde_r_alunos_oferecimentos'] = pega($PDO, 'SELECT * FROM gde_r_alunos_oferecimentos WHERE ra = ?', array($ra));
$S['gde_r_alunos_trancados'] = pega($PDO, 'SELECT * FROM gde_r_alunos_trancados WHERE ra = ?', array($ra));

// Oferecimentos que o usuario cursou/trancou/planejou
$ids_ofs = array_unique(array_merge(
	coluna($S['gde_r_alunos_oferecimentos'], 'id_oferecimento'),
	coluna($S['gde_r_alunos_trancados'], 'id_oferecimento'),
	coluna($S['gde_r_planejados_oferecimentos'], 'id_oferecimento')
));
$Ofs_usuario = pega($PDO, 'SELECT * FROM gde_oferecimentos WHERE id_oferecimento IN '.lista($ids_ofs));

// Disciplinas alvo: as do curriculo mais as que o usuario cursou/planejou
$ids_disciplinas = array_unique(array_merge(
	coluna($S['gde_curriculos'], 'id_disciplina'),
	coluna($S['gde_curriculos_eletivas_conjuntos'], 'id_disciplina'),
	coluna($Ofs_usuario, 'id_disciplina'),
	coluna($S['gde_usuarios_eliminadas'], 'id_disciplina'),
	coluna($S['gde_planejados_eliminadas'], 'id_disciplina')
));

// Todas as turmas dessas disciplinas nos periodos escolhidos
$Ofs_periodos = pega($PDO,
	'SELECT * FROM gde_oferecimentos WHERE id_periodo IN '.lista($PERIODOS_EXPANDIR)
	.' AND id_disciplina IN '.lista($ids_disciplinas));

$por_id = array();
foreach(array_merge($Ofs_usuario, $Ofs_periodos) as $O)
	$por_id[$O['id_oferecimento']] = $O;
$S['gde_oferecimentos'] = array_values($por_id);
$ids_ofs_final = lista(array_keys($por_id));
$ids_disciplinas = array_unique(array_merge($ids_disciplinas, coluna($S['gde_oferecimentos'], 'id_disciplina')));

// Pre-requisitos e equivalencias das disciplinas alvo
$S['gde_pre_conjuntos'] = pega($PDO, 'SELECT * FROM gde_pre_conjuntos WHERE id_disciplina IN '.lista($ids_disciplinas));
$S['gde_pre_lista'] = pega($PDO, 'SELECT * FROM gde_pre_lista WHERE id_conjunto IN '.lista(coluna($S['gde_pre_conjuntos'], 'id_conjunto')));
$S['gde_equivalencias'] = pega($PDO, 'SELECT * FROM gde_equivalencias WHERE id_disciplina IN '.lista($ids_disciplinas));
$S['gde_equivalencias_equivalentes'] = pega($PDO, 'SELECT * FROM gde_equivalencias_equivalentes WHERE id_equivalencia IN '.lista(coluna($S['gde_equivalencias'], 'id_equivalencia')));

// Pre-requisitos e equivalencias apontam para outras disciplinas (por sigla ou
// id): elas tambem entram, senao a Arvore nao consegue resolver o conjunto
$siglas = array_unique(array_merge(
	coluna($S['gde_pre_lista'], 'sigla'),
	coluna($S['gde_equivalencias_equivalentes'], 'sigla')
));
$ids_disciplinas = array_unique(array_merge($ids_disciplinas,
	coluna($S['gde_equivalencias_equivalentes'], 'id_disciplina')));

$placeholders = (count($siglas) > 0) ? implode(',', array_fill(0, count($siglas), '?')) : 'NULL';
$S['gde_disciplinas'] = pega($PDO,
	'SELECT * FROM gde_disciplinas WHERE id_disciplina IN '.lista($ids_disciplinas)
	.' OR sigla IN ('.$placeholders.')', array_values($siglas));

// Horarios, salas, reservas e professores das turmas mantidas
$S['gde_oferecimentos_reservas'] = pega($PDO, 'SELECT * FROM gde_oferecimentos_reservas WHERE id_oferecimento IN '.$ids_ofs_final);
$S['gde_r_oferecimentos_dimensoes'] = pega($PDO, 'SELECT * FROM gde_r_oferecimentos_dimensoes WHERE id_oferecimento IN '.$ids_ofs_final);
$S['gde_dimensoes'] = pega($PDO, 'SELECT * FROM gde_dimensoes WHERE id_dimensao IN '.lista(coluna($S['gde_r_oferecimentos_dimensoes'], 'id_dimensao')));
$S['gde_salas'] = pega($PDO, 'SELECT * FROM gde_salas WHERE id_sala IN '.lista(coluna($S['gde_dimensoes'], 'id_sala')));
$S['gde_r_oferecimentos_professores'] = pega($PDO, 'SELECT * FROM gde_r_oferecimentos_professores WHERE id_oferecimento IN '.$ids_ofs_final);
$S['gde_professores'] = pega($PDO, 'SELECT * FROM gde_professores WHERE id_professor IN '.lista(coluna($S['gde_r_oferecimentos_professores'], 'id_professor')));

// Cursos citados pelas reservas de vaga (Viola_Reserva percorre todas elas)
// ja vieram: gde_cursos foi inteiro

// ---------------------------------------------------------------------------
// Anonimizacao - a amostra e commitada no repositorio
// ---------------------------------------------------------------------------

/** Campos de perfil que sao zerados por completo */
$LIMPAR_USUARIO = array(
	'apelido', 'foto', 'data_nascimento', 'sexo', 'status', 'orkut', 'gtalk',
	'msn', 'blog', 'facebook', 'twitter', 'cidade', 'estado', 'estado_civil',
	'mais', 'exp_profissionais', 'hab_pessoais', 'esp_tecnicas',
	'info_profissional', 'procurando_emprego',
);

function anonimizar(array &$S, array $LIMPAR_USUARIO) {
	foreach($S['gde_usuarios'] as &$u) {
		$id = $u['id_usuario'];
		$u['login'] = 'login'.$id;
		$u['senha'] = SENHA;
		$u['nome'] = 'Usuario';
		$u['sobrenome'] = 'da Silva';
		$u['email'] = 'email'.$id.'@guaycuru.net';
		foreach($LIMPAR_USUARIO as $c)
			if(array_key_exists($c, $u))
				$u[$c] = null;
	}
	unset($u);

	foreach($S['gde_alunos'] as &$a)
		$a['nome'] = 'Aluno '.$a['ra'];
	unset($a);

	// Professores sao dado publico, mas o pacote sempre os anonimizou
	foreach($S['gde_professores'] as &$p) {
		$p['nome'] = 'Professor '.$p['id_professor'];
		foreach(array('email', 'pagina', 'lattes', 'sala', 'matricula', 'matricula_dac') as $c)
			if(array_key_exists($c, $p))
				$p[$c] = null;
	}
	unset($p);

	// Compromissos pessoais do planejador tem texto livre digitado pelo usuario
	foreach($S['gde_planejados_extras'] as $i => &$e)
		$e['nome'] = 'Compromisso '.($i + 1);
	unset($e);
}

anonimizar($S, $LIMPAR_USUARIO);

// ---------------------------------------------------------------------------
// Gravar (um statement por linha: o carregador dos testes nao tem parser SQL)
// ---------------------------------------------------------------------------

function literal($v) {
	if($v === null)
		return 'NULL';
	return "'".str_replace(array("\n", "\r"), array('\\n', '\\r'), addslashes($v))."'";
}

$ORM = colunas_do_orm();
// Ordem de escrita: pais antes de filhos so por clareza, as FKs ficam
// desligadas durante a carga de qualquer jeito
$ORDEM = array(
	'gde_dados', 'gde_institutos', 'gde_cursos', 'gde_modalidades', 'gde_periodos',
	'gde_avaliacao_perguntas', 'gde_disciplinas', 'gde_professores', 'gde_salas',
	'gde_dimensoes', 'gde_alunos', 'gde_usuarios', 'gde_usuarios_config',
	'gde_oferecimentos', 'gde_r_oferecimentos_dimensoes', 'gde_r_oferecimentos_professores',
	'gde_oferecimentos_reservas', 'gde_curriculos', 'gde_curriculos_eletivas',
	'gde_curriculos_eletivas_conjuntos', 'gde_pre_conjuntos', 'gde_pre_lista',
	'gde_equivalencias', 'gde_equivalencias_equivalentes',
	'gde_r_alunos_oferecimentos', 'gde_r_alunos_trancados', 'gde_usuarios_eliminadas',
	'gde_planejados', 'gde_planejados_eliminadas', 'gde_planejados_extras',
	'gde_r_planejados_oferecimentos',
);

$fh = fopen($SAIDA, 'w');
fwrite($fh, "-- GERADO POR tests/Fixtures/extrair.php - NAO EDITAR A MAO.\n");
fwrite($fh, "-- Fatia real e anonimizada do banco GDE: o \"mundo\" do usuario ".ID_USUARIO."\n");
fwrite($fh, "-- (curso ".$id_curso.", modalidade ".$id_modalidade.", catalogo ".$catalogo.", RA ".$ra.").\n");
fwrite($fh, "-- Todas as senhas sao 'gde42'; nomes, logins e e-mails sao ficticios.\n\n");
fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

$FINAL = array();
$total = 0;
foreach($ORDEM as $t) {
	if(!isset($ORM[$t])) {
		fwrite(STDERR, "  ! ".$t." nao existe mais no ORM, ignorando\n");
		continue;
	}
	$FINAL[$t] = array();
	fwrite($fh, "DELETE FROM `".$t."`;\n");
	$linhas = $S[$t] ?? array();
	$total += count($linhas);
	if(count($linhas) == 0) {
		fwrite($fh, "\n");
		continue;
	}
	$cols = $ORM[$t];
	$prefixo = "INSERT INTO `".$t."` (`".implode('`,`', $cols)."`) VALUES ";
	$lote = array();
	foreach($linhas as $l) {
		$vals = $final = array();
		foreach($cols as $c) {
			$v = $l[$c] ?? null;
			$final[$c] = $v;
			$vals[] = literal($v);
		}
		$FINAL[$t][] = $final;
		$lote[] = '('.implode(',', $vals).')';
		if(count($lote) >= 100) {
			fwrite($fh, $prefixo.implode(',', $lote).";\n");
			$lote = array();
		}
	}
	if(count($lote) > 0)
		fwrite($fh, $prefixo.implode(',', $lote).";\n");
	fwrite($fh, "\n");
}
fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($fh);

foreach($ORDEM as $t)
	fwrite(STDERR, sprintf("  %-38s %6d\n", $t, count($S[$t] ?? array())));
fwrite(STDERR, sprintf("  %-38s %6d\n", 'TOTAL', $total));

// ---------------------------------------------------------------------------
// Conferencias
// ---------------------------------------------------------------------------

// FK apontando para linha que ficou de fora vira EntityNotFoundException
// assim que um teste tocar a associacao
$FKS = fks_do_orm();
$pks = array();
foreach($FINAL as $t => $linhas)
	foreach($linhas as $l)
		foreach($l as $c => $v)
			if($v !== null)
				$pks[$t][$c][$v] = true;

$orfas = array();
foreach($FINAL as $t => $linhas)
	foreach(($FKS[$t] ?? array()) as $col => $ref) {
		list($alvo, $col_alvo) = $ref;
		if(!isset($FINAL[$alvo]))
			continue;
		foreach($linhas as $l) {
			$v = $l[$col] ?? null;
			if(($v === null) || (isset($pks[$alvo][$col_alvo][$v])))
				continue;
			$orfas[$t.'.'.$col.' -> '.$alvo] = ($orfas[$t.'.'.$col.' -> '.$alvo] ?? 0) + 1;
		}
	}
if(count($orfas) > 0) {
	fwrite(STDERR, "\n  FKs orfas:\n");
	foreach($orfas as $onde => $n)
		fwrite(STDERR, sprintf("    %-56s %5d\n", $onde, $n));
} else
	fwrite(STDERR, "\n  Integridade referencial: OK\n");

// Rede de seguranca da anonimizacao: nada do banco de origem pode ter
// sobrado nos campos de identificacao
$vazou = array();
foreach($FINAL['gde_usuarios'] as $u) {
	if($u['login'] !== 'login'.$u['id_usuario']) $vazou[] = 'gde_usuarios.login';
	if($u['senha'] !== SENHA) $vazou[] = 'gde_usuarios.senha';
	if($u['nome'] !== 'Usuario') $vazou[] = 'gde_usuarios.nome';
	if(strpos($u['email'], '@guaycuru.net') === false) $vazou[] = 'gde_usuarios.email';
	foreach($LIMPAR_USUARIO as $c)
		if(($u[$c] ?? null) !== null) $vazou[] = 'gde_usuarios.'.$c;
}
foreach($FINAL['gde_alunos'] as $a)
	if(strncmp($a['nome'], 'Aluno ', 6) !== 0) $vazou[] = 'gde_alunos.nome';
foreach($FINAL['gde_professores'] as $p)
	if(strncmp($p['nome'], 'Professor ', 10) !== 0) $vazou[] = 'gde_professores.nome';
foreach($FINAL['gde_planejados_extras'] as $e)
	if(strncmp($e['nome'], 'Compromisso ', 12) !== 0) $vazou[] = 'gde_planejados_extras.nome';

if(count($vazou) > 0) {
	fwrite(STDERR, "\n  ANONIMIZACAO FALHOU em: ".implode(', ', array_unique($vazou))."\n");
	exit(1);
}
fwrite(STDERR, "  Anonimizacao: OK\n");
fwrite(STDERR, "\nGravado: ".$SAIDA." (".number_format(filesize($SAIDA) / 1024, 1)." KB)\n");
