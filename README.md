# K-ERP-Finder #
Gestor de busca de dados e informações sobre o ERP Kugel

### Sobre ###
Este projeto foi criado para gerenciar informações do ERP Kugel para que o suporte possa ser mais ágil e mais completo, conforme o uso diário por quem presta suporte.

### Funções ###
* Live search na página inicial
* Pesquisa por tabelas (a partir dos problemas adicionados com a tabela)
* Pesquisa por categoria (a partir das categorias que vão sendo cadastradas)
* Pesquisa por tags (a partir das tags que vão sendo cadastradas)

Obs: Este projeto está alocado internamente e não está acessível para a rede externa.

### Executar localmente - Ambiente desenvolvimento ou testes ###
* Necessário ter o php no PATH, ou então usar o caminho completo do php
* Acessar a public_html, dentro da pasta raiz do projeto
* Executar na linha de comando:

`php -S localhost:8000 -t public public/index.php`

### Executar localmente - Ambiente de produção ###
* Necessário ter instalado além do php, algum servidor apache.
* Neste exemplo, e no meu caso, uso o Apache Haus para Windows
* Exemplo de configuração para o Apache Haus, usando virtual server

```
<VirtualHost *:80>
    DocumentRoot "${SRVROOT}/htdocs/k-erp-finder/public_html"
    ServerName k-erp-finder.local
    ServerAlias *.k-erp-finder.local
    <Directory "${SRVROOT}/htdocs/k-erp-finder/public_html">
        Options All Includes Indexes
    </Directory>
</VirtualHost>
```

* Adicionar o endereço k-erp-finder.local ao arquivo:
* - Windows: c:\Windows\System32\drivers\etc\hosts
* - Linux: /etc/hosts
* 127.0.0.1           k-erp-finder.local
* Obs: Cada computador da rede que queira acessar precisa adicionar ao hosts, porém deve colocar o IP do servidor, e não local.

### Banco de dados ###
* Está sendo usado o MySQL. No meu caso, estou usando como root com senha qualquer. Caso queira alterar, basta informar no arquivo /public_html/bootstrap/app.php
* Antes de executar pela primeira vez, necessário criar as tabelas, se encontram em /database/schema.sql
