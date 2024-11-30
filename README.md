
<p align="center">
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/php-%3E%3D7.0-blue" alt="PHP Version"></a>
  <a href="https://mariadb.org/"><img src="https://img.shields.io/badge/mariadb-%3E%3D10.0.5-blue" alt="MariaDB Version"></a>
  <a href="https://dev.mysql.com/"><img src="https://img.shields.io/badge/mysql-%3E%3D5.6.4-blue" alt="MySQL Version"></a>
</p>

<p align="center">
  <img src="docs/assets/img/gde-logo.jpg" alt="GDE Logo" width="300" />
</p>

<h2 align="center">🕵️‍♂️ GDE </h2>

## 📋 Pré-Requisitos

- **Servidor Web**: Apache 2.4 ou mais novo, com o módulo `mod_rewrite` ativado e `AllowOverride` definido como `All`;
- **PHP**: Versão 7.0 ou mais recente (recomendado: 7.2);
- Extensões PHP:
  - `mysql_pdo`;
  - `mbstring`;
  - `curl`;
  - `GD`;
- **Banco de Dados**:
  - MySQL 5.6.4 ou mais recente (recomendado: 5.7);
  - Ou MariaDB 10.0.5 ou mais recente;
  - InnoDB ativado com suporte a índices Full Text.

## 🛠 Instalação

1. **Instale o Composer**:
   Baixe e instale o Composer diretamente de [getcomposer.org](https://getcomposer.org/).

2. **Dependências do Projeto**:
   Na pasta raiz do projeto, execute:
   ```bash
   composer install
   ```

3. **Configuração Inicial**:
   - Copie `common/config-sample.inc.php` para `common/config.inc.php`:
     ```bash
     cp common/config-sample.inc.php common/config.inc.php
     ```
   - Edite o arquivo `common/config.inc.php` com suas configurações, especialmente:
     - Banco de Dados (`DB`);
     - URL base do sistema.

4. **Dados do Pacote**:
   - Baixe o arquivo `gde_pacote.zip` e descompacte-o.
   - Importe o SQL para seu banco de dados:
     ```bash
     mysql -u USUARIO -p BANCO < gde_pacote.sql
     ```
     Substitua `USUARIO` pelo nome de usuário e `BANCO` pelo banco configurado.

5. **Atualização de Esquema**:
   Garanta que o esquema do banco está atualizado:
   ```bash
   vendor/bin/doctrine orm:schema-tool:update --force
   ```

## 🌐 Configurando a API do Google

1. Ative a API no [Google Console](https://console.developers.google.com).
2. Vá para a página de credenciais.
3. Configure as credenciais OAuth:
   - **Origens JavaScript autorizadas**: `http://localhost`;
   - **URIs de redirecionamento autorizados**: `http://localhost/gde/views/google-calendar.php`.
4. Baixe o JSON das credenciais e adicione-o na pasta do projeto.

## 🚀 Como Rodar o Projeto

### **Windows** (Utilizando WAMP)
Para configurar e executar o projeto no Linux, consulte o guia completo neste link:  
[Passo a Passo para Windows](docs/WINDOWS_CONFIG.md).  

### **Linux**
Para configurar e executar o projeto no Linux, consulte o guia completo neste link:  
[Passo a Passo para Linux](docs/LINUX_CONFIG.md).

---

## 🗂 Sobre os Dados no Pacote

- Login de exemplo: **`login1`**;
- Todos os usuários possuem a senha **`gde42`**;
- Os dados fornecidos são fictícios, incluindo alunos e usuários;
- Contém dados de catálogos dos anos **2007** e **2016**;
- Inclui amostras de cursos, modalidades, disciplinas e oferecimentos.

## ❓ Perguntas Frequentes

### Este é o GDE "de verdade"?
Sim. A versão 2.5 substituiu a 2.3 em **22/12/2017**.

### Quais as diferenças entre as versões 2.3 e 2.5?
- **Funcionalidades removidas**: "avisos", "fóruns", "grupos" e "oportunidades".
- **Melhorias**:
  - Suporte a múltiplos níveis de disciplinas e cursos;
  - Correções diversas no backend;
  - Uso do ORM Doctrine.

### O chat foi removido?
Sim. O consumo excessivo de recursos levou à remoção do chat. Alternativas modernas serão consideradas no futuro.

### Onde encontro o código dos crawlers / robôs que pegam os dados? 
Por requisição da DAC, essa parte do código não será disponibilizada.

### Por que isso foi feito?
Porque eu acredito que a comunidade de alunos (e ex-alunos) da Unicamp podem colaborar com o projeto, e levá-lo muito mais longe do que eu e meus amigos que me ajudaram somos capazes, por questões de tempo, conhecimento, ideias, etc.

## 🤝 Contribuições

Quer colaborar? Faça um **fork** e envie um **pull request**!

### Como posso ajudar?

- **Programador PHP**: Resolva problemas ou crie novas funcionalidades.
- **Designer**: Proponha layouts modernos e responsivos.
- **Outros**: Envie sugestões ou relate problemas criando uma **Issue**.

## Agradecimentos ##
- [Carlos Avelar](https://github.com/carlosamds) por desenvolver uma integração com o Google Calendar.
- [Luciano Zago](https://github.com/lcnzg) por ter corrigido um erro no planejador.
- [Nicolas Caous](https://github.com/NicolasCaous) por algumas correções de bugs e por ter descoberto e reportado (incluindo PoCs) duas vulnerabilidades.