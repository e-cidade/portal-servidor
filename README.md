

# Portal Servidor

Portal de Servidor

- [Laravel](https://laravel.com/docs/routing).
- [InertiaJS com Vue](https://inertiajs.com/).

##### OBS. Para cadas instituição deve-se ter um portal altere no .env para ter a instituição desejada

# Instalação em produção

####  CRIAR DIRETÓRIO 
```
sudo mkdir -p /opt/contass/portal-servidor
```

####  ALTERAR POSSE 
```
sudo chown -R $USER:$USER /opt/contass/portal-servidor

```

##  DEFINIR PERMISSÕES 
```
sudo chmod -R 755 /opt/contass/portal-servidor
sudo chmod -R o+rx /opt /opt/contass/portal-servidor

```

## Diretório → clonar

```
cd /opt/contass/
cp .env.example .env

```

## Arquivo .env
1. Alterar arquivo .env com a conexão do banco de dados do e-cidade
2. Caso necessário de permissão de acesso ao ip do container para o postgres local para o ip 172.26.1.0/24
3. Se o https estiver ativo altere a variável APP_ENV de local para production

# Instalação em desenvolvimento

#### Clone o projeto em uma pasta qualquer
```
```

#### Arquivo .env
1. Alterar arquivo .env com a conexão do banco de dados do e-cidade
2. Caso necessário de permissão de acesso ao ip do container para o postgres local para o ip 172.26.1.0/24
3. Se o https estiver ativo altere a variável APP_ENV de local para production

#### Execute o comando dentro da pasta do projeto

```

docker network create portaltransparencia_pm_transparencia
docker compose up -d --build
docker compose exec -it app php artisan migrate

```


### OBS.: 
 1. Para todos os casos o portal vai rodar na porta 80
 2. Todos os comandos devem ser rodados dentrodo conteiner
 3. caso necessário dê permissão de acesso ao ip do container para o postgres local
 4. Rode  as migrations dentro do container
