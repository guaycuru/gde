
# Configuração no Windows

## Baixe e instale o WAMP

1. Faça o download do WAMP Server e inicie o processo de instalação.
2. Durante a instalação, certifique-se de selecionar os pacotes do **Apache**, **PHP** e **MySQL**.
3. Conclua a instalação conforme solicitado pelo instalador.

## Copie os arquivos do projeto para o diretório WWW

1. Após a instalação do WAMP, localize o diretório padrão do servidor:
   ```plaintext
   C:\wamp64\www\
   ```
2. Crie a estrutura de pastas necessária para o projeto:
   ```plaintext
   C:\wamp64\www\Web\gde
   ```
3. Copie os arquivos do projeto para a pasta criada acima.

## Configure o banco de dados

1. Acesse o phpMyAdmin pelo endereço:
   ```plaintext
   http://localhost/phpmyadmin
   ```
2. Crie um banco de dados com o mesmo nome configurado no arquivo:
   ```plaintext
   common/config.inc.php
   ```
3. Importe o arquivo **gde_pacote.sql** para o banco de dados criado.

## Inicie o servidor WAMP

1. Abra o painel do WAMP Server (ícone na barra de tarefas).
2. Certifique-se de que os serviços do **Apache** e **MySQL** estejam rodando:
   - Ícone verde no WAMP indica que todos os serviços estão ativos.
3. Acesse o projeto pelo navegador em:
   ```plaintext
   http://localhost/Web/gde
   ```

## Teste o sistema

1. Use as credenciais de exemplo abaixo para verificar o funcionamento do sistema:
   - **Usuário:** login1
   - **Senha:** gde42

Se todos os passos forem seguidos corretamente, o sistema estará pronto para uso! 🎉
