#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/metalar/backend}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"

cd "$APP_DIR"

git fetch --prune origin
git reset --hard origin/main

docker compose -f "$COMPOSE_FILE" up -d --build --remove-orphans

docker compose -f "$COMPOSE_FILE" exec -T app php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T app php artisan db:ensure-schema
docker compose -f "$COMPOSE_FILE" exec -T app php artisan package:discover --ansi
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan storage:link
docker compose -f "$COMPOSE_FILE" exec -T app php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan route:cache

docker compose -f "$COMPOSE_FILE" ps
