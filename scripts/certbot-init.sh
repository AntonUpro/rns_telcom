#!/usr/bin/env bash
# Запускать один раз после того как DNS настроен и порт 80 открыт.
set -euo pipefail

cd "$(dirname "$0")/.."

if [ ! -f .env.prod ]; then
    echo "Ошибка: файл .env.prod не найден. Создайте его из .env.example.prod"
    exit 1
fi

APP_DOMAIN=$(grep -E '^APP_DOMAIN=' .env.prod | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs)
CERTBOT_EMAIL=$(grep -E '^CERTBOT_EMAIL=' .env.prod | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs)

if [ -z "${APP_DOMAIN}" ] || [ -z "${CERTBOT_EMAIL}" ]; then
    echo "Ошибка: APP_DOMAIN и CERTBOT_EMAIL должны быть заполнены в .env.prod"
    exit 1
fi

echo "==> Запрос сертификата для ${APP_DOMAIN}"
docker compose --env-file .env.prod -f docker-compose-prod.yaml run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email "${CERTBOT_EMAIL}" \
    --agree-tos \
    --no-eff-email \
    -d "${APP_DOMAIN}"

echo "==> Перезагрузка nginx"
docker compose --env-file .env.prod -f docker-compose-prod.yaml exec rns-telcom-nginx nginx -s reload

echo "==> Сертификат получен. HTTPS работает."
