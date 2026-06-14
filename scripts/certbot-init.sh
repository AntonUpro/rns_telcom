#!/usr/bin/env bash
# Запускать один раз после того как DNS настроен и порт 80 открыт.
# Использует standalone-режим certbot: сам поднимает HTTP-сервер на порту 80,
# поэтому nginx останавливается на время выдачи сертификата.
set -euo pipefail

cd "$(dirname "$0")/.."

if [ ! -f .env.prod ]; then
    echo "Ошибка: файл .env.prod не найден. Создайте его из .env.example.prod"
    exit 1
fi

APP_DOMAIN=$(grep -E '^APP_DOMAIN=' .env.prod | cut -d= -f2- | sed 's/[[:space:]]*#.*//' | tr -d '"' | tr -d "'" | xargs)
CERTBOT_EMAIL=$(grep -E '^CERTBOT_EMAIL=' .env.prod | cut -d= -f2- | sed 's/[[:space:]]*#.*//' | tr -d '"' | tr -d "'" | xargs)

if [ -z "${APP_DOMAIN}" ] || [ -z "${CERTBOT_EMAIL}" ]; then
    echo "Ошибка: APP_DOMAIN и CERTBOT_EMAIL должны быть заполнены в .env.prod"
    exit 1
fi

echo "==> [1/3] Остановка nginx (освобождаем порт 80)"
docker compose --env-file .env.prod -f docker-compose-prod.yaml stop rns-telcom-nginx

echo "==> [2/3] Запрос сертификата для ${APP_DOMAIN} (standalone)"
docker compose --env-file .env.prod -f docker-compose-prod.yaml run --rm \
    --publish "80:80" \
    --entrypoint certbot \
    certbot certonly \
    --standalone \
    --email "${CERTBOT_EMAIL}" \
    --agree-tos \
    --no-eff-email \
    -d "${APP_DOMAIN}"

echo "==> [3/3] Запуск nginx с SSL"
docker compose --env-file .env.prod -f docker-compose-prod.yaml start rns-telcom-nginx

echo "==> Готово! Сертификат получен. HTTPS работает на https://${APP_DOMAIN}"
