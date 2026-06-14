APP=rns-telcom

.PHONY: help up exec down

help:
	@awk 'function build_alias(cmds) { split(cmds, array, " "); res=array[1]; for (i=2; i<=length(array); i++) { res= res " (alias: " array[i] ")"; } return res; } BEGIN {FS = ":.*##"; printf "Usage: make [TARGET]\nTargets:\n"} /^[a-zA-Z_\- ]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", build_alias($$1),  $$2; } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

up: ## initialize database and rabbitMq
	docker compose up -d

ps: ## initialize database and rabbitMq
	docker compose ps

down: ## initialize database and rabbitMq
	docker compose down --remove-orphans

exec: ## initialize database and rabbitMq
	docker exec -it rns-telcom-app bash

lint: ## phpStan
	docker exec -it rns-telcom-app vendor/bin/phpstan analyse

ci:
	docker exec -it rns-telcom-app composer install

nm: ## phpUnit
	docker exec -it rns-telcom-app npm run dev

up-prod: ## Запустить prod окружение
	docker compose --env-file .env.prod -f docker-compose-prod.yaml up -d

down-prod: ## Остановить prod окружение
	docker compose --env-file .env.prod -f docker-compose-prod.yaml down --remove-orphans

build-prod: ## Пересобрать prod образы
	docker compose --env-file .env.prod -f docker-compose-prod.yaml build

deploy: ## Полный деплой: pull + сборка + зависимости + миграции + кеш
	bash scripts/deploy.sh

exec-prod: ## Shell в prod app-контейнере
	docker exec -it rns-telcom-app bash

logs-prod: ## Логи prod (follow)
	docker compose -f docker-compose-prod.yaml logs -f

migrate-prod: ## Запустить миграции БД в проде
	docker exec rns-telcom-app bin/console doctrine:migrations:migrate --no-interaction

cache-prod: ## Прогрев Symfony кеша в проде
	docker exec rns-telcom-app bin/console cache:warmup --env=prod

certbot-init: ## Первичное получение SSL-сертификата (один раз)
	bash scripts/certbot-init.sh
