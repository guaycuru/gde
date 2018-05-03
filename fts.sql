ALTER TABLE `gde_professores` ADD FULLTEXT `nome_fts`(`nome`);
ALTER TABLE `gde_disciplinas` ADD FULLTEXT `sigla_nome_fts` (`sigla`, `nome`);
ALTER TABLE `gde_alunos` ADD FULLTEXT `nome_fts` (`nome`);
