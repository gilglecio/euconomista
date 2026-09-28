# Setup

Requisitos: Docker com Docker Compose v2.

Stack: PHP 8.2 (Apache) + MySQL 8.4. Os arquivos de imagem ficam em `./docker`.

- Clone o repositório e entre em `/euconomista`
- `docker compose up -d --build`
- `docker compose exec api composer install`
- `docker compose exec api bower install --allow-root`
- `docker compose exec api ./db migrate --no-interaction`
- Acesse `http://localhost:3010`

## Configuração

Variáveis lidas pelo `docker-compose.yml` (defina no shell ou num arquivo `.env` na raiz):

| Variável | Padrão | Descrição |
| --- | --- | --- |
| `APP_PORT` | `3010` | Porta HTTP do app no host |
| `DB_PORT` | `3316` | Porta do MySQL no host |
| `MAIL_HOST` / `MAIL_PORT` | `host.docker.internal` / `1025` | SMTP (ex.: MailHog local) |
| `PHP_ERROR_REPORTING` | `E_ALL & ~E_DEPRECATED` | Nível de erros exibidos |

O app também lê `APP_URL`, `DB_*`, `MAIL_*`, `FB_APP_ID` e `FB_APP_SECRET` do ambiente (ver `app/env.php`).

## Testes

- `docker compose exec api vendor/bin/phpunit tests`
