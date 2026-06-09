#!/bin/sh
set -e

cd /app

# Railway sets PORT; default for local docker build test
PORT="${PORT:-8080}"

run_migrations() {
  echo "Running migrations..."
  php artisan migrate --force --no-interaction
}

case "${RAILWAY_SERVICE_ROLE:-web}" in
  worker)
    run_migrations
    echo "Starting queue worker..."
    exec php artisan queue:work --sleep=3 --tries=3 --timeout=60
    ;;
  scheduler)
    run_migrations
    echo "Starting scheduler..."
    exec php artisan schedule:work
    ;;
  *)
    run_migrations
    php artisan config:cache
    php artisan route:cache
    echo "Starting web server on port ${PORT}..."
    exec php artisan serve --host=0.0.0.0 --port="${PORT}"
    ;;
esac
