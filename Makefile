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

setup-remotes:
	git remote add metrics git@github.com:morawskim/sf-messenger-metrics-middleware.git

split-packages:
	docker run --rm -e SUDO_UID=$(shell id -u) -v $(PWD):/data splitsh-lite --prefix=src/Messenger/MetricsMiddleware/ --target=refs/heads/metrics
