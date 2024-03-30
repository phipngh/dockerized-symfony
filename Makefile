.DEFAULT_GOAL := help
.PHONY: *

UID = $(shell id -u)
GID = $(shell id -g)

define run-in-container
	echo "CONTAINER: "${2}""; \
	echo "COMMAND: "${3}""; \
	if [ ! -f /.dockerenv ] && [ $$(env|grep -c -e "^CI=") -eq 0 ] && [ -d docker ] && [ "$$(docker ps -q --filter name=$(2))" ]; then \
		docker compose exec --user $(1) -T $(2) $(3); \
	elif [ $$(env|grep -c "^CI=") -gt 0 -a $$(env|grep -cw "DOCKER_DRIVER") -eq 1 ]; then \
		docker compose exec --user $(1) -T $(2) $(3); \
	elif [ ! -f /.dockerenv ] && [ $$(env|grep -c -e "^CI=") -eq 0 ] && [ -d docker ]; then \
		docker compose run --rm --user $(1) -T $(2) $(3); \
	else \
		$(3); \
	fi
endef

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

##############################
# INFRASTRUCTURE
##############################

infra-up: ## Start docker containers
	docker compose up --build -d $(service)

infra-stop: ## Stop all docker container
	docker compose stop $(service)

infra-clean: ## To remove images, containers, volumes
	@docker compose down -v --rmi all

infra-rebuild: ## To remove images, containers, volumes and setup again.
	make infra-clean
	make infra-up

infra-show-logs: ## Show docker containers logs
	docker compose logs -ft $(service)

infra-shell-php: ## To open a shell session in the php container
	docker compose exec --user=www-data php bash

##############################
# APPLICATION
##############################

install: ## Install application
	@$(call run-in-container,www-data,php,composer install --prefer-dist --no-progress --no-interaction)

clear: ## cache clean
	rm -rf var/cache/*
	@$(call run-in-container,www-data,php,php -d memory_limit=-1 bin/console cache:clear)
