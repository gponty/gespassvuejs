SHELL := /bin/bash

CURRENT_DATE = `date "+%Y-%m-%d_%H-%M-%S"`

##
## General purpose
## ---------------
##

.DEFAULT_GOAL := help
help: ## Show this help.
	@printf "\n Available commands:\n\n"
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-25s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m## */[33m/'
.PHONY: help

install: vendor db migrations fixtures test-db
.PHONY: install

##
## Project
## -------
##

cc: ## Clear the cache and warm it up.
	@printf $(SCRIPT_TITLE_PATTERN) "PHP" "Clear cache"
	-@symfony console cache:clear --no-warmup
	@printf $(SCRIPT_TITLE_PATTERN) "PHP" "Warmup cache"
	-@symfony console cache:warmup
.PHONY: cc

vendor: ## Install Composer dependencies.
	@printf ""$(SCRIPT_TITLE_PATTERN) "PHP" "Install Composer dependencies"
	composer install --optimize-autoloader --prefer-dist --no-progress
.PHONY: vendor

db: ## Create a database for the project
	@printf ""$(SCRIPT_TITLE_PATTERN) "DB" "Drop existing database"
	@symfony console doctrine:database:drop --no-interaction --if-exists --force
	@printf ""$(SCRIPT_TITLE_PATTERN) "DB" "Create database"
	@symfony console doctrine:database:create --no-interaction --if-not-exists
.PHONY: db-container

migrations:  ## Create database schema through migrations.
	@printf ""$(SCRIPT_TITLE_PATTERN) "DB" "Run migrations"
	@symfony console doctrine:migrations:migrate --no-interaction
.PHONY: migrations

fixtures: ## Add default data to the project.
	@printf ""$(SCRIPT_TITLE_PATTERN) "DB" "Install fixture data in the database"
	@symfony console doctrine:fixtures:load --no-interaction --append
.PHONY: fixtures

copy-env:
	@printf ""$(SCRIPT_TITLE_PATTERN) "SYMFONY" "copy env test file workflow"
	cp .env.test.workflow .env.test
.PHONY: copy-env

add-ssh-key:
	@printf ""$(SCRIPT_TITLE_PATTERN) "DEPLOYER" "add SSH key"
	mkdir -p /root/.ssh;
	ssh-keyscan -t rsa github.com >> /root/.ssh/known_hosts
	ssh-keyscan -t rsa $(HOST_TO_DEPLOY) >> /root/.ssh/known_hosts
.PHONY: add-ssh

deployer: vendor add-ssh-key
	@printf ""$(SCRIPT_TITLE_PATTERN) "DEPLOYER" "Deploy project"
	./vendor/bin/dep deploy -vvv
.PHONY: deployer

##
## QA
## --
##

test-db: ## Set up the test database
	@printf ""$(SCRIPT_TITLE_PATTERN) "Test DB" "Drop existing database"
	@APP_ENV=test php bin/console doctrine:database:drop --no-interaction --if-exists --force
	@printf ""$(SCRIPT_TITLE_PATTERN) "Test DB" "Create database"
	@APP_ENV=test php bin/console doctrine:database:create --no-interaction
	@APP_ENV=test php bin/console doctrine:schema:create --no-interaction
	@printf ""$(SCRIPT_TITLE_PATTERN) "Test DB" "Install fixture data in the database"
	@APP_ENV=test php bin/console doctrine:fixtures:load --no-interaction --append
.PHONY: test-db

install-phpunit:
	@APP_ENV=test symfony php bin/phpunit --version
.PHONY: install-phpunit

phpunit: ## Execute the PHPUnit test suite
	@APP_ENV=test symfony php bin/phpunit
.PHONY: phpunit

qa: ## Execute QA tools
	$(MAKE) security-check
	$(MAKE) cs
	$(MAKE) phpstan
.PHONY: qa

security-check: ## Execute the Symfony Security checker
	@symfony security:check
.PHONY: security-check

phpstan: ## Execute PHPStan
	@printf "\n"$(SCRIPT_TITLE_PATTERN) "QA" "phpstan"
	@symfony php vendor/phpstan/phpstan/phpstan analyse --memory-limit=4G
.PHONY: phpstan

cs: ## Execute php-cs-fixer
	@printf $(SCRIPT_TITLE_PATTERN) "QA" "php-cs-fixer"
	@symfony php vendor/bin/php-cs-fixer fix
.PHONY: cs

cs-dry: ## Execute php-cs-fixer with a DRY RUN
	@printf $(SCRIPT_TITLE_PATTERN) "QA" "php-cs-fixer"
	@symfony php vendor/bin/php-cs-fixer fix --dry-run --diff
.PHONY: cs-dry

lint: ## Execute some linters on the project
	@printf $(SCRIPT_TITLE_PATTERN) "QA" "lint:yaml"
	@symfony console lint:yaml src config translations

	@printf $(SCRIPT_TITLE_PATTERN) "QA" "lint:container"
	@symfony console lint:container

	#@printf $(SCRIPT_TITLE_PATTERN) "QA" "lint:twig"
	#@symfony console lint:twig --show-deprecations
.PHONY: lint

## —— Yarn 🐱 Js ———————————————————————————————————————————————————————————————
dev: ## Rebuild assets for the dev env
	yarn run encore dev

watch: ## Watch files and build assets when needed for the dev env
	yarn run encore dev --watch

build: ## Build assets for production
	yarn run encore production

# Helper vars
SCRIPT_TITLE_PATTERN := "\033[32m[%s]\033[0m %s\n"
SCRIPT_ERROR_PATTERN := "\033[31m[%s]\033[0m %s\n"