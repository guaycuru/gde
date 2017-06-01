## Instalação ##

[ToDo]

 1. getcomposer.org
 2. composer install
 3. common/config-sample.inc.php -> common/config.inc.php
 4. vi common/config.inc.php
 5. vendor/bin/doctrine[.bat] orm:schema-tool:update --force

## FAQ - Perguntas Frequentes ##

P: Este é o GDE "de verdade"?  
R: Sim e não: Esta é a versão 2.5 do GDE, atualmente a versão "em produção" é a 2.3.  
  
P: Qual a diferença entre a versão 2.3 e a 2.5?  
R: Em termos de funcionalidades: "avisos", "fóruns", "grupos" e "oportunidades" foram removidos (não eram utilizados). Em termos de backend: a versão 2.3 foi escrita entre 2009 e 2012, e contém código antigo, desatualizado, potencialmente inseguro e, sinceramente, às vezes vergonhoso. Nenhuma biblioteca ou framework foi utilizada, foi tudo feito do zero. Na versão 2.5 está tudo sendo reescrito para utilizar o ORM [Doctrine](http://www.doctrine-project.org/ "Doctrine"). Além disso, a versão 2.5 suporta disciplinas com a mesma sigla e níveis diferentes, cursos com o mesmo número e níveis diferentes e oferecimentos com turmas com mais de 2 caracteres.  
  
P: Já foi tudo reescrito?  
R: Não, esse é um processo lento e trabalhoso, e infelizmente estou fazendo tudo sozinho.  
  
P: E falta muito?  
R: Depende do ponto de vista. Veja a próxima pergunta.  
  
P: O que já funciona nesta versão e o que não funciona?  
R: Segue a lista que vou manter atualizada:  

Funciona:

 - Acontecimentos e Status
 - Amigos
 - Árvore / Integralização (pouco testada)
 - Avaliar Professores
 - Configurações da Conta
 - Contato (não testada)
 - Convidar um Amigo (não testada)
 - Disciplina
 - Editar Perfil (pouco testada)
 - Eliminar Disciplinas
 - Estatísticas (parcial)
 - Login
 - Inicial
 - Mapa do Campus
 - Notas
 - Oferecimento
 - Perfil (Usuário, Aluno e Professor)
 - Perguntas Frequentes (FAQ)
 - Planejador (pouco testado)
 - Sala
 - Sobre o GDE

Ainda não funciona:

 - Admin
 - API
 - Calendário
 - Chat
 - Frase do Dia
 - Enquetes

  
P: E qual o prazo pra isso ser concluído?  
R: Gostaria de dar um prazo, mas isso é impossível. Quero concluir o mais rápido possível para que todos possam colaborar com o projeto.  

P: Por que isso está sendo feito?  
R: Porque eu acredito que a comunidade de alunos (e ex-alunos) da Unicamp podem colaborar com o projeto, e levá-lo muito mais longe do que eu (e meus amigos que me ajudaram) sou capaz (por questões de tempo, conhecimento, ideias, etc).  
  
P: Posso colaborar?  
R: Sim, por favor! Faça fork e envie seu pull request!  
