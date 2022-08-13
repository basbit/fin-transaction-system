run:
	docker-compose up -d

install:
	docker-compose run --rm php composer install

lint:
	docker-compose run --rm php sh -c "./vendor/bin/phpcs --standard=PSR12 src tests &\
	./vendor/bin/phpstan analyse src --memory-limit=1G"

test:
	docker-compose run --rm php sh -c "php ./vendor/bin/phpunit"
