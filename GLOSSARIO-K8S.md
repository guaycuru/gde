# Glossário Kubernetes (GDE)

Referência rápida dos termos de Kubernetes usados neste projeto, em português simples. Cada termo tem uma explicação curta e, quando existe, o arquivo do projeto onde ele aparece.

Os manifestos citados abaixo vivem no repositório separado `gde-k8s` (pastas `hom/` e `prod/`), não mais neste repositório.

---

**Cluster**
Um conjunto de várias máquinas (da Unicamp, nesse caso) que funcionam juntas como se fossem um único ambiente. Você não escolhe em qual máquina física algo roda — o Kubernetes decide isso por trás.

**Namespace**
Uma "pasta" lógica dentro do cluster, usada para separar projetos/ambientes uns dos outros. Todos os recursos deste projeto vivem no namespace `guaycuru-gde`. Arquivo: `namespace.yaml`.

**Manifesto / arquivo `.yaml`**
Um arquivo de texto que descreve **o estado que você quer que exista** (ex.: "quero 1 container do `gde` rodando"). Você não roda comandos diretos — você entrega esse arquivo pro Kubernetes e ele cuida de manter a realidade batendo com o que está escrito ali.

**Pod**
A menor unidade que o Kubernetes roda de fato. Na prática, um Pod ≈ uma instância do seu container rodando (parecido com um `docker run`). Pods são descartáveis: o Kubernetes pode destruir um e criar outro no lugar a qualquer momento.

**Deployment**
Um manifesto que garante que sempre existe uma certa quantidade de Pods rodando uma imagem específica. Se um Pod morrer, o Deployment recria outro automaticamente. Arquivos: `app-deployment.yaml`, `redis-deployment.yaml`.

**Service**
Um endereço fixo (tipo um DNS interno) que sempre aponta para o(s) Pod(s) certo(s), mesmo que eles morram e nasçam de novo com IPs diferentes. Arquivos: `app-service.yaml` (endereço `gde`), `redis-service.yaml` (endereço `redis`).

**ConfigMap**
Uma lista de configurações **não sensíveis** (chave-valor), tipo variáveis de ambiente comuns. Arquivo: `configmap.yaml` (`DB_HOST`, `APP_URL`, `DAC_SEMESTRES` etc.).

**Secret**
Igual ao ConfigMap, mas para dados sensíveis (senhas, chaves). Guardado com um pouco mais de controle de acesso, mas **o base64 usado no campo `data:` não é criptografia** — é só o formato exigido pelo objeto, reversível por qualquer um com um comando. No projeto, todos os Secrets hoje são gerados automaticamente pelo Vault Secrets Operator (ver `vault-secret.yaml`) — não existe mais nenhum `secret.yaml` com valores manuais.

**Vault**
Servidor centralizado (mantido pela infra da Unicamp) cuja função é guardar segredos de verdade, com criptografia, controle de acesso e log de quem acessou o quê. A infra escreve a senha lá em texto puro (via CLI ou interface web do Vault) — ninguém precisa converter nada pra base64 manualmente.

**Vault Secrets Operator / `VaultStaticSecret`**
A peça de software (rodando no cluster) que faz a ponte entre o Vault e o Kubernetes: você escreve um manifesto `VaultStaticSecret` apontando pro caminho do segredo no Vault, e o operator busca o valor e cria, sozinho, um Secret comum do Kubernetes a partir dele (incluindo a conversão pra base64). Depois disso, o Secret criado se usa exatamente como qualquer outro (`secretKeyRef`). Arquivo: `vault-secret.yaml`.

**`envFrom` / `secretKeyRef`**
Formas de injetar ConfigMap/Secret como variáveis de ambiente dentro do container. `envFrom: configMapRef` pega *todas* as chaves do ConfigMap de uma vez; `secretKeyRef` pega *uma* chave específica de um Secret.

**PVC (Persistent Volume Claim)**
Um "disco" que existe independente do ciclo de vida de qualquer Pod. Você o "pluga" (monta) num caminho dentro do container; se o Pod morrer e um novo nascer, o novo Pod pluga o mesmo disco e os arquivos continuam lá. No projeto guarda as fotos de perfil dos usuários. Arquivo: `pvc.yaml`.

**StorageClass**
O "tipo de disco" disponível no cluster (quem provisiona o armazenamento por trás do PVC). Aqui usamos a classe `nfs` (armazenamento em rede, permite `ReadWriteMany` — várias cópias do app acessando ao mesmo tempo).

**initContainer**
Um container "ajudante" que roda uma vez, sozinho, antes do container principal do Pod subir — usado pra tarefas de preparação (no projeto: copiar imagens de placeholder pro PVC vazio na primeira vez).

**Ingress**
A "porta de entrada" do cluster vinda da internet. Um manifesto que diz "pedidos chegando pro hostname X devem ser roteados pro Service Y" — o equivalente em Kubernetes de um virtual host do Apache/nginx. Arquivo: `ingress.yaml`.

**Ingress Controller**
O programa de verdade (rodando no cluster) que lê os manifestos de Ingress e efetivamente roteia o tráfego. No nosso cluster é o nginx Ingress Controller (classe `nginx`), já instalado pela infra.

**cert-manager**
Um add-on do cluster (já instalado pela infra) que emite e renova certificados HTTPS automaticamente, sem passo manual. Você só anota no Ingress qual "emissor" usar.

**ClusterIssuer**
A configuração de "quem emite os certificados" que o cert-manager usa. No projeto, o emissor se chama `letsencrypt` (Let's Encrypt, gratuito) — referenciado via `cert-manager.io/cluster-issuer: letsencrypt` no `ingress.yaml`.

**CronJob**
O equivalente Kubernetes de uma entrada de `crontab`: no horário definido, cria um Pod temporário que roda um comando e depois pode sumir (diferente do Deployment, que quer manter Pods vivos pra sempre). Arquivo: `cronjobs.yaml` (7 tarefas: sync DAC, cardápio, limpezas de cache/tokens, contagem online, backup).

**Imagem (Docker)**
O resultado de executar as instruções de um `Dockerfile` até o fim: um pacote congelado contendo o sistema operacional, tudo que o app precisa (PHP, extensões, dependências) e o próprio código-fonte, pronto pra rodar em qualquer lugar. Parecido com uma imagem de disco/VM. Cada build gera uma imagem nova, identificada por uma tag.

**Tag**
O "rótulo de versão" de uma imagem (ex.: `gde:a1b2c3d`). Usamos o hash curto do commit do git como tag, em vez de `latest`, porque isso torna cada build único e detectável como mudança real.

**Container Registry**
Um lugar pra guardar e distribuir imagens Docker — como um "GitHub de imagens". O GitLab tem um embutido, mas não está habilitado neste projeto; usamos um registry externo fornecido pela infra: `registry.nuvem.unicamp.br`.

**CI/CD (Integração/Entrega Contínua)**
Automação que reage a um `git push` e executa passos sozinha (build, testes, publicação), sem um humano rodar os comandos na mão.

**Pipeline / Stage**
No GitLab, o `.gitlab-ci.yml` descreve um **pipeline** dividido em **stages** (passos), executados em ordem a cada push. No projeto: `build` (builda e publica a imagem) e `update-manifests` (atualiza a tag nos manifestos do repositório `gde-k8s` e commita de volta lá).

**Runner**
O programa que de fato executa os stages de um pipeline. No projeto, o Runner já roda como um Pod dentro do próprio cluster (`gitlab-runner`, namespace `guaycuru-gde`), provisionado pela infra.

**Kaniko**
Ferramenta usada no stage `build` pra construir a imagem Docker (seguindo o `Dockerfile`) sem precisar de acesso privilegiado ao Docker — necessário porque isso normalmente não é permitido dentro de um cluster Kubernetes por segurança.

**GitOps**
O modelo onde o git é a "fonte da verdade": um robô (o ArgoCD) fica comparando o que está escrito nos manifestos do repositório com o que está rodando de fato no cluster, e aplica automaticamente qualquer diferença — sem `kubectl apply` manual. No projeto, a fonte de verdade é o repositório `gde-k8s`, separado do código da aplicação (`gde`).

**ArgoCD**
A ferramenta que implementa o GitOps aqui: monitora o repositório `gde-k8s` continuamente e sincroniza o cluster com o que está lá. Rollback = `git revert`; o ArgoCD desfaz a mudança no cluster sozinho.
