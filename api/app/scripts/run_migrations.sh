set -e

environment=${1:-development}

# Apply migrations
php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction
php app/console doctrine:migrations:up-to-date

if [ "$environment" == "local" ]; then
    php app/console doctrine:migrations:migrate --allow-no-migration --no-interaction --env=test
    php app/console doctrine:migrations:up-to-date --env=test
fi
