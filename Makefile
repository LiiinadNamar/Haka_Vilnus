.PHONY: install setup start stop tests log dev prod watch help
.DEFAULT_GOAL := help

# run just "make" to see cmd list
help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*##"; printf "\033[36m%-20s\033[0m %s\n", "Target", "Description"} /^[a-zA-Z_-]+:.*?##/ { printf "\033[36m%-20s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

install: ## Install project dependencies
	composer install
	npm install
	npm run build

setup: ## Setup database
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate --no-interaction
	php bin/console doctrine:fixtures:load --no-interaction

start: ## Start Symfony server & compose svcs
	symfony server:start -d
	docker compose up -d

stop: ## Stop Symfony server & compose svcs
	symfony server:stop
	docker compose down

up: start ## Alias for start
down: stop ## Alias for stop

tests: ## Run tests
	php bin/phpunit

log: ## Show Symfony server logs
	symfony server:log

dev: ## Build assets in development mode
	npm run dev --enable-source-maps

prod: ## Build assets in production mode
	npm run build

watch: ## Watch and build assets in development mode
	npm run watch

