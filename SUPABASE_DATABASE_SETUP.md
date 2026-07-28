# Supabase Database Setup

Guia para usar o Supabase apenas como banco Postgres da API Laravel, mantendo o backend, frontend, imagens, filas e scheduler na VPS.

## 1. Criar o projeto no Supabase

Crie um projeto no Supabase e guarde a senha do banco. No painel do projeto, abra **Connect** para copiar uma connection string de Postgres.

Para a VPS, prefira nesta ordem:

- **Direct connection**, se a VPS conseguir acessar IPv6 ou se o projeto tiver IPv4 add-on.
- **Session pooler**, se a VPS for IPv4-only.

Evite **Transaction pooler** para o runtime Laravel, porque aplicações persistentes funcionam melhor com conexão direta ou pooler em modo de sessão.

## 2. Criar o schema da aplicação

No SQL Editor do Supabase, execute uma vez:

```sql
create schema if not exists laravel;
```

O Laravel vai usar esse schema via `DB_SCHEMA=laravel`, deixando as tabelas da aplicação separadas do schema `public`.

## 3. Configurar o `.env` da VPS

No arquivo `.env` do backend em produção, ajuste as variáveis de banco:

```env
DB_CONNECTION=pgsql
DB_SCHEMA=laravel
DB_SSLMODE=require
DB_URL="postgresql://USER:PASSWORD@HOST:PORT/postgres"
```

Também é possível usar `DATABASE_URL` no lugar de `DB_URL`, porque o `config/database.php` aceita os dois.

Mantenha as demais variáveis como estão, incluindo `FILESYSTEM_DISK=local`, `QUEUE_CONNECTION=database`, `JWT_SECRET`, `APP_KEY`, email e pagamento.

## 4. Subir a API com o novo driver

O Dockerfile instala `pdo_pgsql`, que é o driver PHP usado pelo Laravel para falar com Postgres.

Na VPS, depois de atualizar o código:

```bash
docker compose -f docker-compose.prod.yml up -d --build --remove-orphans
```

## 5. Criar as tabelas no banco novo

Como o banco começa vazio, rode:

```bash
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan db:seed --force
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
```

Se não quiser popular dados iniciais, pule o `db:seed`.

## 6. Validar

Teste os fluxos principais:

- Cadastro, ativação e login.
- Listagem de produtos, categorias e lojas.
- Upload e exibição de imagens de produto.
- Carrinho, checkout e tentativa de pagamento.
- Webhook da AbacatePay.
- Ajustes, reservas e baixa de estoque.

Para checar a conexão usada pelo Laravel:

```bash
docker compose -f docker-compose.prod.yml exec -T app php artisan tinker
```

```php
DB::select('select current_database(), current_schema()');
```
