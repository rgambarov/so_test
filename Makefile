SHELL := /bin/bash
export APP_UID := $(shell id -u)
export APP_GID := $(shell id -g)
.PHONY: setup up down logs shell test migrate lint
setup:
	bash bin/setup.sh
up:
	docker compose up -d
down:
	docker compose down
logs:
	docker compose logs -f --tail=100
shell:
	docker compose exec app bash
test:
	docker compose exec app php artisan test
migrate:
	docker compose exec app php artisan migrate
lint:
	docker compose exec app vendor/bin/pint --test
