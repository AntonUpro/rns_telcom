#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> [1/6] git pull"
git pull

echo "==> [2/6] docker compose up --build"
docker compose --env-file .env.prod -f docker-compose-prod.yaml up -d

echo "==> [3/6] composer install (no-dev)"
docker exec --user root rns-telcom-app composer install --no-dev --optimize-autoloader

echo "==> [4/6] npm build"
docker exec --user root rns-telcom-app npm ci
docker exec --user root rns-telcom-app npm run build

echo "==> [5/6] doctrine migrations"
docker exec --user root rns-telcom-app bin/console doctrine:migrations:migrate --no-interaction

echo "==> [6/6] cache clear"
docker exec --user root rns-telcom-app bin/console cache:clear --env=prod

echo "==> Done!"
