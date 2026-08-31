# Configuração no Linux

## Registro do site

Crie um link virtual do seu diretório com o projeto para o local padrão de acesso do Apache:

```bash
sudo ln -s /caminho/do/projeto /var/www/html/gde # Exemplo sudo ln -s /home/[seu_usuario]/gde /var/www/html/gde
```

Crie o arquivo de registro do domínio dentro do Apache, use o nome que você achar melhor (e.g. `gde.conf`):

```bash
sudo nano /etc/apache2/sites-available/gde.conf
```

Adicione as seguintes linhas no arquivo, substitua o caminho pelo caminho do seu projeto (e.g. `/home/[seu_usuario]/gde/`):

```bash
ServerName gde
DocumentRoot /var/www/html/gde/
Alias "/Web/gde/" "/var/www/html/gde/"
```

## Permissões do projeto

Como o projeto tem um arquivo `.htaccess`, nós precisamos habilitar as diretrizes para que as configurações sejam aplicadas:

```bash
sudo nano /etc/apache2/apache2.conf
```

```bash
...
<Directory /var/www/>
	Options Indexes FollowSymLinks
	AllowOverride None
	Require all granted
</Directory>

# Adicione o bloco abaixo
<Directory /var/www/html/gde/>
	Options Indexes FollowSymLinks
	AllowOverride All
	Require all granted
</Directory>

#<Directory /srv/>
# Options Indexes FollowSymLinks
...
```

## Habilitar site e módulos

Com os arquivos de configuração do domínio criados e devidamente registrados, crie um link simbólico para `sites-enabled`:

```bash
sudo a2ensite gde
```

Talvez seja necessário habilitar alguns módulos utilizados no projeto se já não estiverem habilitados:

```bash
sudo a2enmod rewrite 
```

```bash
sudo a2enmod php7.x # e.g. php7.4
```

Após isso, reinicie o Apache para atualizar com as novas configurações:

```bash
sudo systemctl restart apache2
```

## Pós-configuração

Agora, você deveria ser capaz de rodar o projeto tranquilamente, acessando: [http://localhost/Web/gde/](http://localhost/Web/gde/)
  
Caso você veja apenas uma tela branca ou receba uma mensagem de erro vazia ao tentar logar, possa ser que o Apache não tenha permissão para acessar seu projeto. Basta dar essa permissão:

```bash
sudo chown -R www-data:www-data /var/www/html/gde/proxies
```

Se estes passos funcionaram, se divirta mexendo no código e contribuindo! 🎉🎉
