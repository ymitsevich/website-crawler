DOCKER_COMPOSE = docker compose
DOCKER = docker

.PHONY: build-dev build-prod init-dev run-dev test
build-dev:
	$(DOCKER_COMPOSE) -f docker-compose.dev.yml build

build-prod:
	$(DOCKER_COMPOSE) -f docker-compose.prod.yml build

init-dev:
	$(DOCKER_COMPOSE) -f docker-compose.dev.yml run --rm app composer install --no-interaction --no-progress --optimize-autoloader
	$(DOCKER_COMPOSE) -f docker-compose.dev.yml up -d mq
	$(DOCKER_COMPOSE) -f docker-compose.dev.yml run --rm worker

run-dev:
	$(DOCKER_COMPOSE) -f docker-compose.dev.yml run --rm app bin/console scrape:website:run $(filter-out $@,$(MAKECMDGOALS))

test:
	$(DOCKER) run --rm -it website-crawler-app:latest vendor/bin/phpunit
