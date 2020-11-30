build: docker-build
up: docker-up
composer: docker-run-composer
cli: docker-exec-li
down: docker-down

docker-build:
	docker-compose up -d --build

docker-up:
	docker-compose up -d

docker-run-composer:
	docker-compose run -rm cli composer install

docker-exec-li:
	docker-compose exec cli bash

docker-down:
	docker-compose down --remove-orphans