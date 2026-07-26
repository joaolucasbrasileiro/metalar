#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/metalar/backend}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"

cd "$APP_DIR"

if [ ! -f .env ]; then
    cp .env.prod.example .env
    echo "Created .env from .env.prod.example. Edit it before running deploy."
    exit 1
fi

docker compose -f "$COMPOSE_FILE" up -d --build

docker compose -f "$COMPOSE_FILE" exec -T app php artisan package:discover --ansi
docker compose -f "$COMPOSE_FILE" exec -T app php artisan key:generate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan jwt:secret --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan storage:link
docker compose -f "$COMPOSE_FILE" exec -T app php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan route:cache

docker compose -f "$COMPOSE_FILE" ps
