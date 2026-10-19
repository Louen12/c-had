#!/bin/sh
set -e

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-interaction --prefer-dist
fi

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>/dev/null || true

exec "$@"
