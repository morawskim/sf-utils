.PHONY: tests

tests:
	docker compose exec php74 vendor/bin/phpunit tests/
	docker compose exec php80 vendor/bin/phpunit tests/
	docker compose exec php81 vendor/bin/phpunit tests/
	docker compose exec php82 vendor/bin/phpunit tests/
	docker compose exec php83 vendor/bin/phpunit tests/
