.PHONY: tests

tests:
	rm -f composer.lock
	docker compose exec php74 composer install && vendor/bin/phpunit tests/
	rm -f composer.lock
	docker compose exec php80 composer install && vendor/bin/phpunit tests/
	rm -f composer.lock
	docker compose exec php81 composer install && vendor/bin/phpunit tests/
	rm -f composer.lock
	docker compose exec php82 composer install && vendor/bin/phpunit tests/
	rm -f composer.lock
	docker compose exec php83 composer install && vendor/bin/phpunit tests/
