.PHONY: help
help: ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: init
init: ## Init database
	@[ -e var/db.sqlite ] && rm var/db.sqlite
	@sqlite3 -line var/db.sqlite 'CREATE TABLE php_release(version VARCHAR(15) PRIMARY KEY, release_date DATE NOT NULL);'
	@sqlite3 -line var/db.sqlite 'CREATE TABLE php_version(version VARCHAR(15) PRIMARY KEY, last_release VARCHAR(15) NOT NULL, initial_release_date DATE NOT NULL, active_support_until DATE, end_of_life_date DATE NOT NULL);'
