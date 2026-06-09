.PHONY: up down build restart logs shell-php migrate fresh seed artisan tinker

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

restart:
	docker compose restart

logs:
	docker compose logs -f

shell-php:
	docker compose exec backend bash

migrate:
	docker compose exec backend php artisan migrate

fresh:
	docker compose exec backend php artisan migrate:fresh

artisan:
	docker compose exec backend php artisan $(cmd)

tinker:
	docker compose exec backend php artisan tinker

check-domains:
	docker compose exec backend php artisan domains:check

frontend-install:
	cd frontend && npm install

frontend-dev:
	cd frontend && npm run dev

frontend-build:
	cd frontend && npm run build

setup:
	cp -n backend/.env.example backend/.env 2>/dev/null || true
	cp -n frontend/.env.example frontend/.env 2>/dev/null || true
	docker compose build
	docker compose up -d
	@echo "Waiting for containers to be ready..."
	@sleep 10
	@echo "Done! Backend: http://localhost:8000 | Frontend: run 'make frontend-dev'"
