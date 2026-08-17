# Rodando os testes

## Comandos básicos

```bash
composer test               # suíte completa (unit + integração), ~2s
composer test:unit          # só os rápidos, sem banco
composer test:integration   # só os que usam o MariaDB
```

Ou direto com o PHPUnit, que aceita filtros úteis:

```bash
vendor/bin/phpunit                                    # tudo
vendor/bin/phpunit tests/Unit/UtilTest.php            # um arquivo só
vendor/bin/phpunit --filter testCreditosAtuais        # um teste pelo nome
vendor/bin/phpunit --testdox                          # saída legível, frase por teste
```

## Cobertura

Requer Xdebug (já presente no PHP local). No PowerShell:

```powershell
$env:XDEBUG_MODE='coverage'; vendor/bin/phpunit --coverage-text            # resumo no terminal
$env:XDEBUG_MODE='coverage'; vendor/bin/phpunit --coverage-html cobertura  # relatório navegável em cobertura/index.html
```

Em bash/CI: `XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text`.

## Banco dos testes de integração

Os testes em `tests/Integration/` precisam de um MariaDB **dedicado e descartável**.
A conexão é resolvida nesta ordem:

1. Variáveis de ambiente `TEST_DB_HOST` / `TEST_DB_PORT` / `TEST_DB_USER` /
   `TEST_DB_PASS` / `TEST_DB_NAME` (precedência final);
2. `tests/db.local.php` (gitignorado — no dev local aponta para o
   `gde42_test` em localhost, user `Web`);
3. Defaults apontando para o serviço `db-test` do docker-compose
   (`127.0.0.1:3307`, banco `gde_test` — `docker compose up -d db-test`).

Comportamento:

- Se o banco estiver fora do ar, os testes de integração viram **skipped** e o
  resto da suíte roda normalmente — nada quebra.
- O schema das entidades GDE é (re)criado automaticamente no primeiro connect
  (`SchemaTool::updateSchema`, só das classes `GDE\*`; as `DAC\*` são do banco
  institucional e ficam de fora).
- Cada teste roda dentro de `Base::StartTrans()` / `CompleteTrans(false)`:
  tudo que ele insere sofre rollback, então o banco não acumula lixo e os
  testes não enxergam dados uns dos outros.

**Atenção:** o harness executa DDL e INSERTs — **nunca** aponte os `TEST_DB_*`
para um banco `gde` real (nem o de homologação).

## Amostra de dados reais (`tests/Fixtures/`)

Montar fixture na mão só chega até certo ponto: sem currículo cadastrado a
`Arvore` classifica tudo como Extra-Curricular, nenhum pré-requisito é avaliado
e não há reserva de vagas para violar. Para cobrir esses caminhos existe
`tests/Fixtures/gde_amostra.sql`, uma fatia de ~21 mil linhas com o "mundo" do
usuário 1 (`login1`, RA 70812):

- currículo de Engenharia de Computação (catálogo 2007) com pré-requisitos;
- 74 eliminadas e histórico de 92 oferecimentos cursados;
- 33 planejados reais, de 2010 a 2024, com 37 compromissos extras;
- turmas com horários, salas, professores e **reservas de vaga**.

Testes que usam a amostra estendem `AmostraTestCase` (que estende
`IntegrationTestCase`). A amostra é carregada uma vez por classe, **fora** da
transação, e apagada no `tearDownAfterClass` — as demais classes continuam
vendo o banco vazio. O rollback por teste continua valendo, então a amostra é
efetivamente imutável do ponto de vista de cada teste.

A amostra é **anonimizada** na geração: logins viram `loginN`, todas as senhas
viram `gde42`, nomes viram "Usuario da Silva" / "Aluno RA" / "Professor N", os
compromissos extras viram "Compromisso N" e os campos de perfil (foto, redes
sociais, cidade, data de nascimento, textos livres) são zerados. O RA é
mantido, como no pacote público `gde_pacote.sql`.

### Regerando a amostra

```bash
FIXTURE_DB_NAME=gde42_prod php tests/Fixtures/extrair.php
```

O extrator lê **direto do banco, só com SELECTs** — não de um dump. Isso é
proposital: as colunas de saída vêm do metadata do Doctrine e o banco de origem
já está no schema atual, então a fatia nasce correta sem nenhuma migração. (O
`gde_pacote.sql` público é de 2017 e está bem defasado: `gde_curriculos` ganhou
`id_disciplina`/`creditos`, `gde_oferecimentos` trocou `id_professor` pela
tabela de junção `gde_r_oferecimentos_professores`, `gde_planejados` ganhou
`simulado`, e `gde_alunos`/`gde_periodos` ganharam várias colunas.)

Conexão por variáveis de ambiente: `FIXTURE_DB_HOST` (127.0.0.1),
`FIXTURE_DB_PORT` (3306), `FIXTURE_DB_NAME` (gde42_prod), `FIXTURE_DB_USER`
(Web), `FIXTURE_DB_PASS` (vazia).

Ao final o extrator confere sozinho:

- se alguma tabela sumiu do ORM (avisa e ignora);
- **integridade referencial** da fatia — uma FK apontando para linha que ficou
  de fora vira `EntityNotFoundException` no primeiro teste que tocar a
  associação;
- se a **anonimização** pegou em todos os registros — se escapar qualquer
  nome, login, e-mail ou campo de perfil, ele aborta com erro em vez de gravar.

Regere depois de mexer nas entidades. Os testes que dependem da amostra pinam
valores concretos (CP, siglas, turmas), então uma amostra nova provavelmente
exige reajustar as asserções de `PlanejadorAmostraTest`.

**Cuidado ao ampliar a fatia:** `$PERIODOS_EXPANDIR` controla em que períodos a
amostra guarda *todas* as turmas das disciplinas alvo. Cada período custa ~200
oferecimentos (mais dimensões, salas e reservas), então mantenha a lista curta.

## Escrevendo testes novos

- `tests/Unit/` — sem banco. O `tests/bootstrap.php` cria um EntityManager
  somente-de-metadados: dá para instanciar entidades e usar os getters/setters
  mágicos do `Base`, mas qualquer `Load()`/`FindBy()`/`Save()`/DQL falha na
  conexão (de propósito).
- `tests/Integration/` — estenda `IntegrationTestCase` e use o banco à vontade;
  o rollback por teste cuida da limpeza. Monte as fixtures no próprio teste
  (`Save(false)` + `Base::_EM_Flush()` no final do seed).
- Precisa de currículo, pré-requisitos ou eliminadas de verdade? Estenda
  `AmostraTestCase` em vez de `IntegrationTestCase` (ver seção da amostra).
  Prefira fixture montada à mão quando ela bastar: a amostra custa ~1s de
  carga por classe.
- Nunca inclua `common/common.inc.php` num teste (sessão, auth, HTML) e evite
  caminhos que chegem em `Base::OK_JSON()`/`Error_JSON()`/`Util::Forbidden()` —
  eles dão `die()` e matam o processo do PHPUnit.
- Testes que documentam comportamento suspeito devem pinar o comportamento
  atual com um comentário; a correção vai em commit separado do teste.

Namespace dos testes: `GDE\Tests\Unit` / `GDE\Tests\Integration` (autoload-dev
`GDE\Tests\` → `tests/`).
