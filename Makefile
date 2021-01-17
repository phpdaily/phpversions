.PHONY: help
help: ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: init
init: ## Init database
	@rm -f var/db.sqlite
	@sqlite3 -line var/db.sqlite 'CREATE TABLE IF NOT EXISTS php_release(version VARCHAR(15) PRIMARY KEY, release_date DATE NOT NULL);'
	@sqlite3 -line var/db.sqlite 'CREATE TABLE IF NOT EXISTS php_version(version VARCHAR(15) PRIMARY KEY, last_release VARCHAR(15) NOT NULL, initial_release_date DATE NOT NULL, active_support_until DATE, end_of_life_date DATE NOT NULL);'
	@sqlite3 -line var/db.sqlite 'CREATE TABLE IF NOT EXISTS last_update(last_update DATETIME NOT NULL);'

.PHONY: clean
clean: ## Clean project files
	@rm -f var/db.sqlite
	@docker-compose down

.PHONY: serve
serve: ## Run project through docker-compose
	@echo "--> Start containers"
	@docker-compose up -d --force-recreate

	@echo "--> Install vendors"
	@docker-compose exec fpm composer install --no-dev

ifeq (,$(wildcard var/db.sqlite))
	@echo "--> Initialize database"
	@docker-compose exec fpm sqlite3 -line var/db.sqlite 'CREATE TABLE IF NOT EXISTS php_release(version VARCHAR(15) PRIMARY KEY, release_date DATE NOT NULL);'
	@docker-compose exec fpm sqlite3 -line var/db.sqlite 'CREATE TABLE IF NOT EXISTS php_version(version VARCHAR(15) PRIMARY KEY, last_release VARCHAR(15) NOT NULL, initial_release_date DATE NOT NULL, active_support_until DATE, end_of_life_date DATE NOT NULL);'
	@docker-compose exec fpm sqlite3 -line var/db.sqlite 'CREATE TABLE IF NOT EXISTS last_update(last_update DATETIME NOT NULL);'

	@echo "--> Synchronize data"
	@docker-compose exec fpm php bin/console synchronize
endif

.PHONY: sync
sync: ## Synchronize PHP versions
	@echo "--> Synchronize data"
	@docker-compose exec fpm bin/console synchronize
