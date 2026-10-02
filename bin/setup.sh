#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
export APP_UID="${APP_UID:-$(id -u)}"
export APP_GID="${APP_GID:-$(id -g)}"
if [ "$APP_UID" = "0" ] || [ "$APP_GID" = "0" ]; then
  echo "Run setup as your regular Linux user, without sudo." >&2
  exit 1
fi
command -v docker >/dev/null || { echo "Docker is required." >&2; exit 1; }
docker compose version >/dev/null
if [ ! -f .env ]; then cp .env.example .env; fi
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache
docker compose build app
docker compose up -d --wait db
docker compose run --rm app composer install --no-interaction --prefer-dist
if ! grep -q '^APP_KEY=base64:' .env; then
  docker compose run --rm app php artisan key:generate --ansi
fi
docker compose run --rm app php artisan migrate --force
docker compose up -d app web
echo "Open http://localhost:${HTTP_PORT:-8080}"
