## Pré-Requisitos ##

 - Apache 2.4 ou mais novo;
   - Módulo `mod_rewrite` ativado;
   - Configuração `AllowOverride` definida como `All`;
 - PHP 5.6.30 ou mais novo (recomendável o uso do PHP 7.2);
   - Extensão mysql_pdo;
   - Extensão mbstring;
   - Extensão curl;
   - Extensão GD;
 - MySQL 5.6.4 ou mais novo (recomendável o uso do MySQL 5.7) ou MariaDB 10.0.5 ou mais novo;
   - InnoDB ativado com suporte a Full Text Indexes

## Instalação ##

 1. Instale o Composer de [getcomposer.org](https://getcomposer.org);
 2. Acesse a pasta raiz do projeto e rode o comando `composer install`;
 3. Copie o arquivo `common/config-sample.inc.php` para `common/config.inc.php`;
 4. Edite o arquivo `common/config.inc.php` com os dados apropriados. As partes mais importantes que devem ser alteradas são as relacionadas ao banco de dados (DB) e à URL base do sistema;
 5. Baixe o arquivo [gde_pacote.zip](https://gde.guaycuru.net/gde_pacote.zip) e descompacte-o;
 6. Importe o arquivo `gde_pacote.sql` com o comando `mysql -u USUARIO -p BANCO < gde_pacote.sql` no qual USUARIO é seu usuário no MySQL e BANCO é o nome do banco de dados configurado no passo `4`. Esta importação irá demorar algum tempo, então tenha paciência;
 7. Rode o comando `vendor/bin/doctrine orm:schema-tool:update --force` para ter certeza que as tabelas estão atualizadas.

### Sobre os dados no pacote ###

 - Utilize o login `login1`
 - Todos os usuários possuem a senha `gde42`
 - Todos os dados de alunos e usuários são fictícios
 - O pacote possui alguns dados dos catálogos 2007 e 2016
 - Por questões de tamanho, apenas alguns cursos, modalidades, disciplinas e oferecimentos estão presentes

## Sobre este projeto ##

P: Este é o GDE "de verdade"?  
R: Sim e não: Esta é a versão 2.5 do GDE, atualmente a versão "em produção" é a 2.3.  
  
P: Qual a diferença entre a versão 2.3 e a 2.5?  
R: Em termos de funcionalidades: "avisos", "fóruns", "grupos" e "oportunidades" foram removidos (não eram utilizados). Em termos de backend: a versão 2.3 foi escrita entre 2009 e 2012, e contém código antigo, desatualizado, potencialmente inseguro e, sinceramente, às vezes vergonhoso. Nenhuma biblioteca ou framework foi utilizada, foi tudo feito do zero. Na versão 2.5 foi tudo reescrito para utilizar o ORM [Doctrine](http://www.doctrine-project.org/). Além disso, a versão 2.5 suporta disciplinas com a mesma sigla e níveis diferentes, cursos com o mesmo número e níveis diferentes e oferecimentos com mais de um professor ou com turmas com mais de 2 caracteres, além de várias outras correções menores.  
  
P: Quando o GDE 2.5 vai ao ar?  
R: Assim que algumas burocracias envolvidas forem resolvidas. Espero que isso ocorra antes de 2018!  

P: Já foi tudo reescrito?  
R: Sim, com exceção de algumas coisas menos importantes ou que não serão portadas para a nova versão.  

P: Cadê o chat?  
R: O chat da forma como foi escrito consumia muitos recursos de CPU e memória do servidor, então ele não será levado para a versão 2.5, no entanto buscaremos uma alternativa mais moderna para as próximas versões.  

P: Onde encontro o código dos crawlers / robôs que pegam os dados?  
R: Por requisição da DAC, essa parte do código não será disponibilizada.  

P: Por que isso está sendo feito?  
R: Porque eu acredito que a comunidade de alunos (e ex-alunos) da Unicamp podem colaborar com o projeto, e levá-lo muito mais longe do que eu e meus amigos que me ajudaram somos capazes, por questões de tempo, conhecimento, ideias, etc.  
  
P: Posso colaborar?  
R: Sim, por favor! Faça fork e envie seu pull request!  

P: Como posso ajudar?  
R: Depende:

 - Se você sabe programar em PHP, fique a vontade para corrigir um problema ou criar uma nova feature. Seu pull request será analisado com carinho!
 - Se você é designer, crie um novo layout mais moderno (quem sabe, responsivo) para o GDE, ou melhore o atual, e envie seu pull request.
 - Se você não se encaixa em nenhuma das opções anteriores, envie sugestões ou problemas encontrados criando uma [Issue](https://github.com/guaycuru/gde/issues).
