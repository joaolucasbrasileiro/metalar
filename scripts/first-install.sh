#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/metalar/backend}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"

cd "$APP_DIR"

if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env from .env.example. Edit it before running deploy."
    exit 1
fi

set_env_value() {
    local key="$1"
    local value="$2"

    if grep -q "^${key}=" .env; then
        sed -i.bak "s|^${key}=.*|${key}=${value}|" .env
        rm -f .env.bak
        return
    fi

    printf '\n%s=%s\n' "$key" "$value" >> .env
}

ensure_env_secret() {
    local key="$1"
    local value="$2"
    local current

    current="$(grep "^${key}=" .env | tail -n 1 | cut -d= -f2- || true)"

    if [ -z "$current" ]; then
        set_env_value "$key" "$value"
    fi
}

ensure_env_secret "APP_KEY" "base64:$(openssl rand -base64 32)"
ensure_env_secret "JWT_SECRET" "$(openssl rand -base64 64 | tr -d '\n')"

docker compose -f "$COMPOSE_FILE" up -d --build --force-recreate

docker compose -f "$COMPOSE_FILE" exec -T app php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T app php artisan db:ensure-schema
docker compose -f "$COMPOSE_FILE" exec -T app php artisan package:discover --ansi
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan storage:link
docker compose -f "$COMPOSE_FILE" exec -T app php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan route:cache

docker compose -f "$COMPOSE_FILE" ps
