#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/metalar/backend}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"

cd "$APP_DIR"

git fetch --prune origin
git reset --hard origin/main

docker compose -f "$COMPOSE_FILE" up -d --build --remove-orphans

docker compose -f "$COMPOSE_FILE" exec -T app php -r '$keys = ["DB_CONNECTION", "DB_SCHEMA", "DB_SSLMODE"]; foreach ($keys as $key) { echo $key."=".(getenv($key) ?: "").PHP_EOL; } echo "DB_URL=".(getenv("DB_URL") || getenv("DATABASE_URL") ? "set" : "empty").PHP_EOL; if (getenv("DB_CONNECTION") !== "pgsql") { fwrite(STDERR, "DB_CONNECTION must be pgsql for docker-compose.prod.yml.\n"); exit(1); }'
docker compose -f "$COMPOSE_FILE" exec -T app php artisan config:clear
docker compose -f "$COMPOSE_FILE" exec -T app php artisan route:clear
docker compose -f "$COMPOSE_FILE" exec -T app php artisan view:clear
docker compose -f "$COMPOSE_FILE" exec -T app php artisan db:ensure-schema
docker compose -f "$COMPOSE_FILE" exec -T app php artisan package:discover --ansi
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan storage:link
docker compose -f "$COMPOSE_FILE" exec -T app php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan route:cache

docker compose -f "$COMPOSE_FILE" ps
