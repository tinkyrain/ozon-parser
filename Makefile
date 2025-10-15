CONTAINER_NAME=selenium_parser_app

# Парсинг товара
parse:
	@if [ "$(MAKECMDGOALS)" = "parse" ]; then \
		echo "Использование: make parse <ID>"; \
		exit 1; \
	fi
	docker exec -it $(CONTAINER_NAME) php bin/console app:parse $(filter-out $@,$(MAKECMDGOALS))

# Собрать контейнеры
build:
	docker-compose build

# Поднять контейнеры (в фоне)
up:
	docker-compose up -d

# Остановить контейнеры
down:
	docker-compose down

# Очистка кэша Symfony
cache-clear:
	docker exec -it $(CONTAINER_NAME) php bin/console cache:clear

# Поднять контейнеры и очистить кэш
reset: up cache-clear cache-warmup

